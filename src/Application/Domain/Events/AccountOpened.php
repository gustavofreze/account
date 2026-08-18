<?php

declare(strict_types=1);

namespace Account\Application\Domain\Events;

use Account\Application\Domain\Events\Commons\DomainEventBehavior;
use Account\Application\Domain\Models\Account\Holder;

final readonly class AccountOpened implements AccountEvent
{
    use DomainEventBehavior;

    public function __construct(public Holder $holder)
    {
    }

    public function eventType(): string
    {
        return 'AccountOpened';
    }
}
