<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Driven\Shared\Database\RelationalConnection;

final readonly class Balances
{
    private const float NO_MOVEMENT = 0.00;

    public function __construct(private RelationalConnection $connection)
    {
    }

    public function of(AccountId $id): Balance
    {
        $balance = $this->connection
            ->fetchOne(sql: Queries::FIND_BALANCE, bindings: ['accountId' => $id->identityValue()])
            ->map(transform: static fn(array $record): Balance => Balance::from(value: (float)$record['amount']))
            ->getOrNull();

        return ($balance ?? Balance::from(value: self::NO_MOVEMENT));
    }
}
