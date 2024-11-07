<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Mocks;

use Account\Application\Commands\CreditAccount;
use Account\Application\Ports\Inbound\AccountCrediting;
use PHPUnit\Framework\MockObject\Generator\RuntimeException;

final class AccountCreditingMock implements AccountCrediting
{
    public function handle(CreditAccount $command): void
    {
        if ($command->id->toString() === '2ab2ea68-2b17-4932-aa3a-1a47a84960da') {
            throw new RuntimeException('An unexpected error occurred.');
        }
    }
}
