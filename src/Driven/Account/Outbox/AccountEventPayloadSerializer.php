<?php

declare(strict_types=1);

namespace Account\Driven\Account\Outbox;

use Account\Driven\Account\Outbox\Event\AccountIntegrationEvent;
use TinyBlocks\BuildingBlocks\Event\IntegrationEventRecord;
use TinyBlocks\Mapper\Serializer;
use TinyBlocks\Outbox\Serialization\PayloadSerializer;
use TinyBlocks\Outbox\Serialization\SerializedPayload;

final readonly class AccountEventPayloadSerializer implements PayloadSerializer
{
    public function __construct(private Serializer $mapper)
    {
    }

    public function supports(IntegrationEventRecord $record): bool
    {
        return $record->event instanceof AccountIntegrationEvent;
    }

    public function serialize(IntegrationEventRecord $record): SerializedPayload
    {
        return SerializedPayload::fromArray(payload: $this->mapper->toArray(source: $record->event));
    }
}
