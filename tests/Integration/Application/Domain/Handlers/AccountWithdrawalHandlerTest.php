<?php

declare(strict_types=1);

namespace Test\Integration\Application\Domain\Handlers;

use Account\Application\Domain\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Domain\Exceptions\InsufficientFunds;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Domain\Ports\Inbound\AccountWithdrawal;
use Account\Application\Domain\Ports\Outbound\Accounts;
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
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @And the transaction is recorded in the account history */
        $this->accounts->applyTransactionTo(account: $account);

        /** @And a withdrawal command is created for 50.00 */
        $command = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @When the handler processes the withdrawal command */
        $this->handler->handle(command: $command);

        /** @Then the account ID and holder should remain unchanged */
        $actual = $this->accounts->findById(id: $command->id);

        self::assertSame($account->id->toString(), $actual->id->toString());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the account balance should reflect a decrease of 50.00, resulting in a final balance of 50.00 */
        $balance = $this->accounts->balanceOf(id: $actual->id);

        self::assertSame(50.00, $balance->amount->toFloat());
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
        $template = 'Account with ID <%s> not found.';
        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage(sprintf($template, $account->id->toString()));

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

        /** @Then an InsufficientFunds exception is expected */
        $template = 'Account with ID <%s> has insufficient funds.';
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessage(sprintf($template, $account->id->toString()));

        /** @When the handler processes the withdrawal command */
        $this->handler->handle(command: $command);
    }
}