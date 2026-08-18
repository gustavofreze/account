<?php

declare(strict_types=1);

namespace Test\Unit\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Application\Exceptions\AccountAlreadyExists;
use Account\Driven\Account\Repository\AccountRepository;
use Account\Driven\Account\Repository\Balances;
use Account\Driven\Account\Repository\Records\AccountRecordReader;
use Account\Driven\Account\Repository\Records\AccountRecordWriter;
use Account\Driven\Shared\Database\DatabaseConstraint;
use Account\Driven\Shared\Database\DatabaseFailure;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Test\Unit\Driven\Account\Outbox\OutboxRepositorySpy;

/**
 * Justification for a unit test on a repository (php-testing-unit § Default stance). The constraint-translation
 * branches the writer decides are chosen by which DatabaseFailure the connection raises, and the accounts table
 * carries a single constraint, so the non-unique branch is unreachable against the real schema that the integration
 * suite exercises. The connection and the outbox are system boundaries, where php-testing § Doubles admits a Spy, and
 * the reader, the writer, and the balances are real objects built over that same spy.
 */
final class AccountRepositoryTest extends TestCase
{
    public function testSaveIssuesTheInsertAndPublishesTheRecordedFact(): void
    {
        /** @Given a connection that accepts every statement and an outbox that accepts every record */
        $outbox = new OutboxRepositorySpy();
        $connection = new RelationalConnectionSpy();

        /** @And an account opened for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );

        /** @When the account is saved */
        new AccountRepository(
            outbox: $outbox,
            reader: new AccountRecordReader(),
            writer: new AccountRecordWriter(connection: $connection),
            balances: new Balances(connection: $connection),
            connection: $connection
        )->save(account: $account);

        /** @Then the insert reached the database and the opening reached the outbox */
        self::assertSame(1, $connection->statementsIssued());
        self::assertSame(1, $outbox->recordsPushed());
    }

    public function testUniqueViolationBecomesAccountAlreadyExists(): void
    {
        /** @Given a connection that rejects the insert with a unique constraint violation */
        $failure = DatabaseFailure::dueTo(
            error: new RuntimeException('Duplicate entry'),
            constraint: DatabaseConstraint::UNIQUE
        );
        $connection = new RelationalConnectionSpy(failureOnExecute: $failure);

        /** @And an account opened for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );

        /** @Then an AccountAlreadyExists is expected */
        $this->expectException(AccountAlreadyExists::class);

        /** @When the account is saved */
        new AccountRepository(
            outbox: new OutboxRepositorySpy(),
            reader: new AccountRecordReader(),
            writer: new AccountRecordWriter(connection: $connection),
            balances: new Balances(connection: $connection),
            connection: $connection
        )->save(account: $account);
    }

    public function testDatabaseFailureWithoutConstraintIsRethrown(): void
    {
        /** @Given a connection that rejects the insert with a failure carrying no constraint */
        $failure = DatabaseFailure::from(error: new RuntimeException('Connection lost'));
        $connection = new RelationalConnectionSpy(failureOnExecute: $failure);

        /** @And an account opened for a holder */
        $account = Account::openFrom(
            id: AccountId::generate(),
            holder: Holder::from(document: SimpleIdentity::from(number: '12345678901'))
        );

        /** @Then the original DatabaseFailure is expected */
        $this->expectException(DatabaseFailure::class);
        $this->expectExceptionMessage('Connection lost');

        /** @When the account is saved */
        new AccountRepository(
            outbox: new OutboxRepositorySpy(),
            reader: new AccountRecordReader(),
            writer: new AccountRecordWriter(connection: $connection),
            balances: new Balances(connection: $connection),
            connection: $connection
        )->save(account: $account);
    }

    public function testFindByIdReturnsNullWhenNoRowMatches(): void
    {
        /** @Given a connection that yields no row */
        $connection = new RelationalConnectionSpy();

        /** @When an account is looked up */
        $actual = new AccountRepository(
            outbox: new OutboxRepositorySpy(),
            reader: new AccountRecordReader(),
            writer: new AccountRecordWriter(connection: $connection),
            balances: new Balances(connection: $connection),
            connection: $connection
        )->findById(id: AccountId::generate());

        /** @Then no account is found */
        self::assertNull($actual);
    }
}
