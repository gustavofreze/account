<?php

declare(strict_types=1);

namespace Account\Application\Domain\Ports\Outbound;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Application\Domain\Models\Account\Holder;

interface Accounts
{
    /**
     * Saves an account.
     *
     * @param Account $account The account to be saved.
     */
    public function save(Account $account): void;

    /**
     * Finds an account by its unique identifier.
     *
     * @param AccountId $id The unique identifier of the account.
     * @return Account|null The account found, or null if no account exists with the specified ID.
     */
    public function findById(AccountId $id): ?Account;

    /**
     * Finds an account by its holder.
     *
     * @param Holder $holder The holder to look up.
     * @return Account|null The account found, or null if none exists for the specified holder.
     */
    public function findByHolder(Holder $holder): ?Account;

    /**
     * Retrieves the current balance of the specified account.
     *
     * @param AccountId $id The unique identifier of the account.
     * @return Balance The current balance of the account.
     */
    public function balanceOf(AccountId $id): Balance;

    /**
     * Applies a pending transaction and updates the balance of the specified account.
     *
     * @param Account $account The account to which the transaction is applied, with balance adjusted accordingly.
     */
    public function applyTransactionTo(Account $account): void;
}
