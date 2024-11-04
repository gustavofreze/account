<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator;

final readonly class Name implements Obfuscator
{
    private const string KEY = 'name';

    public function obfuscate(array $data): array
    {
        if (isset($data[self::KEY]) && is_string($data[self::KEY])) {
            $name = $data[self::KEY];
            $length = strlen($name);
            $maskedLength = intval($length / 2);
            $lastPart = substr($name, -$maskedLength);
            $maskedPart = str_repeat('*', $length - $maskedLength);

            $data[self::KEY] = sprintf('%s%s', $maskedPart, $lastPart);
        }

        return $data;
    }
}
