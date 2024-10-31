<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class InvalidTransaction extends DomainException
{
    public function __construct(string $transactionType)
    {
        $template = 'The transaction type <%s> is invalid or not supported.';
        parent::__construct(message: sprintf($template, $transactionType));
    }
}
