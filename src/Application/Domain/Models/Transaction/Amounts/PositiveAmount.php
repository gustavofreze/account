<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\NonPositiveAmount;
use TinyBlocks\Math\Internal\Exceptions\NonPositiveValue as NonPositiveNumberException;
use TinyBlocks\Math\PositiveBigDecimal;

final class PositiveAmount extends PositiveBigDecimal implements Amount
{
    private function __construct(public float $value)
    {
        try {
            parent::__construct(value: $value, scale: self::SCALE);
        } catch (NonPositiveNumberException) {
            throw new NonPositiveAmount(value: $value, scale: self::SCALE);
        }
    }

    public static function from(float $value): PositiveAmount
    {
        return new PositiveAmount(value: $value);
    }
}
