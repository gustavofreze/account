<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging;

use Psr\Log\LoggerInterface;

/**
 * Defines a logging contract, extending PSR-3 standards.
 *
 * @see https://www.php-fig.org/psr/psr-3
 */
interface Logger extends LoggerInterface
{
    /**
     * Logs an informational entry.
     *
     * @param string $key Key to identify the log.
     * @param array $context Optional context data.
     */
    public function logInfo(string $key, array $context = []): void;

    /**
     * Logs an error entry.
     *
     * @param string $key Key to identify the error.
     * @param array $context Optional context data.
     */
    public function logError(string $key, array $context = []): void;
}
