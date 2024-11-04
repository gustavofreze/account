<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database\MySql;

use Account\Driven\Shared\Database\QueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;

final class MySqlQueryBuilder implements QueryBuilder
{
    private Result $result;

    private Statement $statement;

    public function __construct(private readonly Connection $connection)
    {
    }

    public function bind(array $data): MySqlQueryBuilder
    {
        foreach ($data as $column => $value) {
            $this->statement->bindValue($column, $value);
        }

        return $this;
    }

    public function query(string $sql): MySqlQueryBuilder
    {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }

    public function execute(): MySqlQueryBuilder
    {
        $this->result = $this->statement->executeQuery();

        return $this;
    }

    public function fetchOne(): array
    {
        return $this->result->fetchAssociative() ?: [];
    }

    public function fetchOneOrNull(): ?array
    {
        $row = $this->result->fetchAssociative();

        return $row === false ? null : $row;
    }
}
