<?php

declare(strict_types=1);

namespace Account\Driven\Account\Outbox\Event;

use TinyBlocks\BuildingBlocks\Event\IntegrationEvent;

/**
 * Fact an account publishes for consumers outside the account domain.
 */
interface AccountIntegrationEvent extends IntegrationEvent
{
}
