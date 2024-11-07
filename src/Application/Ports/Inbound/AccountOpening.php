<?php

declare(strict_types=1);

namespace Account\Application\Ports\Inbound;

use Account\Application\Commands\OpenAccount;

/**
 * Handles account opening requests by processing the specified command.
 */
interface AccountOpening
{
    /**
     * Processes the given account opening command.
     *
     * @param OpenAccount $command The command containing account opening details.
     */
    public function handle(OpenAccount $command): void;
}
