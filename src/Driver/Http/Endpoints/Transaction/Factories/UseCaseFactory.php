<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Factories;

use Account\Application\Domain\Commands\Command;
use Account\Application\Domain\Commands\CreditAccount;
use Account\Application\Domain\Commands\DebitAccount;
use Account\Application\Domain\Commands\RequestWithdrawal;
use Account\Application\Domain\Ports\Inbound\AccountCrediting;
use Account\Application\Domain\Ports\Inbound\AccountDebiting;
use Account\Application\Domain\Ports\Inbound\AccountWithdrawal;
use InvalidArgumentException;

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
        $template = 'Unsupported command <%s>.';

        match (get_class($command)) {
            DebitAccount::class      => $this->accountDebiting->handle(command: $command),
            CreditAccount::class     => $this->accountCrediting->handle(command: $command),
            RequestWithdrawal::class => $this->accountWithdrawal->handle(command: $command),
            default                  => throw new InvalidArgumentException(
                message: sprintf($template, get_class($command))
            )
        };
    }
}
