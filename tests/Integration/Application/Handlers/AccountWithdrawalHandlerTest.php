<?php

declare(strict_types=1);

namespace Test\Integration\Application\Handlers;

use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\AccountWithInsufficientFunds;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountWithdrawal;
use Account\Application\Ports\Outbound\Accounts;
use Test\Integration\IntegrationTestCase;

final class AccountWithdrawalHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;

    private AccountWithdrawal $handler;

    protected function setUp(): void
    {
        $this->handler = $this->get(class: AccountWithdrawal::class);
        $this->accounts = $this->get(class: Accounts::class);
    }

    public function testSuccessfulWithdrawal(): void
    {
        /** @Given an account is created for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account is saved */
        $this->accounts->save(account: $account);

        /** @And a credit transaction of 100.00 is applied to the account */
        $credit = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));
        $account->credit(transaction: $credit);

        /** @And the transaction is recorded in the account history */
        $this->accounts->credit(account: $account, transaction: $credit);

        /** @And a withdrawal command is created for 50.00 */
        $command = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @When the handler processes the withdrawal command */
        $this->handler->handle(command: $command);

        /** @Then the account ID and holder should remain unchanged */
        $actual = $this->accounts->findById(id: $command->id);

        self::assertNotNull($actual);
        self::assertSame($account->id->identityValue(), $actual->id->identityValue());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the account balance should reflect a decrease of 50.00, resulting in a final balance of 50.00 */
        $balance = $this->fixtures()->balanceOf(accountId: $actual->id->identityValue());

        self::assertSame(50.00, $balance);

        /** @And every fact should reach the outbox in the order the account recorded it */
        $eventTypes = $this->fixtures()->outboxEventTypesOf(accountId: $actual->id->identityValue());

        self::assertSame(['AccountOpened', 'AccountCredited', 'AccountWithdrawn'], $eventTypes);
    }

    public function testSecondWithdrawalIsRefusedWhenTheBalanceNoLongerCoversIt(): void
    {
        /** @Given an account is created with a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account has an initial balance */
        $this->accounts->save(account: $account);

        /** @And a credit transaction of 100.00 is applied */
        $credit = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));
        $account->credit(transaction: $credit);

        /** @And the transaction is recorded */
        $this->accounts->credit(account: $account, transaction: $credit);

        /** @And a first withdrawal command for 60.00 is created */
        $firstCommand = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -60.00))
        );

        /** @When the first withdrawal command is processed */
        $this->handler->handle(command: $firstCommand);

        /** @Then the account balance should reflect the first withdrawal */
        $balanceAfterFirstWithdrawal = $this->fixtures()->balanceOf(accountId: $account->id->identityValue());

        self::assertSame(40.00, $balanceAfterFirstWithdrawal);

        /** @And a second withdrawal command for 50.00 is created */
        $secondCommand = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @Then an AccountWithInsufficientFunds exception is expected when processing the second withdrawal */
        $this->expectException(AccountWithInsufficientFunds::class);

        /** @When the second withdrawal command is processed */
        $this->handler->handle(command: $secondCommand);
    }

    public function testExceptionWhenAccountNotFound(): void
    {
        /** @Given a new account is created but not saved */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '43003399000177'))
        );

        /** @And a withdrawal command is created for 50.00 */
        $command = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @Then an AccountNotFound exception is expected */
        $this->expectException(AccountNotFound::class);

        /** @When the handler processes the withdrawal command */
        $this->handler->handle(command: $command);
    }

    public function testExceptionWhenInsufficientFunds(): void
    {
        /** @Given an account is created for a holder with no initial balance */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account is saved */
        $this->accounts->save(account: $account);

        /** @And a withdrawal command is created for 50.00, exceeding the available balance */
        $command = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @Then an AccountWithInsufficientFunds exception is expected */
        $this->expectException(AccountWithInsufficientFunds::class);

        /** @When the handler processes the withdrawal command */
        $this->handler->handle(command: $command);
    }
}
