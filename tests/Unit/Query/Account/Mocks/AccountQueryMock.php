<?php

declare(strict_types=1);

namespace Account\Query\Account\Mocks;

use Account\Query\Account\AccountQuery;
use Account\Query\Account\Models\Account;
use Account\Query\Account\Models\Balance;
use Account\Query\Account\Models\Transaction;
use Account\Query\Account\Models\Transactions;
use Account\Query\Filters;
use RuntimeException;

final class AccountQueryMock implements AccountQuery
{
    private array $accounts = [];

    private array $transactions = [];

    public function saveAccount(Account $account): void
    {
        $this->accounts[$account->id] = $account;
    }

    public function saveTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function findById(string $accountId): ?Account
    {
        if ($accountId === '4798c22b-1f50-4ac8-9ddd-df6dcb210b41') {
            throw new RuntimeException('An unexpected error occurred.');
        }

        return $this->accounts[$accountId] ?? null;
    }

    public function balanceOf(string $accountId): Balance
    {
        $extractAmount = fn(Transaction $transaction): float => $transaction->amount;

        return Balance::from(data: ['amount' => array_sum(array_map($extractAmount, $this->transactions))]);
    }

    public function transactionsOf(string $accountId, Filters $filters): Transactions
    {
        return Transactions::createFrom(elements: $this->transactions);
    }
}
