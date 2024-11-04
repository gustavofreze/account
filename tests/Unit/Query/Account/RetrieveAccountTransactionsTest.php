<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\Account\Mocks\AccountQueryMock;
use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Transaction;
use Account\Query\QueryErrorHandling;
use Account\RequestFactory;
use DateTimeImmutable;
use DateTimeInterface;
use Monolog\Test\TestCase;
use Ramsey\Uuid\Uuid;
use TinyBlocks\Http\HttpCode;

final class RetrieveAccountTransactionsTest extends TestCase
{
    private const string PATH = '/accounts/{accountId}/transactions';

    private AccountQuery $query;

    private RetrieveAccountTransactions $endpoint;

    private QueryErrorHandling $middleware;

    protected function setUp(): void
    {
        $this->query = new AccountQueryMock();
        $this->endpoint = new RetrieveAccountTransactions(query: $this->query);
        $this->middleware = new QueryErrorHandling(exceptionHandler: new QueryAccountExceptionHandler());
    }

    public function testRetrieveAccountTransactionsSuccessfully(): void
    {
        /** @Given an existing account is saved */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '12345678901'
        ]);
        $this->query->saveAccount(account: $account);

        /** @And the account ID is used to create a request */
        $request = RequestFactory::getFrom(
            path: self::PATH,
            parameters: ['accountId' => $account->id]
        );

        /** @And there are transactions associated with this account */
        $this->query->saveTransactions(transactions: [
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
                'operationTypeId' => 3
            ])
        ]);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(HttpCode::OK->value, $actual->getStatusCode());

        /** @And the response body should contain the transactions */
        $response = json_decode($actual->getBody()->__toString(), true);

        /** @Then it should match the expected structure */
        self::assertIsArray($response);
        self::assertNotEmpty($response);
        self::assertArrayHasKey('id', $response[0]);
        self::assertArrayHasKey('amount', $response[0]);
        self::assertArrayHasKey('created_at', $response[0]);
        self::assertArrayHasKey('account_id', $response[0]);
        self::assertArrayHasKey('operation_type_id', $response[0]);

        /** @And the transaction details should be correct */
        self::assertEquals(4, $response[0]['operation_type_id']);
        self::assertEquals('300.00', $response[0]['amount']);
        self::assertEquals($account->id, $response[0]['account_id']);
    }

    public function testRetrieveAccountTransactionsWithNoTransactions(): void
    {
        /** @Given an existing account is saved */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '12345678901'
        ]);
        $this->query->saveAccount(account: $account);

        /** @And the account ID is used to create a request */
        $request = RequestFactory::getFrom(
            path: self::PATH,
            parameters: ['accountId' => $account->id]
        );

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(HttpCode::OK->value, $actual->getStatusCode());

        /** @And the response body should be an empty array */
        $response = json_decode($actual->getBody()->__toString(), true);

        /** @Then it should be an empty array */
        self::assertIsArray($response);
        self::assertEmpty($response);
    }

    public function testRetrieveAccountTransactionsWithSingleTransaction(): void
    {
        /** @Given an existing account is saved */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '12345678901'
        ]);
        $this->query->saveAccount(account: $account);

        /** @And the account ID is used to create a request */
        $request = RequestFactory::getFrom(
            path: self::PATH,
            parameters: ['accountId' => $account->id]
        );

        /** @And there is a single transaction associated with this account */
        $this->query->saveTransactions(transactions: [
            Transaction::from(data: [
                'id'              => Uuid::uuid4()->toString(),
                'amount'          => '300.00',
                'createdAt'       => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                'accountId'       => $account->id,
                'operationTypeId' => 4
            ])
        ]);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(HttpCode::OK->value, $actual->getStatusCode());

        /** @And the response body should contain the transaction */
        $response = json_decode($actual->getBody()->__toString(), true);

        /** @Then it should match the expected structure */
        self::assertIsArray($response);
        self::assertNotEmpty($response);
        self::assertArrayHasKey('id', $response[0]);
        self::assertArrayHasKey('amount', $response[0]);
        self::assertArrayHasKey('created_at', $response[0]);
        self::assertArrayHasKey('account_id', $response[0]);
        self::assertArrayHasKey('operation_type_id', $response[0]);

        /** @And the transaction details should be correct */
        self::assertEquals(4, $response[0]['operation_type_id']);
        self::assertEquals('300.00', $response[0]['amount']);
        self::assertEquals($account->id, $response[0]['account_id']);
    }

    public function testExceptionWhenAccountNotFound(): void
    {
        /** @Given a request is made with a non-existent account ID */
        $parameters = ['accountId' => '0cba568f-1c8e-4fed-bef5-8b1166993dd2'];

        /** @And this data is used to create a request */
        $request = RequestFactory::getFrom(path: self::PATH, parameters: $parameters);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate not found */
        self::assertSame(HttpCode::NOT_FOUND->value, $actual->getStatusCode());

        /** @And the response body should contain an AccountNotFound error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('Account with ID <0cba568f-1c8e-4fed-bef5-8b1166993dd2> not found.', $response['error']);
    }
}
