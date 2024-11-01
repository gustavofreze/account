<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use TinyBlocks\Math\NegativeBigDecimal;

final class NegativeAmount extends NegativeBigDecimal implements Amount
{
    private function __construct(public float $value)
    {
        parent::__construct(value: $value, scale: self::SCALE);
    }

    public static function from(float $value): NegativeAmount
    {
        return new NegativeAmount(value: -abs($value));
    }
}
