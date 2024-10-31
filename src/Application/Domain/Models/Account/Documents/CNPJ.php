<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account\Documents;

use Account\Application\Domain\Exceptions\InvalidDocument;

final readonly class CNPJ implements Document
{
    public function __construct(private string $number)
    {
        if (preg_match('/^\d{14}$/', $this->number) !== 1) {
            throw new InvalidDocument(class: self::class, value: $this->number);
        }
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
