<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Balance;
use Account\Query\Account\Models\Transactions;
use Account\Query\Filters;

interface AccountQuery
{
    /**
     * Find an account by its unique identifier.
     *
     * @param string $accountId The unique identifier of the account.
     * @return Account|null The account found, or null if no account exists with the specified ID.
     */
    public function findById(string $accountId): ?Account;

    /**
     * Retrieves the current balance of the specified account.
     *
     * @param string $accountId The unique identifier of the account.
     * @return Balance The current balance of the account.
     */
    public function balanceOf(string $accountId): Balance;

    /**
     * Retrieves all transactions associated with the specified account.
     *
     * @param string $accountId The unique identifier of the account.
     * @param Filters $filters Optional filters to apply to the transaction retrieval.
     * @return Transactions A collection of transactions associated with the account.
     */
    public function transactionsOf(string $accountId, Filters $filters): Transactions;
}
