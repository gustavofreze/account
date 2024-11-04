<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

final readonly class Holder
{
    private function __construct(public string $document)
    {
    }

    public static function from(array $data): Holder
    {
        return new Holder(document: $data['holderDocumentNumber']);
    }

    public function toArray(): array
    {
        return ['document' => $this->document];
    }
}
