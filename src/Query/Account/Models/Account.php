<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

final readonly class Account
{
    private function __construct(public string $id, public Holder $holder)
    {
    }

    public static function from(array $data): Account
    {
        $id = $data['id'];
        $holder = Holder::from(data: $data);

        return new Account(id: $id, holder: $holder);
    }

    public function toArray(): array
    {
        return [
            'holder'     => $this->holder->toArray(),
            'account_id' => $this->id
        ];
    }
}
