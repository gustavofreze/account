<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database;

interface QueryBuilder
{
    /**
     * Bind values to parameters in the SQL query.
     *
     * @param array $data An associative array where keys are column names and values are the corresponding values.
     * @return QueryBuilder The current instance for method chaining.
     */
    public function bind(array $data): QueryBuilder;

    /**
     * Set the query string for the statement.
     *
     * @param string $sql The SQL query string.
     * @return QueryBuilder The current instance for method chaining.
     */
    public function query(string $sql): QueryBuilder;

    /**
     * Execute the prepared statement.
     *
     * @return QueryBuilder The current instance for method chaining.
     */
    public function execute(): QueryBuilder;

    /**
     * Fetch a single row from the result set.
     *
     * @return array|null The first row from the result set, or null if no rows are found.
     */
    public function fetchOne(): ?array;
}
