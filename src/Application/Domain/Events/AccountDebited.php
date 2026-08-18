<?php

declare(strict_types=1);

namespace Account\Application\Domain\Events;

use Account\Application\Domain\Events\Commons\DomainEventBehavior;
use Account\Application\Domain\Models\Transaction\Amounts\Amount;
use Account\Application\Domain\Models\Transaction\TransactionId;

final readonly class AccountDebited implements AccountEvent
{
    use DomainEventBehavior;

    public function __construct(public Amount $amount, public TransactionId $transactionId)
    {
    }

    public function eventType(): string
    {
        return 'AccountDebited';
    }
}
