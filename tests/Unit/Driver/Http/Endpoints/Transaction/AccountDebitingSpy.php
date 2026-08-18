<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Transaction;

use Account\Application\Commands\DebitAccount;
use Account\Application\Ports\Inbound\AccountDebiting;

final class AccountDebitingSpy implements AccountDebiting
{
    public function handle(DebitAccount $command): void
    {
    }
}
