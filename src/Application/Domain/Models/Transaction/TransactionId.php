<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction;

use TinyBlocks\Ksuid\Ksuid;

final readonly class TransactionId
{
    private function __construct(private string $value)
    {
    }

    public static function generate(): TransactionId
    {
        return new TransactionId(value: Ksuid::random()->getValue());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
