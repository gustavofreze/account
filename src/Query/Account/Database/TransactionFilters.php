<?php

declare(strict_types=1);

namespace Account\Query\Account\Database;

use Account\Query\Filters;
use Account\Query\PreparedQuery;

final readonly class TransactionFilters implements Filters
{
    private const int START_INDEX = 0;

    private function __construct(private ?array $operationTypeIds)
    {
    }

    public static function from(array $data): TransactionFilters
    {
        $operationTypeIds = $data['operationTypeIds'] ?? null;

        return new TransactionFilters(operationTypeIds: $operationTypeIds);
    }

    public function applyFiltersTo(string $query, array $parameters): PreparedQuery
    {
        $whereClauses = [];

        if (!empty($this->operationTypeIds)) {
            $placeholders = array_fill(self::START_INDEX, count($this->operationTypeIds), '?');
            $whereClauses[] = sprintf('operation_type_id IN (%s)', implode(',', $placeholders));

            foreach ($this->operationTypeIds as $id) {
                $parameters[] = $id;
            }
        }

        if (!empty($whereClauses)) {
            $query .= sprintf(' AND %s', implode(' AND ', $whereClauses));
        }

        $query .= ' ORDER BY created_at DESC';

        return PreparedQuery::from(query: $query, parameters: $parameters);
    }
}
