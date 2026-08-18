<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\InvalidAmount;
use Account\Application\Domain\Models\Commons\Decimal;

final readonly class PositiveOrZeroAmount implements Amount
{
    use AmountBehavior;

    private function __construct(float $value)
    {
        if ($value < 0) {
            throw new InvalidAmount(value: $value);
        }

        $this->value = Decimal::of(scale: self::SCALE, value: $value);
    }

    public static function from(float $value): PositiveOrZeroAmount
    {
        return new PositiveOrZeroAmount(value: $value);
    }
}
