<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Transaction;

use Account\Application\Commands\CreditAccount;
use Account\Application\Ports\Inbound\AccountCrediting;
use RuntimeException;

final class AccountCreditingSpy implements AccountCrediting
{
    public function handle(CreditAccount $command): void
    {
        if ($command->id->identityValue() === '2ab2ea68-2b17-4932-aa3a-1a47a84960da') {
            throw new RuntimeException('An unexpected error occurred.');
        }
    }
}
