<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Models\Commons\ValueObject;
use Account\Application\Domain\Models\Commons\ValueObjectBehavior;
use Account\Application\Domain\Models\Transaction\Amounts\Amount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveOrZeroAmount;

final readonly class Balance implements ValueObject
{
    use ValueObjectBehavior;

    private function __construct(public PositiveOrZeroAmount $amount)
    {
    }

    public static function from(float $value): Balance
    {
        return new Balance(amount: PositiveOrZeroAmount::from(value: $value));
    }

    public function hasSufficientFunds(Amount $amount): bool
    {
        $remaining = $this->amount->toDecimal()->subtract(subtrahend: $amount->toDecimal()->absolute());

        return !$remaining->isNegative();
    }
}
