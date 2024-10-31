<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Models\Account\Documents\Document;

final readonly class Holder
{
    private function __construct(public Document $document)
    {
    }

    public static function from(Document $document): Holder
    {
        return new Holder(document: $document);
    }
}
