<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository\Records;

use Account\Application\Domain\Models\Account\Balance;

final readonly class BalanceRecord
{
    public function __construct(private array $record)
    {
    }

    public static function from(array $result): BalanceRecord
    {
        return new BalanceRecord(record: $result);
    }


    public function toBalance(): Balance
    {
        return Balance::from(value: (float)$this->record['amount']);
    }
}
