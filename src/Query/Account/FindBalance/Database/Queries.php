<?php

declare(strict_types=1);

namespace Account\Query\Account\FindBalance\Database;

final readonly class Queries
{
    public const string FIND_BALANCE = '
        SELECT SUM(amount) AS amount
        FROM transactions
        WHERE account_id = UUID_TO_BIN(:accountId)';
}
