<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Factories;

use Account\Application\Commands\Command;
use Account\Application\Commands\CreditAccount;
use Account\Application\Commands\DebitAccount;
use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Ports\Inbound\AccountCrediting;
use Account\Application\Ports\Inbound\AccountDebiting;
use Account\Application\Ports\Inbound\AccountWithdrawal;

final readonly class UseCaseFactory
{
    public function __construct(
        private AccountDebiting $accountDebiting,
        private AccountCrediting $accountCrediting,
        private AccountWithdrawal $accountWithdrawal
    ) {
    }

    public function handle(Command $command): void
    {
        match (get_class($command)) {
            DebitAccount::class      => $this->accountDebiting->handle(command: $command),
            CreditAccount::class     => $this->accountCrediting->handle(command: $command),
            RequestWithdrawal::class => $this->accountWithdrawal->handle(command: $command)
        };
    }
}
