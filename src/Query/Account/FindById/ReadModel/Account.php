<?php

declare(strict_types=1);

namespace Account\Query\Account\FindById\ReadModel;

final readonly class Account
{
    private function __construct(public string $id, public Holder $holder)
    {
    }

    public static function from(string $id, string $document): Account
    {
        return new Account(id: $id, holder: Holder::from(document: $document));
    }

    public function toArray(): array
    {
        return [
            'holder'     => $this->holder->toArray(),
            'account_id' => $this->id
        ];
    }
}
