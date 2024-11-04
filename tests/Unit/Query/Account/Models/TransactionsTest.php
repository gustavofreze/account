<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class TransactionsTest extends TestCase
{
    public function testToArrayWithSingleTransaction(): void
    {
        /** @Given a transaction is created */
        $transaction = Transaction::from(data: [
            'id'              => Uuid::uuid4()->toString(),
            'amount'          => '300.00',
            'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'accountId'       => Uuid::uuid4()->toString(),
            'operationTypeId' => 4
        ]);

        /** @And the transaction is added to a collection of transactions */
        $transactions = Transactions::createFrom(elements: [$transaction]);

        /** @When the toArray method is called */
        $result = $transactions->all();

        /** @Then the result should contain exactly one transaction */
        self::assertCount(1, $result);
        self::assertEquals($transaction->toArray(), $result[0]);
    }

    public function testToArrayWithNoTransactions(): void
    {
        /** @Given an empty collection of transactions */
        $transactions = Transactions::createFromEmpty();

        /** @When the toArray method is called */
        $result = $transactions->all();

        /** @Then the result should be an empty array */
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testToArrayWithMultipleTransactions(): void
    {
        /** @Given multiple transactions are created */
        $transaction1 = Transaction::from(data: [
            'id'              => Uuid::uuid4()->toString(),
            'amount'          => '300.00',
            'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'accountId'       => Uuid::uuid4()->toString(),
            'operationTypeId' => 4
        ]);

        $transaction2 = Transaction::from(data: [
            'id'              => Uuid::uuid4()->toString(),
            'amount'          => '-100.00',
            'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'accountId'       => Uuid::uuid4()->toString(),
            'operationTypeId' => 3
        ]);

        /** @And the transactions are added to a collection of transactions */
        $transactions = Transactions::createFrom(elements: [$transaction1, $transaction2]);

        /** @When the toArray method is called */
        $result = $transactions->all();

        /** @Then the result should contain both transactions */
        self::assertCount(2, $result);
        self::assertEquals($transaction1->toArray(), $result[0]);
        self::assertEquals($transaction2->toArray(), $result[1]);
    }
}