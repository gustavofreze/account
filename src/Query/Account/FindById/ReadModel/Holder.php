<?php

declare(strict_types=1);

namespace Account\Query\Account\FindById\ReadModel;

final readonly class Holder
{
    private function __construct(public string $document)
    {
    }

    public static function from(string $document): Holder
    {
        return new Holder(document: $document);
    }

    public function toArray(): array
    {
        return ['document' => $this->document];
    }
}
