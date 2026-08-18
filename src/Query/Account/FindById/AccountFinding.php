<?php

declare(strict_types=1);

namespace Account\Query\Account\FindById;

use Account\Query\Account\FindById\ReadModel\Account;

/**
 * Finding of a single account by its identifier.
 */
interface AccountFinding
{
    /**
     * Finds the account carrying the given identifier.
     *
     * @param string $accountId The identifier of the account.
     * @return Account The account carrying the identifier.
     */
    public function findById(string $accountId): Account;
}
