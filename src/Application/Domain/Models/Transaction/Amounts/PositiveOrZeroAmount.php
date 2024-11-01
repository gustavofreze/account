<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\NonPositiveOrZeroAmount;
use TinyBlocks\Math\BigDecimal;

final class PositiveOrZeroAmount extends BigDecimal implements Amount
{
    private function __construct(float $value)
    {
        if ($value < 0) {
            throw new NonPositiveOrZeroAmount(value: $value, scale: self::SCALE);
        }

        parent::__construct(value: $value, scale: self::SCALE);
    }

    public static function from(float $value): PositiveOrZeroAmount
    {
        return new PositiveOrZeroAmount(value: $value);
    }
}
