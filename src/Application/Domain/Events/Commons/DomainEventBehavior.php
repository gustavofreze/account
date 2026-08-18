<?php

declare(strict_types=1);

namespace Account\Application\Domain\Events\Commons;

use TinyBlocks\BuildingBlocks\Event\DomainEventBehavior as TinyBlocksDomainEventBehavior;

trait DomainEventBehavior
{
    use TinyBlocksDomainEventBehavior;
}
