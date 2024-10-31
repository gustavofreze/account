<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use TinyBlocks\Math\BigNumber;

/**
 * Represents the value of a transaction.
 */
interface Amount extends BigNumber
{
    /**
     * The scale for the amount's decimal precision.
     */
    public const SCALE = 2;
}
