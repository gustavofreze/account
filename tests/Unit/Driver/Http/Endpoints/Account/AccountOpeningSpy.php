<?php

declare(strict_types=1);

namespace Test\Unit\Driver\Http\Endpoints\Account;

use Account\Application\Commands\OpenAccount;
use Account\Application\Exceptions\AccountAlreadyExists;
use Account\Application\Ports\Inbound\AccountOpening;
use RuntimeException;

final class AccountOpeningSpy implements AccountOpening
{
    private array $documents = [];

    public function handle(OpenAccount $command): void
    {
        $documentNumber = $command->holder->document->getNumber();

        if ($documentNumber === '999999999999') {
            throw new RuntimeException('An unexpected error occurred.');
        }

        if (in_array($documentNumber, $this->documents, true)) {
            throw new AccountAlreadyExists();
        }

        $this->documents[] = $documentNumber;
    }
}
