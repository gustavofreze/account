<?php

declare(strict_types=1);

namespace Account\Query\Account\FindBalance;

use Account\Query\Account\FindBalance\ReadModel\AccountBalance;

/**
 * Finding of the current balance of an account.
 */
interface AccountBalanceFinding
{
    /**
     * Finds the current balance of the account carrying the given identifier.
     *
     * @param string $accountId The identifier of the account.
     * @return AccountBalance The balance the account currently holds.
     */
    public function findBalance(string $accountId): AccountBalance;
}
