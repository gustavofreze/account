<?php

declare(strict_types=1);

namespace Test\Integration\Application\Handlers;

use Account\Application\Commands\OpenAccount;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Exceptions\AccountAlreadyExists;
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
        $account = $this->accounts->findById(id: $command->id);

        self::assertNotNull($account);
        self::assertSame('12345678901', $account->holder->document->getNumber());

        /** @And the opening should reach the outbox carrying the holder document number */
        $payload = $this->fixtures()->outboxPayloadOf(
            accountId: $command->id->identityValue(),
            eventType: 'AccountOpened'
        );

        self::assertSame(['holder_document_number' => '12345678901'], $payload);
    }

    public function testExceptionWhenAccountAlreadyExists(): void
    {
        /** @Given a holder who already has an account */
        $holder = Holder::from(document: SimpleIdentity::from(number: '12345678901'));

        /** @And the account of that holder is already opened */
        $this->handler->handle(command: new OpenAccount(id: AccountId::generate(), holder: $holder));

        /** @Then an AccountAlreadyExists exception is expected */
        $this->expectException(AccountAlreadyExists::class);

        /** @When the handler processes another account opening command for the same holder */
        $this->handler->handle(command: new OpenAccount(id: AccountId::generate(), holder: $holder));
    }
}
