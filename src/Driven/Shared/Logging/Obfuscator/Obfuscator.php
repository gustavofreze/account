<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator;

/**
 * Defines the contract for classes responsible for obfuscating sensitive information in data.
 */
interface Obfuscator
{
    /**
     * Obfuscates sensitive data and returns the modified data.
     *
     * @param array $data The data to be obfuscated.
     * @return array The obfuscated data.
     */
    public function obfuscate(array $data): array;
}
