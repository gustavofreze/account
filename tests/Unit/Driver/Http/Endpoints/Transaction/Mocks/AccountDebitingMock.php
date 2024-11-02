<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Mocks;

use Account\Application\Domain\Commands\DebitAccount;
use Account\Application\Domain\Ports\Inbound\AccountDebiting;

final class AccountDebitingMock implements AccountDebiting
{
    public function handle(DebitAccount $command): void
    {
    }
}
