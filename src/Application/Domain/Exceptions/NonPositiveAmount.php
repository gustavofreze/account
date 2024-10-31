<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class NonPositiveAmount extends DomainException
{
    public function __construct(float $value, int $scale)
    {
        $formattedValue = number_format($value, $scale, '.', '');
        $template = 'Non-positive amount <%s> is invalid. Amount must be greater than zero.';
        parent::__construct(message: sprintf($template, $formattedValue));
    }
}
