<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Account;

use Account\Driver\Http\DriverExceptionMapping;
use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Query\Shared\Http\QueryExceptionMapping;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Test\Unit\RequestFactory;
use TinyBlocks\Http\Code;
use TinyBlocks\Http\ErrorHandler\ErrorMiddleware;

final class OpenAccountTest extends TestCase
{
    private OpenAccount $endpoint;

    private ErrorMiddleware $middleware;

    protected function setUp(): void
    {
        $this->endpoint = new OpenAccount(accountOpening: new AccountOpeningSpy());
        $this->middleware = ErrorMiddleware::create()
            ->withMappings(new DriverExceptionMapping(), new QueryExceptionMapping())
            ->build();
    }

    public function testOpenAccount(): void
    {
        /** @Given valid data to open an account */
        $payload = ['holder' => ['document' => '12345678901']];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::CREATED->value, $actual->getStatusCode());

        /** @And the response body should contain a valid account ID */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertTrue(Uuid::isValid($response['id']));
    }

    public function testExceptionWhenUnknownError(): void
    {
        /** @Given valid data to open an account */
        $payload = ['holder' => ['document' => '999999999999']];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler, and an unexpected error occurs */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate an internal server error */
        self::assertSame(Code::INTERNAL_SERVER_ERROR->value, $actual->getStatusCode());

        /** @And the response body should contain the unexpected error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('INTERNAL_ERROR', $response['code']);
        self::assertSame('An unexpected error occurred.', $response['message']);
    }

    public function testExceptionWhenInvalidRequest(): void
    {
        /** @Given invalid data to open an account */
        $payload = ['holder' => ['document' => '123-4567-890']];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate failure */
        self::assertSame(Code::UNPROCESSABLE_ENTITY->value, $actual->getStatusCode());

        /** @And the response body should contain a validation error for the document field */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('INVALID_REQUEST', $response['code']);
        self::assertSame('`.holder.document` must match the `/^\\d{11,50}$/` pattern', $response['message']);
    }

    public function testExceptionWhenAccountAlreadyExists(): void
    {
        /** @Given valid data to open an account */
        $payload = ['holder' => ['document' => '12345678901']];

        /** @And this data is used to create the first request */
        $response = $this->middleware->process(RequestFactory::postFrom(payload: $payload), $this->endpoint);

        /** @Then the first response status should indicate success */
        self::assertSame(Code::CREATED->value, $response->getStatusCode());

        /** @And another request is created with the same document */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the handler processes the duplicate request */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate a conflict */
        self::assertSame(Code::CONFLICT->value, $actual->getStatusCode());

        /** @And the response body should indicate that the account already exists */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('ACCOUNT_ALREADY_EXISTS', $response['code']);
        self::assertSame('An account already exists for this holder document number.', $response['message']);
    }
}
