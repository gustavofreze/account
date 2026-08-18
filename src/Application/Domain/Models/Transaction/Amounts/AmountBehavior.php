<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Models\Commons\Decimal;
use Account\Application\Domain\Models\Commons\ValueObjectBehavior;

trait AmountBehavior
{
    use ValueObjectBehavior;

    private readonly Decimal $value;

    public function toDecimal(): Decimal
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return $this->value->toFloat();
    }
}
