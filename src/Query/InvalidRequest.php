<?php

declare(strict_types=1);

namespace Account\Query;

use InvalidArgumentException;

final class InvalidRequest extends InvalidArgumentException
{
    public function __construct(private readonly array $errors)
    {
        parent::__construct();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
