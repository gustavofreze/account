<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Transaction;

use Account\Application\Domain\Models\Transaction\OperationType;
use Account\Driver\Http\DriverExceptionMapping;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Driver\Http\Endpoints\Transaction\TransactionDispatcher;
use Account\Query\Shared\Http\QueryExceptionMapping;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Test\Unit\RequestFactory;
use TinyBlocks\Http\Code;
use TinyBlocks\Http\ErrorHandler\ErrorMiddleware;

final class CreateTransactionTest extends TestCase
{
    private CreateTransaction $endpoint;

    private ErrorMiddleware $middleware;

    protected function setUp(): void
    {
        $dispatcher = new TransactionDispatcher(
            accountDebiting: new AccountDebitingSpy(),
            accountCrediting: new AccountCreditingSpy(),
            accountWithdrawal: new AccountWithdrawalSpy()
        );

        $this->endpoint = new CreateTransaction(dispatcher: $dispatcher);
        $this->middleware = ErrorMiddleware::create()
            ->withMappings(new DriverExceptionMapping(), new QueryExceptionMapping())
            ->build();
    }

    public function testNormalPurchaseTransaction(): void
    {
        /** @Given that I have the data to create a normal purchase transaction */
        $payload = [
            'amount'            => 123.45,
            'account_id'        => Uuid::uuid7()->toString(),
            'operation_type_id' => OperationType::NORMAL_PURCHASE->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::NO_CONTENT->value, $actual->getStatusCode());
    }

    public function testPurchaseWithInstallmentsTransaction(): void
    {
        /** @Given that I have the data to create a purchase with installment's transaction */
        $payload = [
            'amount'            => 200.00,
            'account_id'        => Uuid::uuid7()->toString(),
            'operation_type_id' => OperationType::PURCHASE_WITH_INSTALLMENTS->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::NO_CONTENT->value, $actual->getStatusCode());
    }

    public function testWithdrawalTransaction(): void
    {
        /** @Given that I have the data to create a withdrawal transaction */
        $payload = [
            'amount'            => 50.00,
            'account_id'        => Uuid::uuid7()->toString(),
            'operation_type_id' => OperationType::WITHDRAWAL->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::NO_CONTENT->value, $actual->getStatusCode());
    }

    public function testCreditVoucherTransaction(): void
    {
        /** @Given that I have the data to create a credit voucher transaction */
        $payload = [
            'amount'            => 300.00,
            'account_id'        => Uuid::uuid7()->toString(),
            'operation_type_id' => OperationType::CREDIT_VOUCHER->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate success */
        self::assertSame(Code::NO_CONTENT->value, $actual->getStatusCode());
    }

    public function testExceptionWhenUnknownError(): void
    {
        /** @Given valid data to create a transaction */
        $payload = [
            'amount'            => 300.00,
            'account_id'        => '2ab2ea68-2b17-4932-aa3a-1a47a84960da',
            'operation_type_id' => OperationType::CREDIT_VOUCHER->value
        ];

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
        /** @Given invalid data to create a transaction */
        $payload = [
            'amount'            => 300.00,
            'account_id'        => 'xxxxxx',
            'operation_type_id' => OperationType::CREDIT_VOUCHER->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate failure */
        self::assertSame(Code::UNPROCESSABLE_ENTITY->value, $actual->getStatusCode());

        /** @And the response body should contain a validation error for the account_id field */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('INVALID_REQUEST', $response['code']);
        self::assertSame('The value <"xxxxxx"> is not a valid UUID.', $response['message']);
    }

    public function testExceptionWhenAccountNotFound(): void
    {
        /** @Given a non-existent account ID for a transaction */
        $payload = [
            'amount'            => 300.00,
            'account_id'        => '07072a4b-ded7-41ea-a3e0-055678cb9a7b',
            'operation_type_id' => OperationType::WITHDRAWAL->value
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate a Not Found error */
        self::assertSame(Code::NOT_FOUND->value, $actual->getStatusCode());

        /** @And the response body should contain a not found error message for the account ID */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('ACCOUNT_NOT_FOUND', $response['code']);
        self::assertSame('Account not found.', $response['message']);
    }

    public function testExceptionWhenUnsupportedOperationTypeId(): void
    {
        /** @Given invalid data to create a transaction */
        $payload = [
            'amount'            => 300.00,
            'account_id'        => Uuid::uuid7()->toString(),
            'operation_type_id' => 5
        ];

        /** @And this data is used to create a request */
        $request = RequestFactory::postFrom(payload: $payload);

        /** @When the request is processed by the handler */
        $actual = $this->middleware->process($request, $this->endpoint);

        /** @Then the response status should indicate an unprocessable entity */
        self::assertSame(Code::UNPROCESSABLE_ENTITY->value, $actual->getStatusCode());

        /** @And the response body should report the operation type as unsupported */
        $response = json_decode($actual->getBody()->__toString(), true);

        self::assertSame('UNSUPPORTED_OPERATION_TYPE', $response['code']);
        self::assertSame('The operation type is not supported.', $response['message']);
    }
}
