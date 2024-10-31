<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class InsufficientFunds extends DomainException
{
    public function __construct()
    {
        parent::__construct(message: 'Insufficient funds.');
    }
}
