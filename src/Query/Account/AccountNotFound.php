<?php

declare(strict_types=1);

namespace Account\Query\Account;

use RuntimeException;

final class AccountNotFound extends RuntimeException
{
    public function __construct(string $id)
    {
        $template = 'Account with ID <%s> not found.';
        parent::__construct(message: sprintf($template, $id));
    }
}
