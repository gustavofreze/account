<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Ports\Outbound\Accounts;
use Account\Driven\Account\OperationType;
use Account\Driven\Account\Repository\Records\AccountRecord;
use Account\Driven\Account\Repository\Records\BalanceRecord;
use Account\Driven\Shared\Database\RelationalConnection;

final readonly class Adapter implements Accounts
{
    public function __construct(private RelationalConnection $connection)
    {
    }

    public function save(Account $account): void
    {
        $this->connection
            ->with()
            ->query(sql: Queries::INSERT)
            ->bind(data: [
                ':id'                   => $account->id->toString(),
                ':holderDocumentNumber' => $account->holder->document->getNumber()
            ])
            ->execute();
    }

    public function findById(AccountId $id): ?Account
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BY_ID)
            ->bind(data: [':id' => $id->toString()])
            ->execute()
            ->fetchOneOrNull();

        return AccountRecord::from(result: $result)->toAccountOrNull();
    }

    public function findByHolder(Holder $holder): ?Account
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BY_HOLDER)
            ->bind(data: [':holderDocumentNumber' => $holder->document->getNumber()])
            ->execute()
            ->fetchOneOrNull();

        return AccountRecord::from(result: $result)->toAccountOrNull();
    }

    public function applyCreditTransactionTo(Account $account): void
    {
        /** @var Transaction $transaction */
        $transaction = $account->transactions->first();

        $this->connection
            ->with()
            ->query(sql: Queries::INSERT_TRANSACTION)
            ->bind(data: [
                ':id'              => $transaction->getId()->toString(),
                ':amount'          => $transaction->getAmount()->toFloat(),
                ':accountId'       => $account->id->toString(),
                ':operationTypeId' => OperationType::CREDIT_VOUCHER->value
            ])
            ->execute();
    }

    public function applyDebitTransactionTo(Account $account, Transaction $transaction): void
    {
        $this->connection->inTransaction(
            useCase: function (RelationalConnection $connection) use ($account, $transaction) {
                $accountId = $account->id;
                $balance = $this->balanceOf(id: $accountId);
                $account = $account->debit(balance: $balance, transaction: $transaction);

                /** @var Transaction $transaction */
                $transaction = $account->transactions->first();
                $operationTypeId = OperationType::fromDebitTransaction(transaction: $transaction);

                $connection
                    ->with()
                    ->query(sql: Queries::INSERT_TRANSACTION)
                    ->bind(data: [
                        ':id'              => $transaction->getId()->toString(),
                        ':amount'          => $transaction->getAmount()->toFloat(),
                        ':accountId'       => $accountId->toString(),
                        ':operationTypeId' => $operationTypeId->value
                    ])
                    ->execute();
            }
        );
    }

    public function applyWithdrawalTransactionTo(Account $account, Transaction $transaction): void
    {
        $this->connection->inTransaction(
            useCase: function (RelationalConnection $connection) use ($account, $transaction) {
                $accountId = $account->id;
                $balance = $this->balanceOf(id: $accountId);
                $account = $account->withdraw(balance: $balance, transaction: $transaction);

                /** @var Transaction $transaction */
                $transaction = $account->transactions->first();

                $connection
                    ->with()
                    ->query(sql: Queries::INSERT_TRANSACTION)
                    ->bind(data: [
                        ':id'              => $transaction->getId()->toString(),
                        ':amount'          => $transaction->getAmount()->toFloat(),
                        ':accountId'       => $accountId->toString(),
                        ':operationTypeId' => OperationType::WITHDRAWAL->value
                    ])
                    ->execute();
            }
        );
    }

    private function balanceOf(AccountId $id): Balance
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BALANCE)
            ->bind(data: [':accountId' => $id->toString()])
            ->execute()
            ->fetchOne();

        return BalanceRecord::from(result: $result)->toBalance();
    }
}
