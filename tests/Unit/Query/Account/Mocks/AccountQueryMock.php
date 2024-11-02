<?php

declare(strict_types=1);

namespace Account\Query\Account\Mocks;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Database\Records\Account;
use RuntimeException;

final class AccountQueryMock implements AccountQuery
{
    private array $accounts = [];

    public function save(Account $account): void
    {
        $this->accounts[$account->id] = $account;
    }

    public function findAccountById(string $id): ?Account
    {
        if ($id === '4798c22b-1f50-4ac8-9ddd-df6dcb210b41') {
            throw new RuntimeException('An unexpected error occurred.');
        }

        return $this->accounts[$id] ?? null;
    }
}
