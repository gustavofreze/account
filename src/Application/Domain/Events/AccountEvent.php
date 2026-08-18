<?php

declare(strict_types=1);

namespace Account\Application\Domain\Events;

use Account\Application\Domain\Events\Commons\DomainEvent;

/**
 * Fact recorded by an account about something that happened to it.
 */
interface AccountEvent extends DomainEvent
{
}
