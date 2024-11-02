<?php

declare(strict_types=1);

namespace Account\Query\Account\Database\Records;

use TinyBlocks\Serializer\Serializer;
use TinyBlocks\Serializer\SerializerAdapter;

final readonly class Holder implements Serializer
{
    use SerializerAdapter;

    public function __construct(public string $document)
    {
    }
}
