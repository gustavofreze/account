<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator;

use TinyBlocks\Collection\Collection;

final class Obfuscators extends Collection
{
    public function applyIn(array $data): array
    {
        /** @var Obfuscator $obfuscator */
        foreach ($this as $obfuscator) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $obfuscator->obfuscate(data: $value);
                    continue;
                }

                $data[$key] = $obfuscator->obfuscate(data: [$key => $value])[$key];
            }
        }

        return $data;
    }
}
