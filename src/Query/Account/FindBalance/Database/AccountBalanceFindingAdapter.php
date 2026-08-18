<?php

declare(strict_types=1);

namespace Account\Query\Account\FindBalance\Database;

use Account\Query\Account\FindBalance\AccountBalanceFinding;
use Account\Query\Account\FindBalance\ReadModel\AccountBalance;
use Account\Query\Account\Shared\Database\RegisteredAccounts;
use Account\Query\Shared\Exceptions\AccountNotFound;
use Doctrine\DBAL\Connection;

final readonly class AccountBalanceFindingAdapter implements AccountBalanceFinding
{
    private const float EMPTY_BALANCE = 0.00;

    public function __construct(private RegisteredAccounts $accounts, private Connection $connection)
    {
    }

    public function findBalance(string $accountId): AccountBalance
    {
        if (!$this->accounts->contains(accountId: $accountId)) {
            throw new AccountNotFound();
        }

        $amount = $this->connection
            ->executeQuery(sql: Queries::FIND_BALANCE, params: ['accountId' => $accountId])
            ->fetchOne();

        return AccountBalance::from(amount: is_numeric($amount) ? (float)$amount : self::EMPTY_BALANCE);
    }
}
