<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database;

use Closure;

interface RelationalConnection
{
    /**
     * Get a QueryBuilder instance for building SQL queries.
     *
     * @return QueryBuilder The QueryBuilder instance.
     */
    public function with(): QueryBuilder;

    /**
     * Execute a set of operations within a transaction.
     *
     * @param Closure $useCase The closure containing the operations to be executed within the transaction.
     * @return void
     * @throws DatabaseFailure In case of a transaction failure.
     */
    public function inTransaction(Closure $useCase): void;
}
