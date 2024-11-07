<?php

declare(strict_types=1);

namespace Account\Application\Ports\Inbound;

use Account\Application\Commands\RequestWithdrawal;

/**
 * Handles withdrawal requests for an account by processing the specified command.
 */
interface AccountWithdrawal
{
    /**
     * Processes the given withdrawal request command.
     *
     * @param RequestWithdrawal $command The command containing withdrawal details.
     */
    public function handle(RequestWithdrawal $command): void;
}
