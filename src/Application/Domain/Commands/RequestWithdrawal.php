<?php

declare(strict_types=1);

namespace Account\Application\Domain\Commands;

use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Transaction\Transaction;

final readonly class RequestWithdrawal implements Command
{
    public function __construct(public AccountId $id, public Transaction $transaction)
    {
    }
}
