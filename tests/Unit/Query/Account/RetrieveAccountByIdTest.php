<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\Account\Mocks\AccountQueryMock;
use Account\Query\Account\Models\Account;
use Account\Query\QueryErrorHandling;
use Account\RequestFactory;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use TinyBlocks\Http\Code;

final class RetrieveAccountByIdTest extends TestCase
{
    private const string PATH = '/accounts/{accountId}';

    private AccountQuery $query;

    private RetrieveAccountById $endpoint;

    private QueryErrorHandling $middleware;

    protected function setUp(): void
    {
        $this->query = new AccountQueryMock();
        $this->endpoint = new RetrieveAccountById(query: $this->query);
        $this->middleware = new QueryErrorHandling(exceptionHandler: new QueryAccountExceptionHandler());
    }

    public function testFindAccountById(): void
    {
        /** @Given an existing account is saved */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '12345678901'
        ]);
        $this->query->saveAccount(account: $account);

        /** @And the account ID is used to create a request */
        $request = RequestFactory::getFrom(path: self::PATH, parameters: ['accountId' => $account->id]);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::OK->value, $actual->getStatusCode());

        /** @And the response body should contain the account details */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame($account->id, $response['account_id']);
        self::assertSame($account->holder->document, $response['holder']['document']);
    }

    public function testExceptionWhenUnknownError(): void
    {
        /** @Given a valid account ID that triggers an unexpected error */
        $parameters = ['accountId' => '4798c22b-1f50-4ac8-9ddd-df6dcb210b41'];

        /** @And a request is created with this ID */
        $request = RequestFactory::getFrom(path: self::PATH, parameters: $parameters);

        /** @When the request is processed, triggering an unknown error */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate an internal server error */
        self::assertSame(Code::INTERNAL_SERVER_ERROR->value, $actual->getStatusCode());

        /** @And the response body should contain the unexpected error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('An unexpected error occurred.', $response['error']);
    }

    public function testExceptionWhenInvalidRequest(): void
    {
        /** @Given invalid data with an improperly formatted account ID */
        $parameters = ['accountId' => 'invalid-uuid-format'];

        /** @And this data is used to create a request */
        $request = RequestFactory::getFrom(path: self::PATH, parameters: $parameters);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate an unprocessable entity */
        self::assertSame(Code::UNPROCESSABLE_ENTITY->value, $actual->getStatusCode());

        /** @And the response body should contain an InvalidRequest error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('The value <invalid-uuid-format> is not a valid UUID.', $response['error']['accountId']);
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
        self::assertSame(Code::NOT_FOUND->value, $actual->getStatusCode());

        /** @And the response body should contain an AccountNotFound error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('Account with ID <0cba568f-1c8e-4fed-bef5-8b1166993dd2> not found.', $response['error']);
    }
}
