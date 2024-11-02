<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account\Documents;

use Account\Application\Domain\Exceptions\InvalidDocument;

final readonly class SimpleIdentity implements Document
{
    private const PATTERN = '/^\d{11,50}$/';

    private function __construct(private string $number)
    {
        if (!preg_match(self::PATTERN, $number)) {
            throw new InvalidDocument(class: self::class, value: $number);
        }
    }

    public static function from(string $number): SimpleIdentity
    {
        return new SimpleIdentity(number: $number);
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
