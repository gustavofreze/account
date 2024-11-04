<?php

declare(strict_types=1);

namespace Test\Integration\Application;

use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Driven\Account\Repository\Queries;
use Account\Driven\Account\Repository\Records\BalanceRecord;
use Account\Driven\Shared\Database\RelationalConnection;

final readonly class Repository
{
    public function __construct(private RelationalConnection $connection)
    {
    }

    public function balanceOf(AccountId $id): Balance
    {
        $result = $this->connection
            ->with()
            ->query(sql: Queries::FIND_BALANCE)
            ->bind(data: [':accountId' => $id->toString()])
            ->execute()
            ->fetchOne();

        return BalanceRecord::from(result: $result)->toBalance();
    }
}
