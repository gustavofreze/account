<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database;

use Account\Driven\Shared\Database\MySql\MySqlEngine;
use Doctrine\DBAL\Connection;
use Exception;
use PHPUnit\Framework\TestCase;

final class RelationalConnectionTest extends TestCase
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function testTransactionCommitOnSuccess(): void
    {
        /** @Given a database connection is available */
        $connection = $this->createMock(Connection::class);

        /** @And a transaction is started */
        $connection->expects(self::once())->method('beginTransaction');

        /** @And commit should occur if no errors happen */
        $connection
            ->expects(self::once())
            ->method('commit')
            ->willReturnCallback(function () use (&$commitCalled) {
                $commitCalled = true;
            });

        /** @And rollback should not be triggered on success */
        $connection->expects(self::never())->method('rollBack');

        /** @And a MySqlEngine instance is created to handle the transaction */
        $engine = new MySqlEngine(connection: $connection);

        /** @When executing a successful transaction */
        $commitCalled = false;
        $engine->inTransaction(useCase: fn() => true);

        /** @Then the commit should have been called */
        self::assertTrue($commitCalled);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testTransactionRollbackOnFailure(): void
    {
        /** @Given a database connection is available */
        $connection = $this->createMock(Connection::class);

        /** @And a transaction is started */
        $connection->expects(self::once())->method('beginTransaction');

        /** @And commit should not occur if an error happens */
        $connection->expects(self::never())->method('commit');

        /** @And rollback is triggered in case of failure */
        $connection->expects(self::once())->method('rollBack');

        /** @And a MySqlEngine instance handles this transaction */
        $engine = new MySqlEngine(connection: $connection);

        /** @When executing a transaction that encounters an error */
        $this->expectException(DatabaseFailure::class);

        /** @Then a DatabaseFailure exception is raised, and the transaction is rolled back */
        $engine->inTransaction(useCase: fn() => throw new Exception('Simulated transaction failure.'));
    }
}
