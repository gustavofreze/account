<?php

declare(strict_types=1);

namespace Account\Application\Ports\Inbound;

use Account\Application\Commands\DebitAccount;

/**
 * Handles account debiting requests by processing the specified command.
 */
interface AccountDebiting
{
    /**
     * Processes the given account debiting command.
     *
     * @param DebitAccount $command The command containing debiting details.
     */
    public function handle(DebitAccount $command): void;
}
