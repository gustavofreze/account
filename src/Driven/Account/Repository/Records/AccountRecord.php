<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository\Records;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;

final readonly class AccountRecord
{
    private function __construct(private ?array $record)
    {
    }

    public static function from(?array $result): AccountRecord
    {
        return new AccountRecord(record: $result);
    }

    public function toAccountOrNull(): ?Account
    {
        if (empty($this->record)) {
            return null;
        }

        $id = $this->record['id'];
        $documentNumber = $this->record['holderDocumentNumber'];

        return new Account(
            id: new AccountId(value: $id),
            holder: Holder::from(document: SimpleIdentity::from(number: $documentNumber))
        );
    }
}
