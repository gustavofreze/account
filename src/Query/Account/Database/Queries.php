<?php

declare(strict_types=1);

namespace Account\Query\Account\Database;

final readonly class Queries
{
    public const string FIND_BY_ID = '
        SELECT BIN_TO_UUID(id)        AS id,
               holder_document_number AS holderDocumentNumber
        FROM accounts
        WHERE id = UUID_TO_BIN(:accountId)';

    public const string FIND_BALANCE = '
        SELECT COALESCE(SUM(amount), 0.00) AS amount
        FROM transactions
        WHERE account_id = UUID_TO_BIN(:accountId)';

    public const string FIND_TRANSACTIONS = '
        SELECT BIN_TO_UUID(id)         AS id,
               amount                  AS amount,
               created_at              AS createdAt,
               BIN_TO_UUID(account_id) AS accountId,
               operation_type_id       AS operationTypeId
        FROM transactions
        WHERE account_id = UUID_TO_BIN(:accountId)';
}
