<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Account\Query\Account\Database;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Dtos\Account;
use Doctrine\DBAL\Connection;

final readonly class Facade implements AccountQuery
{
    public function __construct(private Connection $connection)
    {
    }

    public function findAccountById(string $id): ?Account
    {
        $result = $this->connection
            ->executeQuery(sql: Queries::FIND_BY_ID, params: ['id' => $id])
            ->fetchAllAssociative();

        return empty($result) ? null : Account::from(data: $result[0]);
    }
}
