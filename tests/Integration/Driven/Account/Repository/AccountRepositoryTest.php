<?php

declare(strict_types=1);

namespace Test\Integration\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveAmount;
use Account\Application\Domain\Models\Transaction\Operations\CreditVoucher;
use Account\Application\Domain\Models\Transaction\Operations\NormalPurchase;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Ports\Outbound\Accounts;
use Test\Integration\IntegrationTestCase;

/**
 * Round-trip test required by php-testing-integration § Round-trip test. The account is reconstituted through
 * reflection, where a typo in a state key is dropped with no exception and no warning, so every reconstituted
 * property is asserted against the value that was persisted.
 */
final class AccountRepositoryTest extends IntegrationTestCase
{
    private Accounts $accounts;

    protected function setUp(): void
    {
        $this->accounts = $this->get(class: Accounts::class);
    }

    public function testAccountReloadsWithEveryPropertyItWasPersistedWith(): void
    {
        /** @Given an account opened for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        /** @And the account is saved and then credited, so it has recorded more than one fact */
        $this->accounts->save(account: $account);

        $credit = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));
        $account->credit(transaction: $credit);

        $this->accounts->credit(account: $account, transaction: $credit);

        /** @When the account is reloaded */
        $actual = $this->accounts->findById(id: $account->id);

        /** @Then the identifier and the holder carry the persisted values */
        self::assertNotNull($actual);
        self::assertSame($account->id->identityValue(), $actual->id->identityValue());
        self::assertSame($account->holder->document->getNumber(), $actual->holder->document->getNumber());

        /** @And the restored version matches the last one the outbox recorded, so the sequence continues */
        $lastVersion = $this->fixtures()->lastOutboxAggregateVersionOf(accountId: $account->id->identityValue());

        self::assertSame(2, $lastVersion);
        self::assertSame($lastVersion, $actual->aggregateVersion()->value);
    }

    public function testSuccessiveMovementsOnTheSameInstanceReachTheOutboxExactlyOnce(): void
    {
        /** @Given an opened account credited with 100.00 */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '82132928045'))
        );

        $this->accounts->save(account: $account);

        $credit = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 100.00));
        $account->credit(transaction: $credit);

        $this->accounts->credit(account: $account, transaction: $credit);

        /** @And a debit and a withdrawal already applied through that same instance */
        $this->accounts->debit(
            account: $account,
            transaction: NormalPurchase::createFrom(amount: NegativeAmount::from(value: -20.00))
        );
        $this->accounts->withdraw(
            account: $account,
            transaction: Withdrawal::createFrom(amount: NegativeAmount::from(value: -30.00))
        );

        /** @When a further credit is applied through that same instance */
        $furtherCredit = CreditVoucher::createFrom(amount: PositiveAmount::from(value: 15.00));
        $account->credit(transaction: $furtherCredit);

        $this->accounts->credit(account: $account, transaction: $furtherCredit);

        /** @Then each fact reached the outbox once, in the order the account recorded it */
        $eventTypes = $this->fixtures()->outboxEventTypesOf(accountId: $account->id->identityValue());

        self::assertSame(
            ['AccountOpened', 'AccountCredited', 'AccountDebited', 'AccountWithdrawn', 'AccountCredited'],
            $eventTypes
        );
    }
}
