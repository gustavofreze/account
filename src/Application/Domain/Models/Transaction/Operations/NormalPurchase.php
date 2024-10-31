<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Operations;

use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Models\Transaction\TransactionId;

final readonly class NormalPurchase implements Transaction
{
    private function __construct(private TransactionId $id, private NegativeAmount $amount)
    {
    }

    public static function createFrom(NegativeAmount $amount): NormalPurchase
    {
        return new NormalPurchase(id: TransactionId::generate(), amount: $amount);
    }

    public function getId(): TransactionId
    {
        return $this->id;
    }

    public function getAmount(): NegativeAmount
    {
        return $this->amount;
    }
}
