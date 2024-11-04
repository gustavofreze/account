<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InsufficientFunds;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Models\Transaction\Transactions;

final class Account
{
    public Transactions $transactions;

    public function __construct(public AccountId $id, public Holder $holder)
    {
        $this->transactions = Transactions::createFromEmpty();
    }

    public static function openFrom(AccountId $id, Holder $holder): Account
    {
        return new Account(id: $id, holder: $holder);
    }

    public function credit(Transaction $transaction): Account
    {
        $this->transactions->add(elements: $transaction);

        return $this;
    }

    public function debit(Balance $balance, Transaction $transaction): Account
    {
        if ($balance->hasSufficientFunds(amount: $transaction->getAmount())) {
            $this->transactions->add(elements: $transaction);
            return $this;
        }

        throw new InsufficientFunds(accountId: $this->id);
    }

    public function withdraw(Balance $balance, Transaction $transaction): Account
    {
        return $this->debit(balance: $balance, transaction: $transaction);
    }
}
