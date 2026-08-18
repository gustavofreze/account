<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository\Records;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use TinyBlocks\BuildingBlocks\Aggregate\AggregateVersion;

final readonly class AccountRecordReader
{
    public function toAccount(array $record): Account
    {
        $holder = Holder::from(document: SimpleIdentity::from(number: $record['holderDocumentNumber']));

        return Account::reconstituteStrict(
            identity: new AccountId(value: $record['id']),
            aggregateState: ['holder' => $holder],
            aggregateVersion: AggregateVersion::of(value: (int)$record['aggregateVersion'])
        );
    }
}
