<?php

declare(strict_types=1);

namespace Account\Query\Account\Database;

final readonly class Queries
{
    public const FIND_BY_ID = '
        SELECT BIN_TO_UUID(id)        AS id,
               holder_document_number AS holderDocumentNumber
        FROM accounts
        WHERE id = UUID_TO_BIN(:id)';
}
