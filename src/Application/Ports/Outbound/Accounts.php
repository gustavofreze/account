<?php

declare(strict_types=1);

namespace Account\Application\Ports\Outbound;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Transaction;

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
     * Applies a debit transaction to the specified account.
     *
     * @param Account $account The account to which the transaction is applied.
     * @param Transaction $transaction The transaction representing the debit operation.
     */
    public function applyDebitTransactionTo(Account $account, Transaction $transaction): void;

    /**
     * Applies a credit transaction to the specified account.
     *
     * @param Account $account The account to which the transaction is applied.
     */
    public function applyCreditTransactionTo(Account $account): void;

    /**
     * Applies a withdrawal transaction to the specified account.
     *
     * @param Account $account The account to which the transaction is applied.
     * @param Transaction $transaction The transaction representing the withdrawal operation.
     */
    public function applyWithdrawalTransactionTo(Account $account, Transaction $transaction): void;
}
