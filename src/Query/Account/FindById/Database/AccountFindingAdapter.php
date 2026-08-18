<?php

declare(strict_types=1);

namespace Account\Query\Account\FindById\Database;

use Account\Query\Account\FindById\AccountFinding;
use Account\Query\Account\FindById\ReadModel\Account;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Doctrine\DBAL\Connection;

final readonly class AccountFindingAdapter implements AccountFinding
{
    public function __construct(private Connection $connection)
    {
    }

    public function findById(string $accountId): Account
    {
        $row = $this->connection
            ->executeQuery(sql: Queries::FIND_BY_ID, params: ['accountId' => $accountId])
            ->fetchAssociative();

        if ($row === false) {
            throw new AccountNotFound();
        }

        return Account::from(id: (string)$row['id'], document: (string)$row['holderDocumentNumber']);
    }
}
