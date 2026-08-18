<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\CreditAccount;
use Account\Application\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountCrediting;
use Account\Application\Ports\Outbound\Accounts;

final readonly class AccountCreditingHandler implements AccountCrediting
{
    public function __construct(private Accounts $accounts)
    {
    }

    public function handle(CreditAccount $command): void
    {
        $id = $command->id;
        $account = $this->accounts->findById(id: $id);

        if (is_null($account)) {
            throw new AccountNotFound();
        }

        $transaction = $command->transaction;

        $account->credit(transaction: $transaction);

        $this->accounts->credit(account: $account, transaction: $transaction);
    }
}
