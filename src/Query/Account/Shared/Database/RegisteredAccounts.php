<?php

declare(strict_types=1);

namespace Account\Query\Account\Shared\Database;

use Doctrine\DBAL\Connection;

final readonly class RegisteredAccounts
{
    public function __construct(private Connection $connection)
    {
    }

    public function contains(string $accountId): bool
    {
        $row = $this->connection
            ->executeQuery(sql: Queries::EXISTS_BY_ID, params: ['accountId' => $accountId])
            ->fetchOne();

        return $row !== false;
    }
}
