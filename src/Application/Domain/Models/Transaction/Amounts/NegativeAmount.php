<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Models\Commons\Decimal;

final readonly class NegativeAmount implements Amount
{
    use AmountBehavior;

    private function __construct(float $value)
    {
        $this->value = Decimal::of(scale: self::SCALE, value: -abs($value));
    }

    public static function from(float $value): NegativeAmount
    {
        return new NegativeAmount(value: $value);
    }
}
