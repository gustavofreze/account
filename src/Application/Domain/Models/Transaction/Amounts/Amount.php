<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Models\Commons\Decimal;
use Account\Application\Domain\Models\Commons\ValueObject;

/**
 * Monetary value of a transaction.
 */
interface Amount extends ValueObject
{
    /**
     * The scale for the amount's decimal precision.
     */
    public const int SCALE = 2;

    /**
     * Returns the amount as a decimal that arithmetic can be carried out on.
     *
     * @return Decimal The amount as a decimal.
     */
    public function toDecimal(): Decimal;

    /**
     * Returns the amount as a floating point number.
     *
     * @return float The amount.
     */
    public function toFloat(): float;
}
