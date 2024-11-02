<?php

declare(strict_types=1);

namespace Test\Integration\Query;

use Account\Query\Account\Database\Records\Account;
use Doctrine\DBAL\Connection;

final readonly class Repository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(Account $account): void
    {
        $query = 'INSERT INTO accounts (id, holder_document_number) VALUES (UUID_TO_BIN(:id), :holderDocumentNumber)';
        $this->connection->executeStatement($query, [
            'id'                   => $account->id,
            'holderDocumentNumber' => $account->holder->document
        ]);
    }
}
