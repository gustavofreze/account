<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Database;

final readonly class Queries
{
    public const string BASE = "
        SELECT BIN_TO_UUID(txn.id)         AS id,
               txn.amount                  AS amount,
               BIN_TO_UUID(txn.account_id) AS account_id,
               txn.operation_type_id       AS operation_type_id,
               DATE_FORMAT(txn.created_at, '%Y-%m-%dT%H:%i:%s.%f+00:00') AS created_at
        FROM transactions AS txn
    ";
}
