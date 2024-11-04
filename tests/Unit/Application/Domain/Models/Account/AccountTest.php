<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InsufficientFunds;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\PurchaseWithInstallments;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Domain\Models\Transaction\Transaction;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testWithdrawalTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a balance of 100.00 is set as the initial account balance */
        $balance = Balance::from(value: 100.00);

        /** @And a withdrawal transaction of 50.00 is created */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When applying the withdrawal transaction to the account */
        $actual = $account->withdraw(balance: $balance, transaction: $transaction);

        /** @Then the account balance should be the sum of all transaction amounts, totaling 50.00 */
        $totalAmount = $actual
            ->transactions
            ->reduce(
                aggregator: fn(float $carry, Transaction $transaction): float => $carry
                    + abs($transaction->getAmount()->toFloat()),
                initial: 0.00
            );
        self::assertSame(50.00, $totalAmount);

        /** @And the transaction count should be 1 */
        self::assertSame(1, $actual->transactions->count());
    }

    public function testCreditVoucherTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );

        /** @And a credit transaction of 100.00 with operation type Credit Voucher */
        $transaction = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));

        /** @When applying the credit transaction to the account */
        $actual = $account->credit(transaction: $transaction);

        /** @Then the account balance should reflect the credited amount of 100.00 */
        self::assertSame(100.00, $actual->transactions->first()->amount->value);

        /** @And the transaction count should be 1 */
        self::assertSame(1, $actual->transactions->count());
    }

    public function testNormalPurchaseTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a balance of 100.00 is set as the initial account balance */
        $balance = Balance::from(value: 100.00);

        /** @And a credit transaction of 100.00 is applied to the account */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @And a debit transaction of 50.00 is created with operation type Normal Purchase */
        $transaction = NormalPurchase::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When the debit transaction is applied to the account */
        $actual = $account->debit(balance: $balance, transaction: $transaction);

        /** @Then the account balance should be the sum of all transaction amounts, totaling 50.00 */
        $totalAmount = $actual->transactions->reduce(
            aggregator: fn(float $carry, Transaction $transaction): float => $carry + $transaction
                    ->getAmount()
                    ->toFloat(),
            initial: 0.00
        );
        self::assertSame(50.00, $totalAmount);

        /** @And the transaction count should be 2 */
        self::assertSame(2, $actual->transactions->count());
    }

    public function testInstallmentPurchaseTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );

        /** @And a balance of 100.00 is set as the initial account balance */
        $balance = Balance::from(value: 100.00);

        /** @And a debit transaction of 50.00 with operation type Purchase With Installments is created */
        $transaction = PurchaseWithInstallments::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When applying the debit transaction to the account */
        $actual = $account->debit(balance: $balance, transaction: $transaction);

        /** @Then the account balance should be the sum of all transaction amounts, totaling 50.00 */
        $totalAmount = $actual
            ->transactions
            ->reduce(
                aggregator: fn(float $carry, Transaction $transaction): float => $carry
                    + abs($transaction->getAmount()->toFloat()),
                initial: 0.00
            );
        self::assertSame(50.00, $totalAmount);

        /** @And the transaction count should be 1 */
        self::assertSame(1, $actual->transactions->count());
    }

    public function testExceptionWhenInsufficientFunds(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a balance of 0.00 is set as the initial account balance */
        $balance = Balance::from(value: 0.00);

        /** @And a withdrawal transaction of 50.00 is attempted, which exceeds the balance */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @Then an InsufficientFunds exception should be thrown */
        $template = 'Account with ID <%s> has insufficient funds.';
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessage(sprintf($template, $account->id->toString()));

        /** @When applying the withdrawal transaction to the account */
        $account->withdraw(balance: $balance, transaction: $transaction);
    }
}
