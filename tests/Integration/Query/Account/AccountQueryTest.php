<?php

declare(strict_types=1);

namespace Test\Integration\Query\Account;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Database\Records\Account;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Test\Integration\IntegrationTestCase;
use Test\Integration\Query\Repository;

final class AccountQueryTest extends IntegrationTestCase
{
    private AccountQuery $query;

    private Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(AccountQuery::class);
        $this->repository = new Repository(connection: $this->get(Connection::class));
    }

    public function testFindAccountById(): void
    {
        /** @Given an account with specific details */
        $account = Account::from(data: [
            'id'                   => Uuid::uuid4()->toString(),
            'holderDocumentNumber' => '56005551000100'
        ]);

        /** @And the account is persisted */
        $this->repository->save(account: $account);

        /** @When I retrieve the account by its ID */
        $actual = $this->query->findAccountById(id: $account->id);

        /** @Then the retrieved account details should match the persisted account */
        self::assertEquals($account->id, $actual->id);
        self::assertEquals($account->holder->document, $actual->holder->document);
    }
}
