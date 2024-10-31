<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use TinyBlocks\Ksuid\Ksuid;

final readonly class AccountId
{
    private function __construct(private string $value)
    {
    }

    public static function generate(): AccountId
    {
        return new AccountId(value: Ksuid::random()->getValue());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
