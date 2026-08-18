<?php

declare(strict_types=1);

namespace Test\Unit\Driven\Account\Repository;

use Account\Driven\Shared\Database\DatabaseFailure;
use Account\Driven\Shared\Database\RelationalConnection;
use Account\Driven\Shared\Database\Result;
use Account\Driven\Shared\Database\Row;
use Closure;

final class RelationalConnectionSpy implements RelationalConnection
{
    private const int NO_AFFECTED_ROWS = 0;

    private array $statements = [];

    public function __construct(private readonly ?DatabaseFailure $failureOnExecute = null)
    {
    }

    public function execute(string $sql, array $bindings = []): Result
    {
        $this->statements[] = $sql;

        if (!is_null($this->failureOnExecute)) {
            throw $this->failureOnExecute;
        }

        return new Result(affectedRows: self::NO_AFFECTED_ROWS);
    }

    public function fetchOne(string $sql, array $bindings = []): Row
    {
        $this->statements[] = $sql;

        return Row::from(values: null);
    }

    public function inTransaction(Closure $useCase): mixed
    {
        return $useCase($this);
    }

    public function statementsIssued(): int
    {
        return count($this->statements);
    }
}
