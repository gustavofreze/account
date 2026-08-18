<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use Account\Application\Domain\Models\Account\AccountId;
use DomainException;

final class AccountWithInsufficientFunds extends DomainException
{
    public function __construct(public readonly AccountId $accountId)
    {
    }
}
