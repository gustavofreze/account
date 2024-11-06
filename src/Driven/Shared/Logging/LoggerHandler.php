<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging;

use Account\Driven\Shared\Logging\Obfuscator\Obfuscators;
use Account\Environment;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

final readonly class LoggerHandler implements Logger
{
    use LoggerTrait;

    private const string LOG_TEMPLATE = "%s component=%s type=%s key=%s data=%s\n";

    private string $component;

    public function __construct(private LoggerInterface $logger, private Obfuscators $obfuscators)
    {
        $this->component = Environment::get(variable: 'APP_NAME')->toString();
    }

    public function logInfo(string $key, array $context = []): void
    {
        $this->log(level: LogLevel::INFO, message: $key, context: $context);
    }

    public function logError(string $key, array $context = []): void
    {
        $this->log(level: LogLevel::ERROR, message: $key, context: $context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $level = strtoupper($level);
        $context = json_encode($this->obfuscators->applyIn(data: $context), JSON_UNESCAPED_SLASHES);
        $timestamp = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);

        $data = sprintf(self::LOG_TEMPLATE, $timestamp, $this->component, $level, $message, $context);

        $this->logger->log($level, $data);
    }
}
