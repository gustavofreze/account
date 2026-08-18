<?php

declare(strict_types=1);

namespace Test\Integration\Query\Account\FindBalance;

use Account\Query\Account\FindBalance\Database\AccountBalanceFindingAdapter;
use Account\Query\Account\FindBalance\Http\FindAccountBalance;
use Account\Query\Account\Shared\Database\RegisteredAccounts;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Doctrine\DBAL\Connection;
use Test\Integration\AccountRequests;
use Test\Integration\IntegrationTestCase;
use TinyBlocks\Http\Code;

final class FindAccountBalanceTest extends IntegrationTestCase
{
    private const string CREATED_AT = '2026-08-11 10:00:00.000000';
    private const string BALANCE_PATH = '/accounts/%s/balance';
    private const string ACCOUNT_ID = '0198bfc0-1111-7000-8000-aaaabbbbcccc';
    private const string HOLDER_DOCUMENT = '56005551000100';
    private const string UNKNOWN_ACCOUNT_ID = '0198bfc0-9999-7000-8000-ffffffffffff';

    private FindAccountBalance $endpoint;

    protected function setUp(): void
    {
        $connection = $this->get(class: Connection::class);

        $this->endpoint = new FindAccountBalance(
            balances: new AccountBalanceFindingAdapter(
                accounts: new RegisteredAccounts(connection: $connection),
                connection: $connection
            )
        );
    }

    public function testFindBalanceOfAnAccountWithMovementsThenTheirSumReturns(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And a credit and a debit recorded against it */
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000001',
            amount: 150.75,
            accountId: self::ACCOUNT_ID,
            createdAt: self::CREATED_AT,
            operationTypeId: 4
        );
        $this->fixtures()->insertTransaction(
            id: '0198bfc0-2222-7000-8000-000000000002',
            amount: -50.25,
            accountId: self::ACCOUNT_ID,
            createdAt: self::CREATED_AT,
            operationTypeId: 2
        );

        /** @And a request carrying the account identifier */
        $path = sprintf(self::BALANCE_PATH, self::ACCOUNT_ID);
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the balance is fetched */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the sum of the movements returns */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame(Code::OK->value, $response->getStatusCode());
        self::assertSame(100.50, $body['amount']);
    }

    public function testFindBalanceOfAnAccountWithoutMovementsThenZeroReturns(): void
    {
        /** @Given a registered account that never moved */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And a request carrying the account identifier */
        $path = sprintf(self::BALANCE_PATH, self::ACCOUNT_ID);
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the balance is fetched */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the balance answers zero */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame(Code::OK->value, $response->getStatusCode());
        self::assertSame(0.00, $body['amount']);
    }

    public function testFindBalanceOfAnUnknownAccountThenTheAccountIsNotFound(): void
    {
        /** @Given a request carrying an identifier no account holds */
        $path = sprintf(self::BALANCE_PATH, self::UNKNOWN_ACCOUNT_ID);
        $request = AccountRequests::get(path: $path, accountId: self::UNKNOWN_ACCOUNT_ID);

        /** @Then the account is not found */
        $this->expectException(AccountNotFound::class);

        /** @When the balance is fetched */
        $this->endpoint->handle(request: $request);
    }
}
