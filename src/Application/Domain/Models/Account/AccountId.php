<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Models\Commons\AggregateIdentity;
use Account\Application\Domain\Models\Commons\UniqueIdentifier;
use Account\Application\Domain\Models\Commons\ValueObject;
use Account\Application\Domain\Models\Commons\ValueObjectBehavior;

final readonly class AccountId implements AggregateIdentity, ValueObject
{
    use ValueObjectBehavior;

    public function __construct(private string $value)
    {
    }

    public static function generate(): AccountId
    {
        return new AccountId(value: UniqueIdentifier::generate()->toString());
    }

    public function identityValue(): string
    {
        return $this->value;
    }
}
