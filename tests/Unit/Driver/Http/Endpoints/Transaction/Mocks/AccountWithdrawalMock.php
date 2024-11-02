<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Mocks;

use Account\Application\Domain\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Domain\Ports\Inbound\AccountWithdrawal;

final class AccountWithdrawalMock implements AccountWithdrawal
{
    public function handle(RequestWithdrawal $command): void
    {
        $accountId = $command->id;

        if ($accountId->toString() === '07072a4b-ded7-41ea-a3e0-055678cb9a7b') {
            throw new AccountNotFound(id: $accountId);
        }
    }
}
