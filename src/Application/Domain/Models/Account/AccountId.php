<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Ramsey\Uuid\Uuid;

final readonly class AccountId
{
    public function __construct(private string $value)
    {
    }

    public static function generate(): AccountId
    {
        return new AccountId(value: Uuid::uuid4()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
