<?php

declare(strict_types=1);

namespace Account\Driver\Http\Middlewares;

use Account\Driven\Shared\Logging\LoggerHandler;
use Account\Driven\Shared\Logging\Obfuscator\Obfuscators;
use Account\LoggerMock;
use Account\ResponseFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Uri;
use TinyBlocks\Http\HttpCode;
use TinyBlocks\Http\HttpMethod;

final class LoggingTest extends TestCase
{
    private LoggerInterface $logger;

    private Logging $middleware;

    protected function setUp(): void
    {
        $this->logger = new LoggerMock();
        $loggerHandler = new LoggerHandler(logger: $this->logger, obfuscators: Obfuscators::createFromEmpty());
        $this->middleware = new Logging(logger: $loggerHandler);
    }

    public function testProcessLogsRequestAndResponseSuccessfullyForPost(): void
    {
        /** @Given a valid HTTP request */
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        /** @And the request method is POST */
        $request->method('getMethod')->willReturn(HttpMethod::POST->value);

        /** @And the request URI is set to http://account.localhost/transactions */
        $request->method('getUri')->willReturn(new Uri('http', 'account.localhost', 80, '/transactions'));

        /** @And the request includes a Content-Type header */
        $request->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        /** @And the request payload contains the required fields */
        $request->method('getParsedBody')->willReturn([
            'amount'            => 25.50,
            'account_id'        => 'e2e1cbfc-a2bd-42a2-a519-d269826f90ed',
            'operation_type_id' => 3
        ]);

        /** @And the request handler is expected to return a successful response */
        $response = (new ResponseFactory(statusCode: HttpCode::NO_CONTENT->value, data: []))->build();
        $requestHandler->method('handle')->willReturn($response);

        /** @When the middleware processes the request */
        $actualResponse = $this->middleware->process($request, $requestHandler);

        /** @Then it should log the HTTP request information */
        $firstInfoOutputOrAt = $this->logger->firstInfoOutputOrAt();

        self::assertStringContainsString('uri', $firstInfoOutputOrAt);
        self::assertStringContainsString('"amount":25.5', $firstInfoOutputOrAt);
        self::assertStringContainsString('key=http_request', $firstInfoOutputOrAt);
        self::assertStringContainsString('"operation_type_id":3', $firstInfoOutputOrAt);
        self::assertStringContainsString($request->getUri()->__toString(), $firstInfoOutputOrAt);
        self::assertStringContainsString('"account_id":"e2e1cbfc-a2bd-42a2-a519-d269826f90ed"', $firstInfoOutputOrAt);

        /** @And the logged response should match the expected response */
        self::assertSame($response, $actualResponse);

        /** @And it should log the HTTP response information */
        $secondInfoOutput = $this->logger->firstInfoOutputOrAt(index: 1);

        self::assertStringContainsString('status', $secondInfoOutput);
        self::assertStringContainsString('key=http_response', $secondInfoOutput);
    }

    #[DataProvider('errorPostDataProvider')]
    public function testProcessLogsErrorResponseForPost(int $httpCode, string $message): void
    {
        /** @Given a valid HTTP request */
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        /** @And the request method is POST */
        $request->method('getMethod')->willReturn(HttpMethod::POST->value);

        /** @And the request URI is set to http://account.localhost/transactions */
        $request->method('getUri')->willReturn(new Uri('http', 'account.localhost', 80, '/transactions'));

        /** @And the request includes a Content-Type header */
        $request->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        /** @And the request payload contains the required fields */
        $request->method('getParsedBody')->willReturn([
            'amount'            => 25.50,
            'account_id'        => 'e2e1cbfc-a2bd-42a2-a519-d269826f90ed',
            'operation_type_id' => 3
        ]);

        /** @And the response has a status code indicating the provided error */
        $response = (new ResponseFactory(statusCode: $httpCode, data: ['error' => $message]))->build();
        $requestHandler->method('handle')->willReturn($response);

        /** @When the middleware processes the request */
        $actualResponse = $this->middleware->process($request, $requestHandler);

        /** @And the logged response should match the expected response */
        self::assertSame($response, $actualResponse);

        /** @And it should log the HTTP error response */
        $firstErrorOutputOrAt = $this->logger->firstErrorOutputOrAt();

        self::assertStringContainsString('status', $firstErrorOutputOrAt);
        self::assertStringContainsString($message, $firstErrorOutputOrAt);
        self::assertStringContainsString((string)$httpCode, $firstErrorOutputOrAt);
        self::assertStringContainsString('key=http_response', $firstErrorOutputOrAt);
    }

