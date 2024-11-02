<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Operations;

use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Models\Transaction\TransactionId;

final readonly class PurchaseWithInstallments implements Transaction
{
    private const DEFAULT_INSTALLMENTS = 1;

    private function __construct(private TransactionId $id, private NegativeAmount $amount, public int $installments)
    {
    }

    public static function createFrom(
        NegativeAmount $amount,
        int $installments = self::DEFAULT_INSTALLMENTS
    ): PurchaseWithInstallments {
        return new PurchaseWithInstallments(
            id: TransactionId::generate(),
            amount: $amount,
            installments: $installments
        );
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
