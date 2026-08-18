<?php

declare(strict_types=1);

namespace Account\Query\Account\FindBalance\ReadModel;

final readonly class AccountBalance
{
    private function __construct(public float $amount)
    {
    }

    public static function from(float $amount): AccountBalance
    {
        return new AccountBalance(amount: $amount);
    }

    public function toArray(): array
    {
        return ['amount' => $this->amount];
    }
}
