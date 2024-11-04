<?php

declare(strict_types=1);

namespace Account\Query;

interface Filters
{
    /**
     * Creates a Filters instance from an array of data.
     *
     * @param array $data The data to create the Filters instance from.
     * @return Filters The created Filters instance.
     */
    public static function from(array $data): Filters;

    /**
     * Applies the filters to the given SQL query and parameters.
     *
     * @param string $query The SQL query to which the filters will be applied.
     * @param array $parameters The parameters to be used in the query.
     * @return PreparedQuery The modified query along with its parameters.
     */
    public function applyFiltersTo(string $query, array $parameters): PreparedQuery;
}
