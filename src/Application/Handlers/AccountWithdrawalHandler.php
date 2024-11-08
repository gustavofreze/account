<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\AccountNotFound;
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

        if ($account === null) {
            throw new AccountNotFound(id: $id);
        }

        $this->accounts->applyWithdrawalTransactionTo(account: $account, transaction: $command->transaction);
    }
}
