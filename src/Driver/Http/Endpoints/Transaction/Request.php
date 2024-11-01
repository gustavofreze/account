<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Domain\Commands\Command;
use Account\Driver\Http\Endpoints\InvalidRequest;
use Account\Driver\Http\Endpoints\Transaction\Factories\CommandFactory;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

final class Request
{
    private CommandFactory $factory;

    public function __construct(private readonly array $payload)
    {
        $this->validate();
        $this->factory = new CommandFactory(payload: $this->payload);
    }

    public function toCommand(): Command
    {
        return $this->factory->build();
    }

    private function validate(): void
    {
        try {
            $amountValidator = Validator::numericVal()->positive()->setTemplate('Must be positive.');
            $accountIdValidator = Validator::uuid()->setTemplate('The value <{{input}}> is not a valid UUID.');
            $operationTypeIdValidator = Validator::intType()->positive()->setTemplate('Must be positive.');

            $payloadValidator = Validator::key('amount', $amountValidator)
                ->key('accountId', $accountIdValidator)
                ->key('operationTypeId', $operationTypeIdValidator);

            $payloadValidator->assert($this->payload);
        } catch (NestedValidationException $exception) {
            throw new InvalidRequest(errors: $exception->getMessages());
        }
    }
}
