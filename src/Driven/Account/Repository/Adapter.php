<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Ports\Outbound\Accounts;
use Account\Driven\Account\OperationType;
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
            ->fetchOne();

        return Record::from(result: $result)->toAccountOrNull();
    }

    public function findByHolder(Holder $holder): ?Account
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BY_HOLDER)
            ->bind(data: [':holderDocumentNumber' => $holder->document->getNumber()])
            ->execute()
            ->fetchOne();

        return Record::from(result: $result)->toAccountOrNull();
    }

    public function balanceOf(AccountId $id): Balance
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BALANCE)
            ->bind(data: [':accountId' => $id->toString()])
            ->execute()
            ->fetchOne();

        return Record::from(result: $result)->toBalance();
    }

    public function applyTransactionTo(Account $account): void
    {
        $this->connection->inTransaction(
            useCase: function (RelationalConnection $connection) use ($account) {
                $account
                    ->transactions
                    ->each(actions: function (Transaction $transaction) use ($account, $connection) {
                        $operationTypeId = OperationType::fromTransaction(transaction: $transaction);

                        $connection
                            ->with()
                            ->query(sql: Queries::INSERT_TRANSACTION)
                            ->bind(data: [
                                ':id'              => $transaction->getId()->toString(),
                                ':amount'          => $transaction->getAmount()->toFloat(),
                                ':accountId'       => $account->id->toString(),
                                ':operationTypeId' => $operationTypeId->value
                            ])
                            ->execute();
                    });
            }
        );
    }
}
