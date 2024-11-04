<?php

declare(strict_types=1);

namespace Account\Query;

final readonly class PreparedQuery
{
    private function __construct(public string $query, public array $parameters)
    {
    }

    public static function from(string $query, array $parameters): PreparedQuery
    {
        return new PreparedQuery(query: $query, parameters: $parameters);
    }
}
