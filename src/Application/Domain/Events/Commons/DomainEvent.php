<?php

declare(strict_types=1);

namespace Account\Application\Domain\Events\Commons;

use TinyBlocks\BuildingBlocks\Event\DomainEvent as TinyBlocksDomainEvent;

/**
 * Domain event emitted by an aggregate root within the account domain.
 */
interface DomainEvent extends TinyBlocksDomainEvent
{
}
