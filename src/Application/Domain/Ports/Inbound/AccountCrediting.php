<?php

declare(strict_types=1);

namespace Account\Application\Domain\Ports\Inbound;

use Account\Application\Domain\Commands\CreditAccount;

/**
 * Handles account crediting requests by processing the specified command.
 */
interface AccountCrediting
{
    /**
     * Processes the given account crediting command.
     *
     * @param CreditAccount $command The command containing crediting details.
     */
    public function handle(CreditAccount $command): void;
}
