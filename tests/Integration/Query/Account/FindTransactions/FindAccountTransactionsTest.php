<?php

declare(strict_types=1);

namespace Test\Integration\Query\Account\FindTransactions;

use Account\Query\Account\FindTransactions\Database\AccountTransactionsFindingAdapter;
use Account\Query\Account\FindTransactions\Http\FindAccountTransactions;
use Account\Query\Account\Shared\Database\RegisteredAccounts;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Doctrine\DBAL\Connection;
use Test\Integration\AccountRequests;
use Test\Integration\IntegrationTestCase;
use TinyBlocks\Http\Code;

final class FindAccountTransactionsTest extends IntegrationTestCase
{
    private const string NEWEST_AT = '2026-08-11 10:00:00.000000';
    private const string OLDEST_AT = '2026-08-09 10:00:00.000000';
    private const string MIDDLE_AT = '2026-08-10 10:00:00.000000';
    private const string ACCOUNT_ID = '0198bfc0-1111-7000-8000-aaaabbbbcccc';
    private const string OTHER_ACCOUNT_ID = '0198bfc0-1111-7000-8000-ddddeeeeffff';
    private const string HOLDER_DOCUMENT = '56005551000100';
    private const string TRANSACTIONS_PATH = '/accounts/%s/transactions?%s';
    private const string UNKNOWN_ACCOUNT_ID = '0198bfc0-9999-7000-8000-ffffffffffff';
    private const string OTHER_HOLDER_DOCUMENT = '56005551000199';

    private FindAccountTransactions $endpoint;

    protected function setUp(): void
    {
        $connection = $this->get(class: Connection::class);

        $this->endpoint = new FindAccountTransactions(
            transactions: new AccountTransactionsFindingAdapter(
                accounts: new RegisteredAccounts(connection: $connection),
                connection: $connection
            )
        );
    }

    public function testFindAllThenTheTransactionOfTheAccountReturns(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And a purchase recorded against it */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: -50.25,
            accountId: self::ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 1
        );

        /** @And a request carrying the account identifier */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, '');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the transaction returns with every field of the view */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame(Code::OK->value, $response->getStatusCode());
        self::assertCount(1, $body['data']);
        self::assertSame('0198bfc0-2222-7000-8000-000000000001', $body['data'][0]['id']);
        self::assertSame(-50.25, $body['data'][0]['amount']);
        self::assertSame(self::ACCOUNT_ID, $body['data'][0]['account_id']);
        self::assertSame('2026-08-11T10:00:00.000000+00:00', $body['data'][0]['created_at']);
        self::assertSame(1, $body['data'][0]['operation_type_id']);
        self::assertFalse($body['meta']['has_next']);
    }

    public function testFindAllThenTheMostRecentTransactionComesFirst(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /**
         * @And three transactions recorded on different days, whose identifiers place the oldest first in the
         * clustered index, so a query without an explicit ordering returns them in the wrong order
         */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: -100.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::OLDEST_AT,
            operationTypeId: 1
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000002',
            amount: -200.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::MIDDLE_AT,
            operationTypeId: 2
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000003',
            amount: 300.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 4
        );

        /** @And a request carrying the account identifier */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, '');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the newest transaction comes first and the oldest comes last */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame([
            '0198bfc0-2222-7000-8000-000000000003',
            '0198bfc0-2222-7000-8000-000000000002',
            '0198bfc0-2222-7000-8000-000000000001'
        ], array_column($body['data'], 'id'));
    }

    public function testFindAllSortedByCreationAscendingThenTheOldestComesFirst(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And two transactions recorded on different days */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000003',
            amount: 300.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 4
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: -100.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::OLDEST_AT,
            operationTypeId: 1
        );

        /** @And a request asking for the creation date in ascending order, against the default of most recent first */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, 'sort=created_at,id');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the oldest transaction comes first */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame([
            '0198bfc0-2222-7000-8000-000000000001',
            '0198bfc0-2222-7000-8000-000000000003'
        ], array_column($body['data'], 'id'));
    }

    public function testFindAllThenOnlyTheTransactionsOfTheAccountReturn(): void
    {
        /** @Given two registered accounts */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);
        $this->fixtures()->insertAccount(id: self::OTHER_ACCOUNT_ID, document: self::OTHER_HOLDER_DOCUMENT);

        /** @And a transaction recorded against each of them */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: -100.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 1
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-00000000000a',
            amount: -900.00,
            accountId: self::OTHER_ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 1
        );

        /** @And a request carrying the identifier of the first account */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, '');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then only the transaction of the first account returns */
        $body = json_decode((string)$response->getBody(), true);

        self::assertCount(1, $body['data']);
        self::assertSame('0198bfc0-2222-7000-8000-000000000001', $body['data'][0]['id']);
    }

    public function testFindAllFilteredByOperationTypeThenOnlyTheMatchingTransactionsReturn(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And transactions of two operation types recorded against it */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: -100.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::OLDEST_AT,
            operationTypeId: 1
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000002',
            amount: 300.00,
            accountId: self::ACCOUNT_ID,
            createdAt: self::NEWEST_AT,
            operationTypeId: 4
        );

        /** @And a request filtering by the operation type of the credit */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, 'filter=operation_type_id==4');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then only the credit returns */
        $body = json_decode((string)$response->getBody(), true);

        self::assertCount(1, $body['data']);
        self::assertSame(4, $body['data'][0]['operation_type_id']);
        self::assertSame('0198bfc0-2222-7000-8000-000000000002', $body['data'][0]['id']);
    }

    public function testFindAllOfAnAccountWithoutTransactionsThenNoTransactionReturns(): void
    {
        /** @Given a registered account that never moved */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And a request carrying the account identifier */
        $path = sprintf(self::TRANSACTIONS_PATH, self::ACCOUNT_ID, '');
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the transactions are listed */
        $response = $this->endpoint->handle(request: $request);

        /** @Then no transaction returns */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame(Code::OK->value, $response->getStatusCode());
        self::assertSame([], $body['data']);
        self::assertFalse($body['meta']['has_next']);
    }

    public function testFindAllOfAnUnknownAccountThenTheAccountIsNotFound(): void
    {
        /** @Given a request carrying an identifier no account holds */
        $path = sprintf(self::TRANSACTIONS_PATH, self::UNKNOWN_ACCOUNT_ID, '');
        $request = AccountRequests::get(path: $path, accountId: self::UNKNOWN_ACCOUNT_ID);

        /** @Then the account is not found */
        $this->expectException(AccountNotFound::class);

        /** @When the transactions are listed */
        $this->endpoint->handle(request: $request);
    }
}
