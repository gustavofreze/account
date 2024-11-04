<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator;

use Closure;

final readonly class Replacer
{
    public function __construct(private string $key, private Closure $maskingFunction)
    {
    }

    public function replace(array $data): array
    {
        return $this->apply(data: $data);
    }

    private function apply(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->apply(data: $value);
            }

            if ($key === $this->key && is_string($value)) {
                $data[$key] = ($this->maskingFunction)($value);
            }
        }

        return $data;
    }
}
