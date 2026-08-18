<?php

declare(strict_types=1);

namespace Test\Unit\Driven\Shared\Database;

use Closure;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Throwable;

final class ConnectionSpy extends Connection
{
    public array $statements = [];
    public array $connectionSettings = [];
    public false|array $fetchedRow = false;
    public bool $transactionalUsed = false;
    public ?Throwable $failure = null;

    public function __construct(array $args = [], ?Driver $engine = null, ?Configuration $options = null)
    {
        $this->connectionSettings = [$args, $engine, $options];
    }

    public function executeStatement(string $sql, array $params = [], array $types = []): int
    {
        $this->statements[] = ['sql' => $sql, 'params' => $params];

        if (!is_null($this->failure)) {
            throw $this->failure;
        }

        return 1;
    }

    public function fetchAssociative(string $query, array $params = [], array $types = []): false|array
    {
        $this->statements[] = ['sql' => $query, 'params' => $params];

        if (!is_null($this->failure)) {
            throw $this->failure;
        }

        return $this->fetchedRow;
    }

    public function transactional(Closure $func): mixed
    {
        $this->transactionalUsed = true;

        if (!is_null($this->failure)) {
            throw $this->failure;
        }

        return $func($this);
    }
}
