<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Operations;

use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Models\Transaction\TransactionId;

final readonly class CreditVoucher implements Transaction
{
    private function __construct(public TransactionId $id, public PositiveAmount $amount)
    {
    }

    public static function createFrom(PositiveAmount $amount): CreditVoucher
    {
        return new CreditVoucher(id: TransactionId::generate(), amount: $amount);
    }

    public function getId(): TransactionId
    {
        return $this->id;
    }

    public function getAmount(): PositiveAmount
    {
        return $this->amount;
    }
}
