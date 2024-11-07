<?php

declare(strict_types=1);

namespace Account\Application\Handlers;

use Account\Application\Commands\DebitAccount;
use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Application\Ports\Inbound\AccountDebiting;
use Account\Application\Ports\Outbound\Accounts;

final readonly class AccountDebitingHandler implements AccountDebiting
{
    public function __construct(private Accounts $accounts)
    {
    }

    public function handle(DebitAccount $command): void
    {
        $id = $command->id;
        $account = $this->accounts->findById(id: $id);

        if ($account === null) {
            throw new AccountNotFound(id: $id);
        }

        $this->accounts->applyDebitTransactionTo(account: $account, transaction: $command->transaction);
    }
}
