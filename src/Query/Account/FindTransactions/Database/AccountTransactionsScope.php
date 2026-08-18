<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Database;

use TinyBlocks\HttpQuery\Clause\SqlClause;

final readonly class AccountTransactionsScope implements SqlClause
{
    private function __construct(private string $sql, private array $parameters)
    {
    }

    public static function from(string $accountId): AccountTransactionsScope
    {
        return new AccountTransactionsScope(
            sql: 'txn.account_id = UUID_TO_BIN(:account_id)',
            parameters: ['account_id' => $accountId]
        );
    }

    public function sql(): string
    {
        return $this->sql;
    }

    public function isEmpty(): bool
    {
        return $this->sql === '';
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
