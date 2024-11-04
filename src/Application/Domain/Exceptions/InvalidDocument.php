<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use DomainException;

final class InvalidDocument extends DomainException
{
    public function __construct(string $class, string $value)
    {
        $template = 'The value <%s> is not a valid %s.';
        $className = $this->getClassNameFrom(class: $class);
        parent::__construct(message: sprintf($template, $value, $className));
    }

    private function getClassNameFrom(string $class): string
    {
        $parts = explode('\\', $class);
        return end($parts);
    }
}
