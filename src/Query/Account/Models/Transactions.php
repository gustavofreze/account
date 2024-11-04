<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;

final class Transactions extends Collection
{
    public function toArray(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): array
    {
        $mapped = [];

        $this->each(actions: function (Transaction $transaction, int $index) use (&$mapped) {
            $mapped[$index] = $transaction->toArray();
        });

        return $mapped;
    }
}
