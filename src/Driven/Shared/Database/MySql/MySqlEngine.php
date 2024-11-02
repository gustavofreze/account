<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database\MySql;

use Account\Driven\Shared\Database\DatabaseFailure;
use Account\Driven\Shared\Database\RelationalConnection;
use Closure;
use Doctrine\DBAL\Connection;
use Throwable;

final class MySqlEngine implements RelationalConnection
{
    private MySqlQueryBuilder $queryBuilder;

    public function __construct(private readonly Connection $connection)
    {
        $this->queryBuilder = new MySqlQueryBuilder(connection: $this->connection);
    }

    public function with(): MySqlQueryBuilder
    {
        return $this->queryBuilder;
    }

    public function inTransaction(Closure $useCase): void
    {
        try {
            $this->connection->beginTransaction();
            $useCase($this);
            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new DatabaseFailure(message: $exception->getMessage());
        }
    }
}
