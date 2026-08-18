<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Transaction;

use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountWithdrawal;

final class AccountWithdrawalSpy implements AccountWithdrawal
{
    public function handle(RequestWithdrawal $command): void
    {
        $accountId = $command->id;

        if ($accountId->identityValue() === '07072a4b-ded7-41ea-a3e0-055678cb9a7b') {
            throw new AccountNotFound();
        }
    }
}
