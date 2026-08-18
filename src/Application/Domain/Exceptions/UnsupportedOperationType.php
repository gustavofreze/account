<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use InvalidArgumentException;

final class UnsupportedOperationType extends InvalidArgumentException
{
    public function __construct(public readonly string $value)
    {
    }
}
