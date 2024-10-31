<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InsufficientFunds;
use Account\Application\Domain\Models\Account\Documents\CNPJ;
use Account\Application\Domain\Models\Account\Documents\CPF;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\PurchaseWithInstallments;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testCreditVoucherTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::createFrom(holder: Holder::from(document: new CPF(number: '12345678901')));

        /** @Then the initial balance of this account should be 0.00 */
        self::assertSame(0.00, $account->balance->amount->toFloat());

        /** @And a credit transaction of 100.00 with operation type Credit Voucher */
        $transaction = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));

        /** @When applying the credit transaction to the account */
        $updatedAccount = $account->credit(transaction: $transaction);

        /** @Then the account balance should reflect the credited amount of 100.00 */
        self::assertSame(100.00, $updatedAccount->balance->amount->toFloat());

        /** @And the transaction count should be 1 */
        self::assertSame(1, $updatedAccount->balance->transactions->count());
    }

    public function testNormalPurchaseTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::createFrom(holder: Holder::from(document: new CNPJ(number: '33431849000179')));

        /** @And a credit transaction of 100.00 to initialize the balance */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @Then the balance should be 100.00 */
        self::assertSame(100.00, $account->balance->amount->toFloat());

        /** @And a debit transaction of 50.00 with operation type Normal Purchase */
        $transaction = NormalPurchase::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When applying the debit transaction to the account */
        $updatedAccount = $account->debit(transaction: $transaction);

        /** @Then the account balance should reflect the deducted amount of 50.00 */
        self::assertSame(50.00, $updatedAccount->balance->amount->toFloat());

        /** @And the transaction count should be 2 */
        self::assertSame(2, $updatedAccount->balance->transactions->count());
    }

    public function testInstallmentPurchaseTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::createFrom(holder: Holder::from(document: new CPF(number: '12345678901')));

        /** @And a credit transaction of 100.00 to initialize the balance */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @Then the balance should be 100.00 */
        self::assertSame(100.00, $account->balance->amount->toFloat());

        /** @And a debit transaction of 50.00 with operation type Installment Purchase */
        $transaction = PurchaseWithInstallments::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When applying the debit transaction to the account */
        $updatedAccount = $account->debit(transaction: $transaction);

        /** @Then the account balance should reflect the deducted amount of 50.00 */
        self::assertSame(50.00, $updatedAccount->balance->amount->toFloat());

        /** @And the transaction count should be 2 */
        self::assertSame(2, $updatedAccount->balance->transactions->count());
    }

    public function testWithdrawalTransaction(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::createFrom(holder: Holder::from(document: new CNPJ(number: '33431849000179')));

        /** @And a credit transaction of 100.00 to initialize the balance */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @Then the balance should be 100.00 */
        self::assertSame(100.00, $account->balance->amount->toFloat());

        /** @And a withdrawal transaction of 50.00 */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When applying the withdrawal transaction to the account */
        $updatedAccount = $account->withdraw(transaction: $transaction);

        /** @Then the account balance should reflect the deducted amount of 50.00 */
        self::assertSame(50.00, $updatedAccount->balance->amount->toFloat());

        /** @And the transaction count should be 2 */
        self::assertSame(2, $updatedAccount->balance->transactions->count());
    }

    public function testInsufficientFundsOnWithdrawal(): void
    {
        /** @Given an account is created with any holder */
        $account = Account::createFrom(holder: Holder::from(document: new CNPJ(number: '33431849000179')));

        /** @And a credit transaction of 30.00 to initialize the balance */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 30.00))
        );

        /** @Then the balance of this account should be 30.00 */
        self::assertSame(30.00, $account->balance->amount->toFloat());

        /** @And a withdrawal transaction of 50.00 is attempted, which exceeds the current balance */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @Then an InsufficientFunds exception should be thrown */
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessage('Insufficient funds.');

        /** @When applying the withdrawal transaction to the account */
        $account->withdraw(transaction: $transaction);
    }
}
