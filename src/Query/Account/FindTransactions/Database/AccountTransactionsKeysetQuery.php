<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Database;

use TinyBlocks\HttpQuery\Clause\Every;
use TinyBlocks\HttpQuery\Clause\FilterColumns;
use TinyBlocks\HttpQuery\Clause\Filters;
use TinyBlocks\HttpQuery\Clause\SeekClause;
use TinyBlocks\HttpQuery\Clause\SortClause;
use TinyBlocks\HttpQuery\Cursor\Keyset;

final readonly class AccountTransactionsKeysetQuery
{
    private function __construct(public string $sql, public array $parameters)
    {
    }

    public static function from(Keyset $keyset, string $accountId, array $comparisons): AccountTransactionsKeysetQuery
    {
        $columns = FilterColumns::create()
            ->plain(field: 'created_at', column: 'txn.created_at')
            ->plain(field: 'operation_type_id', column: 'txn.operation_type_id')
            ->wrapped(field: 'id', column: 'txn.id', binding: 'UUID_TO_BIN(%s)');

        $scope = AccountTransactionsScope::from(accountId: $accountId);
        $filters = Filters::from(columns: $columns, comparisons: $comparisons);
        $seek = SeekClause::from(keyset: $keyset, columns: $columns);

        $predicate = Every::of($scope, $filters, $seek);

        $sort = SortClause::from(orders: $keyset->orders(), columns: $columns);
        $limit = $keyset->limit()->plusOne();

        $template = '%s WHERE %s ORDER BY %s LIMIT %d';
        $sql = sprintf($template, Queries::BASE, $predicate->sql(), $sort->sql(), $limit->toInteger());

        return new AccountTransactionsKeysetQuery(sql: $sql, parameters: $predicate->parameters());
    }
}
