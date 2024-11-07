<?php

declare(strict_types=1);

namespace Test\Integration\Application\Handlers;

use Account\Application\Commands\OpenAccount;
use Account\Application\Domain\Exceptions\AccountAlreadyExists;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Ports\Inbound\AccountOpening;
use Account\Application\Ports\Outbound\Accounts;
use Test\Integration\IntegrationTestCase;

final class AccountOpeningHandlerTest extends IntegrationTestCase
{
    private Accounts $accounts;

    private AccountOpening $handler;

    protected function setUp(): void
    {
        $this->handler = $this->get(class: AccountOpening::class);
        $this->accounts = $this->get(class: Accounts::class);
    }

    public function testSuccessfulAccountOpening(): void
    {
        /** @Given I have a valid command for opening an account */
        $command = new OpenAccount(
            id: AccountId::generate(),
            holder: Holder::from(
                document: SimpleIdentity::from(number: '12345678901')
            )
        );

        /** @When the handler processes the account opening command */
        $this->handler->handle(command: $command);

        /** @Then a new account should be saved for this holder */
        $account = $this->accounts->findByHolder(holder: $command->holder);

        self::assertSame('12345678901', $account->holder->document->getNumber());
    }

    public function testExceptionWhenAccountAlreadyExists(): void
    {
        /** @Given a holder who already has an account */
        $existingAccount = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );
        $this->accounts->save(account: $existingAccount);

        /** @And an attempt to open another account for the same holder */
        $command = new OpenAccount(id: AccountId::generate(), holder: $existingAccount->holder);

        /** @Then an AccountAlreadyExists exception is expected */
        $template = 'An account with document number <%s> already exists.';
        $this->expectException(AccountAlreadyExists::class);
        $this->expectExceptionMessage(sprintf($template, $command->holder->document->getNumber()));

        /** @When the handler processes the account opening command */
        $this->handler->handle(command: $command);
    }
}
