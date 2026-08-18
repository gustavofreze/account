<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Transaction\OperationType;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Ports\Outbound\Accounts;
use Account\Driven\Account\Repository\Records\AccountRecordReader;
use Account\Driven\Account\Repository\Records\AccountRecordWriter;
use Account\Driven\Shared\Database\RelationalConnection;
use TinyBlocks\Outbox\OutboxRepository;

final readonly class AccountRepository implements Accounts
{
    public function __construct(
        private OutboxRepository $outbox,
        private AccountRecordReader $reader,
        private AccountRecordWriter $writer,
        private Balances $balances,
        private RelationalConnection $connection
    ) {
    }

    public function save(Account $account): void
    {
        $this->connection->inTransaction(useCase: function () use ($account): void {
            $this->writer->insert(account: $account);

            $this->outbox->push(records: $account->peekEvents());
        });

        $account->pullEvents();
    }

    public function findById(AccountId $id): ?Account
    {
        return $this->connection
            ->fetchOne(sql: Queries::FIND_BY_ID, bindings: ['id' => $id->identityValue()])
            ->map(transform: fn(array $record): Account => $this->reader->toAccount(record: $record))
            ->getOrNull();
    }

    public function credit(Account $account, Transaction $transaction): void
    {
        $this->connection->inTransaction(useCase: function () use ($account, $transaction): void {
            $this->writer->record(
                account: $account,
                transaction: $transaction,
                operationType: OperationType::CREDIT_VOUCHER
            );

            $this->outbox->push(records: $account->peekEvents());
        });

        $account->pullEvents();
    }

    public function debit(Account $account, Transaction $transaction): void
    {
        $this->connection->inTransaction(useCase: function () use ($account, $transaction): void {
            $balance = $this->balances->of(id: $account->id);

            $account->debit(balance: $balance, transaction: $transaction);

            $this->writer->record(
                account: $account,
                transaction: $transaction,
                operationType: OperationType::fromDebitTransaction(transaction: $transaction)
            );

            $this->outbox->push(records: $account->peekEvents());
        });

        $account->pullEvents();
    }

    public function withdraw(Account $account, Transaction $transaction): void
    {
        $this->connection->inTransaction(useCase: function () use ($account, $transaction): void {
            $balance = $this->balances->of(id: $account->id);

            $account->withdraw(balance: $balance, transaction: $transaction);

            $this->writer->record(
                account: $account,
                transaction: $transaction,
                operationType: OperationType::WITHDRAWAL
            );

            $this->outbox->push(records: $account->peekEvents());
        });

        $account->pullEvents();
    }
}
