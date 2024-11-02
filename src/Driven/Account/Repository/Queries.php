<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

final readonly class Queries
{
    public const INSERT = '
        INSERT INTO accounts (id, holder_document_number)
        VALUES (UUID_TO_BIN(:id), :holderDocumentNumber)
    ';

    public const FIND_BY_ID = '
        SELECT BIN_TO_UUID(id) AS id,
               holder_document_number AS holderDocumentNumber
        FROM accounts
        WHERE id = UUID_TO_BIN(:id)
    ';

    public const FIND_BALANCE = '
        SELECT COALESCE(SUM(amount), 0.00) AS amount
        FROM transactions
        WHERE account_id = UUID_TO_BIN(:accountId)
        FOR UPDATE
    ';

    public const FIND_BY_HOLDER = '
        SELECT BIN_TO_UUID(id) AS id,
               holder_document_number AS holderDocumentNumber
        FROM accounts
        WHERE holder_document_number = :holderDocumentNumber
    ';

    public const INSERT_TRANSACTION = '
        INSERT INTO transactions (id, account_id, operation_type_id, amount)
        VALUES (UUID_TO_BIN(:id), UUID_TO_BIN(:accountId), :operationTypeId, :amount)
    ';
}
