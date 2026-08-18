<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Database;

final readonly class Result
{
    public function __construct(private int $affectedRows)
    {
    }

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }
}
