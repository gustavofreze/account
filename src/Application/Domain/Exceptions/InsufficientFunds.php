<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use Account\Application\Domain\Models\Account\AccountId;
use DomainException;

final class InsufficientFunds extends DomainException
{
    public function __construct(AccountId $accountId)
    {
        $template = 'Account with ID <%s> has insufficient funds.';
        parent::__construct(message: sprintf($template, $accountId->toString()));
    }
}
