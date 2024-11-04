<?php

declare(strict_types=1);

namespace Test\Integration\Application\Domain\Handlers;

use Account\Application\Domain\Commands\CreditAccount;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Ports\Inbound\AccountCrediting;
use Account\Application\Domain\Ports\Outbound\Accounts;
use Account\Driven\Shared\Database\RelationalConnection;
use Test\Integration\Application\Repository;
use Test\Integration\IntegrationTestCase;

final class AccountCreditingHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;

    private Repository $repository;

    private AccountCrediting $handler;

    protected function setUp(): void
    {
        $this->handler = $this->get(class: AccountCrediting::class);
        $this->accounts = $this->get(class: Accounts::class);
        $this->repository = new Repository(connection: $this->get(class: RelationalConnection::class));
    }

    public function testCreditIncreasesAccountBalance(): void
    {
        /** @Given a new account is created for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account is saved */
        $this->accounts->save(account: $account);

        /** @And a credit command is created with a Credit Voucher transaction of 100.00 */
        $command = new CreditAccount(
            id: $account->id,
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @When the handler processes the credit command */
        $this->handler->handle(command: $command);

        /** @Then the account ID and holder should remain unchanged */
        $actual = $this->accounts->findById(id: $command->id);

        self::assertSame($account->id->toString(), $actual->id->toString());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the account balance should be updated to 100.00 */
        $balance = $this->repository->balanceOf(id: $actual->id);

        self::assertSame(100.00, $balance->amount->toFloat());
    }

    public function testExceptionWhenAccountNotFound(): void
    {
        /** @Given a new account is created but not saved */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '43003399000177'))
        );

        /** @And a credit command is created with a Credit Voucher transaction of 100.00 for this non-saved account */
        $command = new CreditAccount(
            id: $account->id,
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @Then an AccountNotFound exception is expected */
        $template = 'Account with ID <%s> not found.';
        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage(sprintf($template, $account->id->toString()));

        /** @When the handler processes the credit command */
        $this->handler->handle(command: $command);
    }
}
