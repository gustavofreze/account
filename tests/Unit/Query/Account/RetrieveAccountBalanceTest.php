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

final class RetrieveAccountBalanceTest extends TestCase
{
    private const string PATH = '/accounts/{accountId}/balance';

    private AccountQuery $query;

    private RetrieveAccountBalance $endpoint;

    private QueryErrorHandling $middleware;

    protected function setUp(): void
    {
        $this->query = new AccountQueryMock();
        $this->endpoint = new RetrieveAccountBalance(query: $this->query);
        $this->middleware = new QueryErrorHandling(exceptionHandler: new QueryAccountExceptionHandler());
    }

    public function testRetrieveAccountBalanceSuccessfully(): void
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

        /** @And the response body should contain the account balance */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame(200.00, $response['amount']);
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
