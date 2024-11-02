<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account\Mocks;

use Account\Application\Domain\Commands\OpenAccount;
use Account\Application\Domain\Exceptions\AccountAlreadyExists;
use Account\Application\Domain\Ports\Inbound\AccountOpening;
use PHPUnit\Framework\MockObject\Generator\RuntimeException;

final class AccountOpeningMock implements AccountOpening
{
    private array $documents = [];

    public function handle(OpenAccount $command): void
    {
        $documentNumber = $command->holder->document->getNumber();

        if ($documentNumber === '999999999999') {
            throw new RuntimeException('An unexpected error occurred.');
        }

        if (in_array($documentNumber, $this->documents, true)) {
            throw new AccountAlreadyExists(document: $command->holder->document);
        }

        $this->documents[] = $documentNumber;
    }
}
