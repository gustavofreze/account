<?php

declare(strict_types=1);

namespace Test\Unit\Application\Domain\Models\Account;

use Account\Application\Domain\Events\AccountCredited;
use Account\Application\Domain\Events\AccountDebited;
use Account\Application\Domain\Events\AccountOpened;
use Account\Application\Domain\Events\AccountWithdrawn;
use Account\Application\Domain\Exceptions\AccountWithInsufficientFunds;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testOpeningRecordsTheFact(): void
    {
        /** @Given a holder */
        $holder = Holder::from(document: SimpleIdentity::from(number: '33431849000179'));

        /** @When an account is opened for that holder */
        $account = Account::openFrom(id: AccountId::generate(), holder: $holder);

        /** @Then the account recorded that it was opened for that holder */
        $event = $account->peekEvents()->getBy(index: 0)->event;

        self::assertInstanceOf(AccountOpened::class, $event);
        self::assertSame($holder, $event->holder);
    }

    public function testCreditRecordsTheFact(): void
    {
        /** @Given an opened account */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a credit transaction of 100.00 */
        $transaction = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));

        /** @When the credit transaction is applied */
        $account->credit(transaction: $transaction);

        /** @Then the account recorded the credited amount and the transaction that carried it */
        $event = $account->peekEvents()->getBy(index: 1)->event;

        self::assertInstanceOf(AccountCredited::class, $event);
        self::assertSame(100.00, $event->amount->toFloat());
        self::assertSame($transaction->getId(), $event->transactionId);
    }

    public function testDebitRecordsTheFact(): void
    {
        /** @Given an opened account */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a debit transaction of 50.00 against a balance of 100.00 */
        $balance = Balance::from(value: 100.00);
        $transaction = NormalPurchase::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When the debit transaction is applied */
        $account->debit(balance: $balance, transaction: $transaction);

        /** @Then the account recorded the debited amount and the transaction that carried it */
        $event = $account->peekEvents()->getBy(index: 1)->event;

        self::assertInstanceOf(AccountDebited::class, $event);
        self::assertSame(-50.00, $event->amount->toFloat());
        self::assertSame($transaction->getId(), $event->transactionId);
    }

    public function testWithdrawalRecordsTheFact(): void
    {
        /** @Given an opened account */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );

        /** @And a withdrawal transaction of 50.00 against a balance of 100.00 */
        $balance = Balance::from(value: 100.00);
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @When the withdrawal transaction is applied */
        $account->withdraw(balance: $balance, transaction: $transaction);

        /** @Then the account recorded the withdrawn amount and the transaction that carried it */
        $event = $account->peekEvents()->getBy(index: 1)->event;

        self::assertInstanceOf(AccountWithdrawn::class, $event);
        self::assertSame(-50.00, $event->amount->toFloat());
        self::assertSame($transaction->getId(), $event->transactionId);
    }

    public function testDrainingTheRecordedFactsEmptiesTheBuffer(): void
    {
        /** @Given an opened account that recorded a credit */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );
        $account->credit(transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00)));

        /** @When the recorded facts are drained */
        $drained = $account->pullEvents();

        /** @Then the drained facts are returned and none is left behind */
        self::assertSame(2, $drained->count());
        self::assertSame(0, $account->peekEvents()->count());
    }

    public function testExceptionWhenDebitingWithoutSufficientFunds(): void
    {
        /** @Given an opened account with no balance */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );
        $balance = Balance::from(value: 0.00);

        /** @And a debit transaction of 50.00, which exceeds the balance */
        $transaction = NormalPurchase::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @Then an AccountWithInsufficientFunds should be thrown */
        $this->expectException(AccountWithInsufficientFunds::class);

        /** @When the debit transaction is applied */
        $account->debit(balance: $balance, transaction: $transaction);
    }

    public function testExceptionWhenWithdrawingWithoutSufficientFunds(): void
    {
        /** @Given an opened account with no balance */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '33431849000179'))
        );
        $balance = Balance::from(value: 0.00);

        /** @And a withdrawal transaction of 50.00, which exceeds the balance */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00));

        /** @Then an AccountWithInsufficientFunds should be thrown */
        $this->expectException(AccountWithInsufficientFunds::class);

        /** @When the withdrawal transaction is applied */
        $account->withdraw(balance: $balance, transaction: $transaction);
    }
}
