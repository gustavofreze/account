<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class NonPositiveOrZeroAmount extends DomainException
{
    public function __construct(float $value, int $scale)
    {
        $template = 'Amount <%s> is invalid. Amount must be positive or zero.';
        $formattedValue = number_format($value, $scale, '.', '');
        parent::__construct(message: sprintf($template, $formattedValue));
    }
}
