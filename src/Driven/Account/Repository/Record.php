<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Balance;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;

final readonly class Record
{
    private function __construct(private ?array $result)
    {
    }

    public static function from(?array $result): Record
    {
        return new Record(result: $result);
    }

    public function toAccountOrNull(): ?Account
    {
        if (empty($this->result)) {
            return null;
        }

        $id = (string)$this->result['id'];
        $documentNumber = (string)$this->result['holderDocumentNumber'];

        return new Account(
            id: new AccountId(value: $id),
            holder: Holder::from(document: SimpleIdentity::from(number: $documentNumber))
        );
    }

    public function toBalance(): Balance
    {
        return empty($this->result)
            ? Balance::from(value: 0.00)
            : Balance::from(value: (float)$this->result['amount']);
    }
}
