<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction;

use Ramsey\Uuid\Uuid;

final readonly class TransactionId
{
    private function __construct(private string $value)
    {
    }

    public static function generate(): TransactionId
    {
        return new TransactionId(value: Uuid::uuid4()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
