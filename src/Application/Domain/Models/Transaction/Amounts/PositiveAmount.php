<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use TinyBlocks\Math\PositiveBigDecimal;

final class PositiveAmount extends PositiveBigDecimal implements Amount
{
    private function __construct(public float $value)
    {
        parent::__construct(value: $value, scale: self::SCALE);
    }

    public static function from(float $value): PositiveAmount
    {
        return new PositiveAmount(value: abs($value));
    }
}
