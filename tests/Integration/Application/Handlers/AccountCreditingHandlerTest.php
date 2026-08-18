<?php

declare(strict_types=1);

namespace Test\Integration\Application\Handlers;

use Account\Application\Commands\CreditAccount;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountCrediting;
use Account\Application\Ports\Outbound\Accounts;
use Test\Integration\IntegrationTestCase;

final class AccountCreditingHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;

    private AccountCrediting $handler;

    protected function setUp(): void
    {
        $this->handler = $this->get(class: AccountCrediting::class);
        $this->accounts = $this->get(class: Accounts::class);
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

        self::assertNotNull($actual);
        self::assertSame($account->id->identityValue(), $actual->id->identityValue());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the account balance should be updated to 100.00 */
        $balance = $this->fixtures()->balanceOf(accountId: $actual->id->identityValue());

        self::assertSame(100.00, $balance);

        /** @And the opening and the credit should reach the outbox, in that order */
        $eventTypes = $this->fixtures()->outboxEventTypesOf(accountId: $actual->id->identityValue());

        self::assertSame(['AccountOpened', 'AccountCredited'], $eventTypes);

        /** @And the credit payload should carry the amount and the transaction, in the transport shape */
        $payload = $this->fixtures()->outboxPayloadOf(
            accountId: $actual->id->identityValue(),
            eventType: 'AccountCredited'
        );

        self::assertSame(
            ['amount' => 100, 'transaction_id' => $command->transaction->getId()->toString()],
            $payload
        );
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
        $this->expectException(AccountNotFound::class);

        /** @When the handler processes the credit command */
        $this->handler->handle(command: $command);
    }
}
