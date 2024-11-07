<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\CreditAccount;
use Account\Application\Domain\Exceptions\AccountNotFound;
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

        if ($account === null) {
            throw new AccountNotFound(id: $id);
        }

        $account = $account->credit(transaction: $command->transaction);
        $this->accounts->applyCreditTransactionTo(account: $account);
    }
}
