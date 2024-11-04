<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

use TinyBlocks\Collection\Collection;

final class Transactions extends Collection
{
    public function all(): array
    {
        $mapped = [];

        $this->each(actions: function (Transaction $transaction) use (&$mapped) {
            $mapped[] = $transaction->toArray();
        });

        return $mapped;
    }
}
