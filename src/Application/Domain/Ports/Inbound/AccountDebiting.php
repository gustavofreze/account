<?php

declare(strict_types=1);

namespace Account\Application\Domain\Ports\Inbound;

use Account\Application\Domain\Commands\DebitAccount;

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
