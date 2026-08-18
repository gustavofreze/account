<?php

declare(strict_types=1);

namespace Account\Application\Ports\Outbound;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Transaction\Transaction;

/**
 * Accounts held by cardholders.
 */
interface Accounts
{
    /**
     * Records the opened account.
     *
     * @param Account $account The account that was opened.
     */
    public function save(Account $account): void;

    /**
     * Returns the account carrying the given identifier.
     *
     * @param AccountId $id The identifier of the account.
     * @return Account|null The account carrying the identifier, or null when no account carries it.
     */
    public function findById(AccountId $id): ?Account;

    /**
     * Credits the given transaction to the account.
     *
     * @param Account $account The account being credited.
     * @param Transaction $transaction The transaction being credited.
     */
    public function credit(Account $account, Transaction $transaction): void;

    /**
     * Debits the given transaction from the account.
     *
     * @param Account $account The account being debited.
     * @param Transaction $transaction The transaction being debited.
     */
    public function debit(Account $account, Transaction $transaction): void;

    /**
     * Withdraws the given transaction from the account.
     *
     * @param Account $account The account being withdrawn from.
     * @param Transaction $transaction The transaction being withdrawn.
     */
    public function withdraw(Account $account, Transaction $transaction): void;
}
