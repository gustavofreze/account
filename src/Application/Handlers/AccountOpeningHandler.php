<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\OpenAccount;
use Account\Application\Domain\Models\Account\Account;
use Account\Application\Ports\Inbound\AccountOpening;
use Account\Application\Ports\Outbound\Accounts;

final readonly class AccountOpeningHandler implements AccountOpening
{
    public function __construct(private Accounts $accounts)
    {
    }

    public function handle(OpenAccount $command): void
    {
        $account = Account::openFrom(id: $command->id, holder: $command->holder);

        $this->accounts->save(account: $account);
    }
}
