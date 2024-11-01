<?php

declare(strict_types=1);

namespace Account\Application\Domain\Handlers;

use Account\Application\Domain\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Domain\Ports\Inbound\AccountWithdrawal;
use Account\Application\Domain\Ports\Outbound\Accounts;

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

        $balance = $this->accounts->balanceOf(id: $id);
        $account = $account->withdraw(balance: $balance, transaction: $command->transaction);
        $this->accounts->applyTransactionTo(account: $account);
    }
}
