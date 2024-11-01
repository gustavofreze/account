<?php

declare(strict_types=1);

namespace Account\Query\Account\Dtos;

use TinyBlocks\Serializer\Serializer;
use TinyBlocks\Serializer\SerializerAdapter;

final readonly class Account implements Serializer
{
    use SerializerAdapter;

    private function __construct(public string $id, public Holder $holder)
    {
    }

    public static function from(array $data): Account
    {
        $id = (string)$data['id'];
        $holder = new Holder(document: (string)$data['holderDocumentNumber']);

        return new Account(id: $id, holder: $holder);
    }
}
