<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\Account\Dtos\Account;

interface AccountQuery
{
    /**
     * Find an account by its unique identifier.
     *
     * @param string $id The unique identifier of the account.
     * @return Account|null The account if found, or null if not found.
     */
    public function findAccountById(string $id): ?Account;
}
