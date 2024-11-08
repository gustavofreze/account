<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Models\Transaction\Amounts\Amount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveOrZeroAmount;
use TinyBlocks\Math\BigDecimal;

final readonly class Balance
{
    private function __construct(public PositiveOrZeroAmount $amount)
    {
    }

    public static function from(float $value): Balance
    {
        return new Balance(amount: PositiveOrZeroAmount::from(value: $value));
    }

    public function hasSufficientFunds(Amount $amount): bool
    {
        $debitAmount = BigDecimal::fromFloat(value: $amount->toFloat(), scale: $amount::SCALE);
        $updatedAmount = $this->amount->subtract(subtrahend: $debitAmount->absolute());

        return !$updatedAmount->isNegative();
    }
}
