<?php

declare(strict_types=1);

namespace Test\Integration\Query;

use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Transaction;
use Doctrine\DBAL\Connection;
use TinyBlocks\Collection\Collection;

final readonly class Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function saveAccount(Account $account): void
    {
        $query = 'INSERT INTO accounts (id, holder_document_number) VALUES (UUID_TO_BIN(:id), :holderDocumentNumber)';

        $this->connection->executeStatement($query, [
            'id'                   => $account->id,
            'holderDocumentNumber' => $account->holder->document
        ]);
    }

    public function saveTransactions(Collection $transactions): void
    {
        $query = 'INSERT INTO transactions (id, account_id, operation_type_id, amount, created_at)
                  VALUES (UUID_TO_BIN(:id), UUID_TO_BIN(:accountId), :type, :amount, :createdAt)';

        $transactions->each(actions: fn(Transaction $transaction) => $this->connection->executeStatement($query, [
            'id'        => $transaction->id,
            'type'      => $transaction->operationTypeId,
            'amount'    => $transaction->amount,
            'createdAt' => $transaction->createdAt,
            'accountId' => $transaction->accountId
        ]));
    }
}
