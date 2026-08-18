<?php

declare(strict_types=1);

namespace Account\Driven\Account\Outbox\Event;

use TinyBlocks\BuildingBlocks\Event\IntegrationEventBehavior;

final readonly class AccountOpened implements AccountIntegrationEvent
{
    use IntegrationEventBehavior;

    public function __construct(public string $holderDocumentNumber)
    {
    }
}
