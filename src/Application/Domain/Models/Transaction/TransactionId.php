<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction;

use Account\Application\Domain\Models\Commons\UniqueIdentifier;

final readonly class TransactionId
{
    private function __construct(private string $value)
    {
    }

    public static function generate(): TransactionId
    {
        return new TransactionId(value: UniqueIdentifier::generate()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
