<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

final readonly class Balance
{
    private function __construct(public float $amount)
    {
    }

    public static function from(array $data): Balance
    {
        return new Balance(amount: (float)$data['amount']);
    }

    public function toArray(): array
    {
        return ['amount' => $this->amount];
    }
}
