<?php

declare(strict_types=1);

namespace Test\Integration\Application\Handlers;

use Account\Application\Commands\RequestWithdrawal;
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
use Account\Application\Ports\Inbound\AccountWithdrawal;
use Account\Application\Ports\Outbound\Accounts;
use Account\Driven\Shared\Database\RelationalConnection;
use Test\Integration\Application\Repository;
use Test\Integration\IntegrationTestCase;

final class AccountWithdrawalHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;

    private Repository $repository;

    private AccountWithdrawal $handler;

    protected function setUp(): void
    {
        $this->handler = $this->get(class: AccountWithdrawal::class);
        $this->accounts = $this->get(class: Accounts::class);
        $this->repository = new Repository(connection: $this->get(class: RelationalConnection::class));
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
        $this->accounts->applyCreditTransactionTo(account: $account);

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
        $balance = $this->repository->balanceOf(id: $actual->id);

        self::assertSame(50.00, $balance->amount->toFloat());
    }

    public function testConcurrentWithdrawals(): void
    {
        /** @Given an account is created with a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account has an initial balance */
        $this->accounts->save(account: $account);

        /** @And a credit transaction of 100.00 is applied */
        $account = $account->credit(
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @And the transaction is recorded */
        $this->accounts->applyCreditTransactionTo(account: $account);

        /** @And a first withdrawal command for 60.00 is created */
        $firstCommand = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -60.00))
        );

        /** @When the first withdrawal command is processed */
        $this->handler->handle(command: $firstCommand);

        /** @Then the account balance should reflect the first withdrawal */
        $balanceAfterFirstWithdrawal = $this->repository->balanceOf(id: $account->id);

        self::assertSame(40.00, $balanceAfterFirstWithdrawal->amount->toFloat());

        /** @And a second withdrawal command for 50.00 is created */
        $secondCommand = new RequestWithdrawal(
            id: $account->id,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -50.00))
        );

        /** @Then an InsufficientFunds exception is expected when processing the second withdrawal */
        $this->expectException(InsufficientFunds::class);

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
