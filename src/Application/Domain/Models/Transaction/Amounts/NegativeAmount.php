<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\NonNegativeAmount;
use TinyBlocks\Math\Internal\Exceptions\NonNegativeValue as NonNegativeNumberException;
use TinyBlocks\Math\NegativeBigDecimal;

final class NegativeAmount extends NegativeBigDecimal implements Amount
{
    private function __construct(public float $value)
    {
        try {
            parent::__construct(value: $value, scale: self::SCALE);
        } catch (NonNegativeNumberException) {
            throw new NonNegativeAmount(value: $value, scale: self::SCALE);
        }
    }

    public static function from(float $value): NegativeAmount
    {
        return new NegativeAmount(value: $value);
    }
}
