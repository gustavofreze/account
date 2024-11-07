<?php

declare(strict_types=1);

namespace Account\Application\Commands;

use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Holder;

final readonly class OpenAccount implements Command
{
    public function __construct(public AccountId $id, public Holder $holder)
    {
    }
}