    public function testProcessLogsRequestAndResponseSuccessfullyForGet(): void
    {
        /** @Given a valid HTTP request */
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        /** @And the request method is GET */
        $request->method('getMethod')->willReturn(HttpMethod::GET->value);

        /** @And the request URI is set to http://account.localhost/accounts/cb87522e-f139-4f2b-b55b-0204ef9a6f77 */
        $request->method('getUri')->willReturn(
            new Uri('http', 'account.localhost', 80, '/accounts/cb87522e-f139-4f2b-b55b-0204ef9a6f77')
        );

        /** @And the request includes a Content-Type header */
        $request->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        /** @And the request handler is expected to return a successful response */
        $response = (new ResponseFactory(statusCode: HttpCode::OK->value, data: [
            'id'     => 'cb87522e-f139-4f2b-b55b-0204ef9a6f77',
            'holder' => ['document' => '761692090043414114']
        ]))->build();
        $requestHandler->method('handle')->willReturn($response);

        /** @When the middleware processes the request */
        $actualResponse = $this->middleware->process($request, $requestHandler);

        /** @Then it should log the HTTP request information */
        $firstInfoOutputOrAt = $this->logger->firstInfoOutputOrAt();

        self::assertStringContainsString('uri', $firstInfoOutputOrAt);
        self::assertStringContainsString('method', $firstInfoOutputOrAt);
        self::assertStringContainsString('headers', $firstInfoOutputOrAt);
        self::assertStringContainsString('payload', $firstInfoOutputOrAt);
        self::assertStringContainsString('key=http_request', $firstInfoOutputOrAt);
        self::assertStringContainsString($request->getUri()->__toString(), $firstInfoOutputOrAt);

        /** @And the logged response should match the expected response */
        self::assertSame($response, $actualResponse);

        /** @And it should log the HTTP response information */
        $secondInfoOutput = $this->logger->firstInfoOutputOrAt(index: 1);

        self::assertStringContainsString('status', $secondInfoOutput);
        self::assertStringContainsString('payload', $secondInfoOutput);
        self::assertStringContainsString('key=http_response', $secondInfoOutput);
    }

    #[DataProvider('errorGetDataProvider')]
    public function testProcessLogsErrorResponseForGet(int $httpCode, string $message): void
    {
        /** @Given a valid HTTP request */
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        /** @And the request method is GET */
        $request->method('getMethod')->willReturn(HttpMethod::GET->value);

        /** @And the request URI is set to http://account.localhost/accounts/e2e1cbfc-a2bd-42a2-a519-d269826f90ed */
        $request->method('getUri')->willReturn(
            new Uri('http', 'account.localhost', 80, '/accounts/e2e1cbfc-a2bd-42a2-a519-d269826f90ed')
        );

        /** @And the request includes a Content-Type header */
        $request->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        /** @And the response has a status code indicating the provided error */
        $response = (new ResponseFactory(statusCode: $httpCode, data: ['error' => $message]))->build();
        $requestHandler->method('handle')->willReturn($response);

        /** @When the middleware processes the request */
        $actualResponse = $this->middleware->process($request, $requestHandler);

        /** @And the logged response should match the expected response */
        self::assertSame($response, $actualResponse);

        /** @And it should log the HTTP error response */
        $firstErrorOutputOrAt = $this->logger->firstErrorOutputOrAt();

        self::assertStringContainsString('status', $firstErrorOutputOrAt);
        self::assertStringContainsString('payload', $firstErrorOutputOrAt);
        self::assertStringContainsString($message, $firstErrorOutputOrAt);
        self::assertStringContainsString((string)$httpCode, $firstErrorOutputOrAt);
        self::assertStringContainsString('key=http_response', $firstErrorOutputOrAt);
    }

    public static function errorPostDataProvider(): array
    {
        return [
            'Bad Request'           => [
                'httpCode' => HttpCode::BAD_REQUEST->value,
                'message'  => 'Bad Request'
            ],
            'Not Found'             => [
                'httpCode' => HttpCode::NOT_FOUND->value,
                'message'  => 'Not Found'
            ],
            'Internal Server Error' => [
                'httpCode' => HttpCode::INTERNAL_SERVER_ERROR->value,
                'message'  => 'Internal Server Error'
            ]
        ];
    }

    public static function errorGetDataProvider(): array
    {
        return [
            'Account Not Found' => [
                'httpCode' => HttpCode::NOT_FOUND->value,
                'message'  => 'Account with ID e2e1cbfc-a2bd-42a2-a519-d269826f90ed not found.'
            ]
        ];
    }
}
