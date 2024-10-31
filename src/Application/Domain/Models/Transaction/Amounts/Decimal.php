<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use TinyBlocks\Math\BigDecimal;
use TinyBlocks\Math\BigNumber;

final class Decimal extends BigDecimal implements Amount
{
    public static function fromZero(): Decimal
    {
        return new Decimal(value: 0.00, scale: self::SCALE);
    }

    public static function fromAmount(BigNumber $value): Decimal
    {
        return new Decimal(value: $value->toFloat(), scale: self::SCALE);
    }
}
