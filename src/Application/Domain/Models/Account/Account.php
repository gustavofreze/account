<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Events\AccountCredited;
use Account\Application\Domain\Events\AccountDebited;
use Account\Application\Domain\Events\AccountOpened;
use Account\Application\Domain\Events\AccountWithdrawn;
use Account\Application\Domain\Events\Commons\EventualAggregateRoot;
use Account\Application\Domain\Events\Commons\EventualAggregateRootBehavior;
use Account\Application\Domain\Exceptions\AccountWithInsufficientFunds;
use Account\Application\Domain\Models\Transaction\Transaction;

final class Account implements EventualAggregateRoot
{
    use EventualAggregateRootBehavior;

    public function __construct(public AccountId $id, public Holder $holder)
    {
    }

    public static function openFrom(AccountId $id, Holder $holder): Account
    {
        $account = new Account(id: $id, holder: $holder);

        $account->pushEvent(event: new AccountOpened(holder: $holder));

        return $account;
    }

    public function credit(Transaction $transaction): void
    {
        $this->pushEvent(
            event: new AccountCredited(
                amount: $transaction->getAmount(),
                transactionId: $transaction->getId()
            )
        );
    }

    public function debit(Balance $balance, Transaction $transaction): void
    {
        $this->refuseWithoutFunds(balance: $balance, transaction: $transaction);

        $this->pushEvent(
            event: new AccountDebited(
                amount: $transaction->getAmount(),
                transactionId: $transaction->getId()
            )
        );
    }

    public function withdraw(Balance $balance, Transaction $transaction): void
    {
        $this->refuseWithoutFunds(balance: $balance, transaction: $transaction);

        $this->pushEvent(
            event: new AccountWithdrawn(
                amount: $transaction->getAmount(),
                transactionId: $transaction->getId()
            )
        );
    }

    private function refuseWithoutFunds(Balance $balance, Transaction $transaction): void
    {
        if (!$balance->hasSufficientFunds(amount: $transaction->getAmount())) {
            throw new AccountWithInsufficientFunds(accountId: $this->id);
        }
    }
}
