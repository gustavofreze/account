<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InsufficientFunds;
use Account\Application\Domain\Exceptions\InvalidTransaction;
use Account\Application\Domain\Models\Transaction\Amounts\Amount;
use Account\Application\Domain\Models\Transaction\Amounts\Decimal;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\PurchaseWithInstallments;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Domain\Models\Transaction\Transactions;

final readonly class Balance
{
    private function __construct(public Decimal $amount, public Transactions $transactions)
    {
    }

    public static function initialize(): Balance
    {
        return new Balance(amount: Decimal::fromZero(), transactions: Transactions::createFromEmpty());
    }

    public function apply(Transaction $transaction): Balance
    {
        $amount = $transaction->getAmount();
        $transactionType = $transaction::class;

        $balance = match ($transactionType) {
            CreditVoucher::class            => $this->credit(amount: $amount),
            NormalPurchase::class, Withdrawal::class,
            PurchaseWithInstallments::class => $this->debit(amount: $amount),
            default                         => throw new InvalidTransaction($transactionType)
        };

        $updatedTransactions = $this->transactions->add(elements: $transaction);

        return new Balance(amount: $balance, transactions: $updatedTransactions);
    }

    private function credit(Amount $amount): Decimal
    {
        $updatedAmount = $this->amount->add(addend: $amount);

        return Decimal::fromAmount(value: $updatedAmount);
    }

    private function debit(Amount $amount): Decimal
    {
        $debitAmount = Decimal::fromAmount(value: $amount);
        $updatedAmount = $this->amount->subtract(subtrahend: $debitAmount->absolute());

        if ($updatedAmount->isNegative()) {
            throw new InsufficientFunds();
        }

        return Decimal::fromAmount(value: $updatedAmount);
    }
}
