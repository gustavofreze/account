<?php

declare(strict_types=1);

namespace Account\Query\Account\Shared\Database;

final readonly class Queries
{
    public const string EXISTS_BY_ID = '
        SELECT 1
        FROM accounts
        WHERE id = UUID_TO_BIN(:accountId)';
}
