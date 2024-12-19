<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Driver\Http\Endpoints\Account\Mocks\AccountOpeningMock;
use Account\Driver\Http\Middlewares\ErrorHandling;
use Account\RequestFactory;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use TinyBlocks\Http\Code;

final class OpenAccountTest extends TestCase
{
    private OpenAccount $endpoint;

    private ErrorHandling $middleware;

    protected function setUp(): void
    {
        $this->endpoint = new OpenAccount(useCase: new AccountOpeningMock());
        $this->middleware = new ErrorHandling(exceptionHandler: new OpenAccountExceptionHandler());
    }

    public function testOpenAccount(): void
    {
        /** @Given valid data to open an account */
        $payload = ['holder' => ['document' => '12345678901']];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

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
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate an internal server error */
        self::assertSame(Code::INTERNAL_SERVER_ERROR->value, $actual->getStatusCode());

        /** @And the response body should contain the unexpected error message */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('An unexpected error occurred.', $response['error']);
    }

    public function testExceptionWhenInvalidRequest(): void
    {
        /** @Given invalid data to open an account */
        $payload = ['holder' => ['document' => '123-4567-890']];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate failure */
        self::assertSame(Code::UNPROCESSABLE_ENTITY->value, $actual->getStatusCode());

        /** @And the response body should contain a validation error for the document field */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('document must contain only digits (0-9)', $response['error']['holder']);
    }

    public function testExceptionWhenAccountAlreadyExists(): void
    {
        /** @Given valid data to open an account */
        $payload = ['holder' => ['document' => '12345678901']];

        /** @And this data is used to create the first request */
        $response = $this->middleware->process(
            request: RequestFactory::postFrom(payload: $payload),
            handler: $this->endpoint
        );

        /** @Then the first response status should indicate success */
        self::assertSame(Code::CREATED->value, $response->getStatusCode());

        /** @And another request is created with the same document */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the handler processes the duplicate request */
        $actual = $this->middleware->process(request: $request, handler: $this->endpoint);

        /** @Then the response status should indicate a conflict */
        self::assertSame(Code::CONFLICT->value, $actual->getStatusCode());

        /** @And the response body should indicate that the account already exists */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('An account with document number <12345678901> already exists.', $response['error']);
    }
}
