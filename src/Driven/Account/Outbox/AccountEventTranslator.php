<?php

declare(strict_types=1);

namespace Account\Driven\Account\Outbox;

use Account\Application\Domain\Events\AccountCredited as CreditedFact;
use Account\Application\Domain\Events\AccountDebited as DebitedFact;
use Account\Application\Domain\Events\AccountEvent;
use Account\Application\Domain\Events\AccountOpened as OpenedFact;
use Account\Application\Domain\Events\AccountWithdrawn as WithdrawnFact;
use Account\Driven\Account\Outbox\Event\AccountCredited;
use Account\Driven\Account\Outbox\Event\AccountDebited;
use Account\Driven\Account\Outbox\Event\AccountOpened;
use Account\Driven\Account\Outbox\Event\AccountWithdrawn;
use TinyBlocks\BuildingBlocks\Event\EventRecord;
use TinyBlocks\BuildingBlocks\Event\IntegrationEvent;
use TinyBlocks\BuildingBlocks\Event\IntegrationEventTranslator;

final readonly class AccountEventTranslator implements IntegrationEventTranslator
{
    public function supports(EventRecord $record): bool
    {
        return $record->event instanceof AccountEvent;
    }

    public function translate(EventRecord $record): IntegrationEvent
    {
        $event = $record->event;

        if ($event instanceof OpenedFact) {
            return new AccountOpened(holderDocumentNumber: $event->holder->document->getNumber());
        }

        if ($event instanceof CreditedFact) {
            return new AccountCredited(
                amount: $event->amount->toFloat(),
                transactionId: $event->transactionId->toString()
            );
        }

        if ($event instanceof DebitedFact) {
            return new AccountDebited(
                amount: $event->amount->toFloat(),
                transactionId: $event->transactionId->toString()
            );
        }

        /** @var WithdrawnFact $event */
        return new AccountWithdrawn(
            amount: $event->amount->toFloat(),
            transactionId: $event->transactionId->toString()
        );
    }
}
