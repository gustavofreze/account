<?php

declare(strict_types=1);

namespace Test\Integration\Query\Account\FindById;

use Account\Query\Account\FindById\Database\AccountFindingAdapter;
use Account\Query\Account\FindById\Http\FindAccountById;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Account\Query\Shared\Http\InvalidRequest;
use Doctrine\DBAL\Connection;
use Test\Integration\AccountRequests;
use Test\Integration\IntegrationTestCase;
use TinyBlocks\Http\Code;

final class FindAccountByIdTest extends IntegrationTestCase
{
    private const string ACCOUNT_PATH = '/accounts/%s';
    private const string ACCOUNT_ID = '0198bfc0-1111-7000-8000-aaaabbbbcccc';
    private const string HOLDER_DOCUMENT = '56005551000100';
    private const string UNKNOWN_ACCOUNT_ID = '0198bfc0-9999-7000-8000-ffffffffffff';

    private FindAccountById $endpoint;

    protected function setUp(): void
    {
        $connection = $this->get(class: Connection::class);

        $this->endpoint = new FindAccountById(accounts: new AccountFindingAdapter(connection: $connection));
    }

    public function testFindByIdThenTheAccountAndItsHolderReturn(): void
    {
        /** @Given a registered account */
        $this->fixtures()->insertAccount(id: self::ACCOUNT_ID, document: self::HOLDER_DOCUMENT);

        /** @And a request carrying its identifier */
        $path = sprintf(self::ACCOUNT_PATH, self::ACCOUNT_ID);
        $request = AccountRequests::get(path: $path, accountId: self::ACCOUNT_ID);

        /** @When the account is fetched */
        $response = $this->endpoint->handle(request: $request);

        /** @Then the account and its holder return */
        $body = json_decode((string)$response->getBody(), true);

        self::assertSame(Code::OK->value, $response->getStatusCode());
        self::assertSame(self::ACCOUNT_ID, $body['account_id']);
        self::assertSame(self::HOLDER_DOCUMENT, $body['holder']['document']);
    }

    public function testFindByIdWithAnIdentifierThatIsNoUuidThenTheRequestIsRejected(): void
    {
        /** @Given a request carrying an identifier that is not a UUID */
        $path = sprintf(self::ACCOUNT_PATH, 'not-a-uuid');
        $request = AccountRequests::get(path: $path, accountId: 'not-a-uuid');

        /** @Then the request is rejected before any lookup happens */
        $this->expectException(InvalidRequest::class);

        /** @When the account is fetched */
        $this->endpoint->handle(request: $request);
    }

    public function testFindByIdOfAnUnknownAccountThenTheAccountIsNotFound(): void
    {
        /** @Given a request carrying an identifier no account holds */
        $path = sprintf(self::ACCOUNT_PATH, self::UNKNOWN_ACCOUNT_ID);
        $request = AccountRequests::get(path: $path, accountId: self::UNKNOWN_ACCOUNT_ID);

        /** @Then the account is not found */
        $this->expectException(AccountNotFound::class);

        /** @When the account is fetched */
        $this->endpoint->handle(request: $request);
    }
}
