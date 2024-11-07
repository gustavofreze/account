<?php

declare(strict_types=1);

namespace Account\Application\Commands;

use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Transaction\Transaction;

final readonly class DebitAccount implements Command
{
    public function __construct(public AccountId $id, public Transaction $transaction)
    {
    }
}
