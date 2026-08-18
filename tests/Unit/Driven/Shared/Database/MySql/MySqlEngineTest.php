<?php

declare(strict_types=1);

namespace Test\Unit\Driven\Shared\Database\MySql;

use Account\Driven\Shared\Database\DatabaseConstraint;
use Account\Driven\Shared\Database\DatabaseFailure;
use Account\Driven\Shared\Database\MySql\MySqlEngine;
use Account\Driven\Shared\Database\RelationalConnection;
use Doctrine\DBAL\Driver\PDO\Exception as PdoDriverException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PDOException;
use PHPUnit\Framework\TestCase;
use Test\Unit\Driven\Shared\Database\ConnectionSpy;

final class MySqlEngineTest extends TestCase
{
    public function testExecuteThenReturnsAffectedRows(): void
    {
        /** @Given a connection accepting statements */
        $connection = new ConnectionSpy();

        /** @When a statement is issued */
        $result = new MySqlEngine(connection: $connection)->execute(
            sql: 'UPDATE balances SET amount = :amount WHERE account_id = :account_id',
            bindings: ['amount' => 100.00, 'account_id' => 'account-1']
        );

        /** @Then the affected-row count is exposed and the statement reached the connection */
        self::assertSame(1, $result->affectedRows());
        self::assertSame(
            ['amount' => 100.00, 'account_id' => 'account-1'],
            $connection->statements[0]['params']
        );
    }

    public function testExecuteOnUniqueViolationThenUniqueConstraintFailure(): void
    {
        /** @Given a connection rejecting the statement with a unique violation */
        $connection = new ConnectionSpy();
        $connection->failure = new UniqueConstraintViolationException(
            PdoDriverException::new(new PDOException('duplicate entry')),
            null
        );

        /** @When the statement is issued */
        try {
            new MySqlEngine(connection: $connection)->execute(sql: 'INSERT INTO accounts (id) VALUES (:id)');
            self::fail('A DatabaseFailure was expected.');
        } catch (DatabaseFailure $failure) {
            /** @Then the failure names the unique constraint and carries the driver error */
            self::assertTrue($failure->hasViolated(constraint: DatabaseConstraint::UNIQUE));
            self::assertStringContainsString('duplicate entry', $failure->getMessage());
            self::assertInstanceOf(UniqueConstraintViolationException::class, $failure->getPrevious());
        }
    }

    public function testExecuteOnGenericDatabaseErrorThenConstraintlessFailure(): void
    {
        /** @Given a connection rejecting the statement with a generic database error */
        $connection = new ConnectionSpy();
        $connection->failure = new DriverException(PdoDriverException::new(new PDOException('connection lost')), null);

        /** @When the statement is issued */
        try {
            new MySqlEngine(connection: $connection)->execute(sql: 'INSERT INTO accounts (id) VALUES (:id)');
            self::fail('A DatabaseFailure was expected.');
        } catch (DatabaseFailure $failure) {
            /** @Then the failure carries no constraint */
            self::assertFalse($failure->hasViolated(constraint: DatabaseConstraint::UNIQUE));
            self::assertStringContainsString('connection lost', $failure->getMessage());
        }
    }

    public function testFetchOneForMatchingRowThenRowValue(): void
    {
        /** @Given a connection yielding one row */
        $connection = new ConnectionSpy();
        $connection->fetchedRow = ['id' => 'account-1'];

        /** @When the row is fetched */
        $row = new MySqlEngine(connection: $connection)->fetchOne(
            sql: 'SELECT id FROM accounts WHERE id = :id',
            bindings: ['id' => 'account-1']
        );

        /** @Then the value is present and the query reached the connection */
        self::assertSame(['id' => 'account-1'], $row->getOrNull());
        self::assertSame(['id' => 'account-1'], $connection->statements[0]['params']);
    }

    public function testFetchOneForMissingRowThenEmptyRow(): void
    {
        /** @Given a connection yielding no row */
        $connection = new ConnectionSpy();

        /** @When the row is fetched */
        $row = new MySqlEngine(connection: $connection)->fetchOne(sql: 'SELECT id FROM accounts WHERE id = :id');

        /** @Then the row is empty and mapping keeps it empty */
        self::assertNull($row->getOrNull());
        self::assertNull($row->map(transform: static fn(array $record): string => (string)$record['id'])->getOrNull());
    }

    public function testFetchOneThenMapsTheRow(): void
    {
        /** @Given a connection yielding one row */
        $connection = new ConnectionSpy();
        $connection->fetchedRow = ['id' => 'account-1'];

        /** @When the row is fetched and mapped */
        $row = new MySqlEngine(connection: $connection)->fetchOne(sql: 'SELECT id FROM accounts WHERE id = :id');

        /** @Then the transform applies to the row */
        self::assertSame(
            'account-1',
            $row->map(transform: static fn(array $record): string => (string)$record['id'])->getOrNull()
        );
    }

    public function testFetchOneOnDatabaseErrorThenFailure(): void
    {
        /** @Given a connection rejecting the query */
        $connection = new ConnectionSpy();
        $connection->failure = new DriverException(PdoDriverException::new(new PDOException('connection lost')), null);

        /** @Then the failure surfaces */
        self::expectException(DatabaseFailure::class);

        /** @When the row is fetched */
        new MySqlEngine(connection: $connection)->fetchOne(sql: 'SELECT id FROM accounts WHERE id = :id');
    }

    public function testInTransactionThenWrapsTheWorkInATransaction(): void
    {
        /** @Given an engine over a connection accepting transactions */
        $connection = new ConnectionSpy();
        $engine = new MySqlEngine(connection: $connection);

        /** @When transactional work runs */
        $outcome = $engine->inTransaction(useCase: static fn(RelationalConnection $used): string => 'committed');

        /** @Then the work ran inside a transaction and its result surfaces */
        self::assertSame('committed', $outcome);
        self::assertTrue($connection->transactionalUsed);
    }

    public function testInTransactionOnDatabaseErrorThenFailure(): void
    {
        /** @Given a connection rejecting the transaction */
        $connection = new ConnectionSpy();
        $connection->failure = new DriverException(PdoDriverException::new(new PDOException('deadlock')), null);

        /** @Then the failure surfaces */
        self::expectException(DatabaseFailure::class);

        /** @When transactional work runs */
        new MySqlEngine(connection: $connection)->inTransaction(
            useCase: static fn(RelationalConnection $used): string => 'unreachable'
        );
    }
}
