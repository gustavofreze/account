<?php

declare(strict_types=1);

namespace Account\Application\Domain\Handlers;

use Account\Application\Domain\Commands\CreditAccount;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Domain\Ports\Inbound\AccountCrediting;
use Account\Application\Domain\Ports\Outbound\Accounts;

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
        $this->accounts->applyTransactionTo(account: $account);
    }
}
