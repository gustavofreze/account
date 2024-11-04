<?php

declare(strict_types=1);

namespace Account\Query\Account\Database;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Balance;
use Account\Query\Account\Models\Transaction;
use Account\Query\Account\Models\Transactions;
use Account\Query\Filters;
use Doctrine\DBAL\Connection;

final readonly class Facade implements AccountQuery
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(string $accountId): ?Account
    {
        $result = $this->connection
            ->executeQuery(sql: Queries::FIND_BY_ID, params: ['accountId' => $accountId])
            ->fetchAllAssociative();

        return empty($result) ? null : Account::from(data: $result[0]);
    }

    public function balanceOf(string $accountId): Balance
    {
        $result = $this->connection
            ->executeQuery(sql: Queries::FIND_BALANCE, params: ['accountId' => $accountId])
            ->fetchAllAssociative();

        return Balance::from(data: $result[0]);
    }

    public function transactionsOf(string $accountId, Filters $filters): Transactions
    {
        $preparedQuery = $filters->applyFiltersTo(
            query: Queries::FIND_TRANSACTIONS,
            parameters: ['accountId' => $accountId]
        );

        $result = $this->connection
            ->executeQuery(sql: $preparedQuery->query, params: $preparedQuery->parameters)
            ->fetchAllAssociative();

        return empty($result)
            ? Transactions::createFromEmpty()
            : Transactions::createFrom(elements: $result)->map(
                transformations: fn(array $data): Transaction => Transaction::from(data: $data)
            );
    }
}
