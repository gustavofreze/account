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
use Test\Integration\IntegrationTestCase;

final class AccountCreditingHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;
    private AccountCrediting $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(class: AccountCrediting::class);
        $this->accounts = $this->get(class: Accounts::class);
    }

    public function testCreditIncreasesAccountBalance(): void
    {
        /** @Given an account is created for a holder with document number 82132928045 */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account is saved */
        $this->accounts->save(account: $account);

        /** @And a credit command is prepared with a Credit Voucher transaction of 100.00 for this account */
        $command = new CreditAccount(
            id: $account->id,
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @When the credit command is processed by the AccountCrediting handler */
        $this->handler->handle(command: $command);

        /** @Then the account should retain the same ID and holder's document number */
        $actual = $this->accounts->findById(id: $command->id);

        self::assertSame($account->id->toString(), $actual->id->toString());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the account balance should reflect the credited amount of 100.00 */
        $balance = $this->accounts->balanceOf(id: $actual->id);

        self::assertSame(100.00, $balance->amount->toFloat());
    }

    public function testExceptionWhenAccountNotFound(): void
    {
        /** @Given an account is created but not persisted */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '43003399000177'))
        );

        /** @And a credit command is prepared with a Credit Voucher transaction of 100.00 for this non-persisted account */
        $command = new CreditAccount(
            id: $account->id,
            transaction: CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00))
        );

        /** @Then an AccountNotFound exception should be thrown */
        $template = 'Account with ID <%s> not found.';
        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage(sprintf($template, $account->id->toString()));

        /** @When the credit command is processed by the AccountCrediting handler */
        $this->handler->handle(command: $command);
    }
}
