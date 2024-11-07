<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\OpenAccount;
use Account\Application\Domain\Exceptions\AccountAlreadyExists;
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
        $holder = $command->holder;
        $account = $this->accounts->findByHolder(holder: $holder);

        if ($account !== null) {
            throw new AccountAlreadyExists(document: $holder->document);
        }

        $newAccount = Account::openFrom(id: $command->id, holder: $holder);
        $this->accounts->save(account: $newAccount);
    }
}
