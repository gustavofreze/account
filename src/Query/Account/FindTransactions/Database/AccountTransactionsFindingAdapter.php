<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Database;

use Account\Query\Account\FindTransactions\AccountTransactionsFinding;
use Account\Query\Account\FindTransactions\ReadModel\AccountTransaction;
use Account\Query\Account\Shared\Database\RegisteredAccounts;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Doctrine\DBAL\Connection;
use TinyBlocks\HttpQuery\Cursor\Keyset;
use TinyBlocks\HttpQuery\Cursor\Page;

final readonly class AccountTransactionsFindingAdapter implements AccountTransactionsFinding
{
    public function __construct(private RegisteredAccounts $accounts, private Connection $connection)
    {
    }

    public function findAll(Keyset $keyset, string $accountId, array $comparisons): Page
    {
        if (!$this->accounts->contains(accountId: $accountId)) {
            throw new AccountNotFound();
        }

        $query = AccountTransactionsKeysetQuery::from(
            keyset: $keyset,
            accountId: $accountId,
            comparisons: $comparisons
        );

        $rows = $this->connection
            ->executeQuery(sql: $query->sql, params: $query->parameters)
            ->fetchAllAssociative();

        return $keyset
            ->page(items: $rows)
            ->map(transformation: static function (array $row): array {
                return AccountTransaction::from(
                    id: (string)$row['id'],
                    amount: (float)$row['amount'],
                    accountId: (string)$row['account_id'],
                    createdAt: (string)$row['created_at'],
                    operationTypeId: (int)$row['operation_type_id']
                )->toArray();
            });
    }
}
