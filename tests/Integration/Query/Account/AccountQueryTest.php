<?php

declare(strict_types=1);

namespace Test\Integration\Query\Account;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Database\TransactionFilters;
use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Transaction;
use Account\Query\Account\Models\Transactions;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Test\Integration\IntegrationTestCase;
use Test\Integration\Query\Repository;
use TinyBlocks\Collection\Collection;

final class AccountQueryTest extends IntegrationTestCase
{
    private AccountQuery $query;

    private Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(AccountQuery::class);
        $this->repository = new Repository(connection: $this->get(Connection::class));
    }

    public function testFindById(): void
    {
        /** @Given an account with specific details */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @When I retrieve the account by its ID */
        $actual = $this->query->findById(accountId: $account->id);

        /** @Then the retrieved account details should match the persisted account */
        self::assertSame($account->id, $actual->id);
        self::assertSame($account->holder->document, $actual->holder->document);
    }

    public function testBalanceOf(): void
    {
        /** @Given an account */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @And there are transactions assigned to this account */
        $this->repository->saveTransactions(transactions: Collection::createFrom(elements: [
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '300.00',
                'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 4
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 2
            ])
        ]));

        /** @When I retrieve the account balance */
        $actual = $this->query->balanceOf(accountId: $account->id);

        /** @Then the retrieved balance should match the expected balance */
        self::assertSame(200.00, $actual->amount);
    }

    public function testTransactionsOf(): void
    {
        /** @Given an account */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @And there are transactions assigned to this account */
        $createdAt = new DateTimeImmutable();
        $transactions = Transactions::createFrom(elements: [
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 2
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->modify('-2 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 3
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '300.00',
                'createdAt'       => $createdAt->modify('-3 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 4
            ])
        ]);
        $this->repository->saveTransactions(transactions: $transactions);

        /** @When I retrieve the account balance */
        $actual = $this->query->transactionsOf(accountId: $account->id, filters: TransactionFilters::from(data: []));

        /** @Then the retrieved balance should match the expected balance */
        self::assertSame($transactions->toArray(), $actual->toArray());
    }

    public function testSingleTransaction(): void
    {
        /** @Given an account */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @And there is a single transaction assigned to this account */
        $createdAt = new DateTimeImmutable();
        $transaction = Transaction::from(data: [
            'id'              => Uuid::uuid4()->toString(),
            'amount'          => '100.00',
            'createdAt'       => $createdAt->format(DateTimeInterface::ATOM),
            'accountId'       => $account->id,
            'operationTypeId' => 1
        ]);
        $this->repository->saveTransactions(transactions: Transactions::createFrom(elements: [$transaction]));

        /** @When I retrieve the account transactions */
        $actual = $this->query->transactionsOf(accountId: $account->id, filters: TransactionFilters::from(data: []));

        /** @Then the retrieved transactions should match the expected transaction */
        self::assertSame([$transaction->toArray()], $actual->toArray());
    }

    public function testTransactionsOfWithFilters(): void
    {
        /** @Given an account */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @And there are transactions assigned to this account */
        $createdAt = new DateTimeImmutable();
        $transactions = Transactions::createFrom(elements: [
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 2
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->modify('-2 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 3
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '300.00',
                'createdAt'       => $createdAt->modify('-3 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 4
            ])
        ]);
        $this->repository->saveTransactions(transactions: $transactions);

        /** @When I retrieve the account transactions with filters */
        $actual = $this->query->transactionsOf(
            accountId: $account->id,
            filters: TransactionFilters::from(data: ['operationTypeIds' => ['1', 4]])
        );

        /** @Then the retrieved transactions should match the expected transactions */
        $actual = $actual->toArray()[0];

        self::assertSame($transactions->last()->toArray(), $actual);
        self::assertArrayHasKey('id', $actual);
        self::assertArrayHasKey('amount', $actual);
        self::assertArrayHasKey('created_at', $actual);
        self::assertArrayHasKey('account_id', $actual);
        self::assertArrayHasKey('operation_type_id', $actual);
    }

    public function testTransactionsOfReturnEmpty(): void
    {
        /** @Given an account */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->saveAccount(account: $account);

        /** @And there are transactions assigned to this account */
        $createdAt = new DateTimeImmutable();
        $transactions = Transactions::createFrom(elements: [
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 2
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '-100.00',
                'createdAt'       => $createdAt->modify('-2 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 3
            ]),
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '300.00',
                'createdAt'       => $createdAt->modify('-3 days')->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 4
            ])
        ]);
        $this->repository->saveTransactions(transactions: $transactions);

        /** @When I retrieve the account transactions with filters that do not match any */
        $actual = $this->query->transactionsOf(
            accountId: $account->id,
            filters: TransactionFilters::from(data: ['operationTypeIds' => [1]])
        );

        /** @Then the retrieved transactions should be empty */
        self::assertTrue($actual->isEmpty());
    }
}
