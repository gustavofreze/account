<?php

declare(strict_types=1);

namespace Account\Driven\Account;

use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\PurchaseWithInstallments;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Domain\Models\Transaction\Transaction;
use InvalidArgumentException;

enum OperationType: int
{
    case WITHDRAWAL = 3;
    case CREDIT_VOUCHER = 4;
    case NORMAL_PURCHASE = 1;
    case PURCHASE_WITH_INSTALLMENTS = 2;

    public static function fromDebitTransaction(Transaction $transaction): OperationType
    {
        $type = get_class($transaction);
        $template = 'Unsupported transaction type <%s>.';

        return match ($type) {
            NormalPurchase::class           => self::NORMAL_PURCHASE,
            PurchaseWithInstallments::class => self::PURCHASE_WITH_INSTALLMENTS,
            default                         => throw new InvalidArgumentException(message: sprintf($template, $type))
        };
    }

    public function toTransaction(float $amount): Transaction
    {
        return match ($this) {
            self::WITHDRAWAL                 => Withdrawal::createFrom(amount: NegativeAmount::from(value: $amount)),
            self::CREDIT_VOUCHER             => CreditVoucher::createFrom(amount: PositiveAmount::from(value: $amount)),
            self::NORMAL_PURCHASE            => NormalPurchase::createFrom(
                amount: NegativeAmount::from(value: $amount)
            ),
            self::PURCHASE_WITH_INSTALLMENTS => PurchaseWithInstallments::createFrom(
                amount: NegativeAmount::from(value: $amount)
            )
        };
    }
}
