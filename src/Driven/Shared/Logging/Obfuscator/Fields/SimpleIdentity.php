<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator\Fields;

use Account\Driven\Shared\Logging\Obfuscator\Obfuscator;
use Account\Driven\Shared\Logging\Obfuscator\Replacer;

final readonly class SimpleIdentity implements Obfuscator
{
    private const int ZERO = 0;
    private const int VISIBLE_DIGITS = 5;

    private const string KEY = 'document';

    public function obfuscate(array $data): array
    {
        $maskFunction = static function (string $value): string {
            $maskedLength = max(self::ZERO, strlen($value) - self::VISIBLE_DIGITS);
            $maskedPart = str_repeat('*', $maskedLength);
            $visiblePart = substr($value, -self::VISIBLE_DIGITS);

            return sprintf('%s%s', $maskedPart, $visiblePart);
        };

        $replacer = new Replacer(key: self::KEY, maskingFunction: $maskFunction);

        return $replacer->replace(data: $data);
    }
}
