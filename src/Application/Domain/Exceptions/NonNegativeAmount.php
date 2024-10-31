<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class NonNegativeAmount extends DomainException
{
    public function __construct(float $value, int $scale)
    {
        $formattedValue = number_format($value, $scale, '.', '');
        $template = 'Negative amount <%s> is invalid. Amount must be negative.';
        parent::__construct(message: sprintf($template, $formattedValue));
    }
}
