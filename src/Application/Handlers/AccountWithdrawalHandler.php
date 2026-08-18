<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountWithdrawal;
use Account\Application\Ports\Outbound\Accounts;

final readonly class AccountWithdrawalHandler implements AccountWithdrawal
{
    public function __construct(private Accounts $accounts)
    {
    }

    public function handle(RequestWithdrawal $command): void
    {
        $id = $command->id;
        $account = $this->accounts->findById(id: $id);

        if (is_null($account)) {
            throw new AccountNotFound();
        }

        $this->accounts->withdraw(account: $account, transaction: $command->transaction);
    }
}
