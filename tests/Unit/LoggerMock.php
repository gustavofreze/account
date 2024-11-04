<?php

declare(strict_types=1);

namespace Account;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

final class LoggerMock implements LoggerInterface
{
    use LoggerTrait;

    private array $infoOutputs = [];

    private array $errorOutputs = [];

    public function log($level, Stringable|string $message, array $context = []): void
    {
        match ($level) {
            'INFO'  => $this->infoOutputs[] = $message,
            'ERROR' => $this->errorOutputs[] = $message
        };
    }

    public function firstInfoOutputOrAt(int $index = 0): string
    {
        return $this->infoOutputs[$index] ?? '';
    }

    public function firstErrorOutputOrAt(int $index = 0): string
    {
        return $this->errorOutputs[$index] ?? '';
    }
}
