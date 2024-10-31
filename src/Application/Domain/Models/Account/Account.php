<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Models\Transaction\Transaction;

final readonly class Account
{
    private function __construct(public AccountId $id, public Holder $holder, public Balance $balance)
    {
    }

    public static function createFrom(Holder $holder): Account
    {
        return new Account(
            id: AccountId::generate(),
            holder: $holder,
            balance: Balance::initialize()
        );
    }

    public function credit(Transaction $transaction): Account
    {
        $updatedBalance = $this->balance->apply(transaction: $transaction);

        return new Account(
            id: $this->id,
            holder: $this->holder,
            balance: $updatedBalance
        );
    }

    public function debit(Transaction $transaction): Account
    {
        $updatedBalance = $this->balance->apply(transaction: $transaction);

        return new Account(
            id: $this->id,
            holder: $this->holder,
            balance: $updatedBalance
        );
    }

    public function withdraw(Transaction $transaction): Account
    {
        return $this->debit(transaction: $transaction);
    }
}
