<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

final readonly class Queries
{
    public const string INSERT = '
        INSERT INTO accounts (id, holder_document_number)
        VALUES (UUID_TO_BIN(:id), :holderDocumentNumber)
    ';

    public const string FIND_BY_ID = "
        SELECT BIN_TO_UUID(acc.id)        AS id,
               acc.holder_document_number AS holderDocumentNumber,
               (SELECT COALESCE(MAX(evt.aggregate_version), 0)
                FROM outbox_events AS evt
                WHERE evt.aggregate_type = 'Account'
                  AND evt.aggregate_id = acc.id) AS aggregateVersion
        FROM accounts AS acc
        WHERE acc.id = UUID_TO_BIN(:id)
    ";

    public const string FIND_BALANCE = '
        SELECT COALESCE(SUM(amount), 0.00) AS amount
        FROM transactions
        WHERE account_id = UUID_TO_BIN(:accountId)
        FOR UPDATE
    ';

    public const string INSERT_TRANSACTION = '
        INSERT INTO transactions (id, account_id, operation_type_id, amount)
        VALUES (UUID_TO_BIN(:id), UUID_TO_BIN(:accountId), :operationTypeId, :amount)
    ';
}
