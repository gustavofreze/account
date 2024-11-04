<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use Account\Application\Domain\Models\Account\AccountId;
use DomainException;

final class AccountNotFound extends DomainException
{
    public function __construct(AccountId $id)
    {
        $template = 'Account with ID <%s> not found.';
        parent::__construct(message: sprintf($template, $id->toString()));
    }
}
