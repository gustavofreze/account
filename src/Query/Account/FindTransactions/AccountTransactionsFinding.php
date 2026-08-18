<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions;

use TinyBlocks\HttpQuery\Comparison;
use TinyBlocks\HttpQuery\Cursor\Keyset;
use TinyBlocks\HttpQuery\Cursor\Page;

/**
 * Finding of a forward-only cursor page of transactions scoped per account.
 */
interface AccountTransactionsFinding
{
    /**
     * Finds the next cursor page of transactions matching the keyset and the filter comparisons.
     *
     * @param Keyset $keyset The keyset carrying the page size, the orders, and the incoming cursor.
     * @param string $accountId The identifier of the account that owns the transactions.
     * @param list<Comparison> $comparisons The validated filter comparisons.
     * @return Page The cursor page carrying the transaction views and the next cursor.
     */
    public function findAll(Keyset $keyset, string $accountId, array $comparisons): Page;
}
