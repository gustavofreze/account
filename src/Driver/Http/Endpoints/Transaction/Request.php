<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Commands\Command;
use Account\Driver\Http\InvalidRequest;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\ValidatorBuilder;

final readonly class Request
{
    private const array TEMPLATES = [
        'amount'            => 'Must be positive.',
        'account_id'        => 'The value <{{input}}> is not a valid UUID.',
        'operation_type_id' => 'Must be positive.'
    ];

    public function __construct(private array $payload)
    {
        try {
            $amount = ValidatorBuilder::numericVal()->positive();
            $accountId = ValidatorBuilder::uuid();
            $operationTypeId = ValidatorBuilder::intType()->positive();

            ValidatorBuilder::arrayType()
                ->key('amount', $amount)
                ->key('account_id', $accountId)
                ->key('operation_type_id', $operationTypeId)
                ->assert($this->payload, self::TEMPLATES);
        } catch (ValidationException $exception) {
            throw new InvalidRequest(messages: $exception->getMessages());
        }
    }

    public function toCommand(): Command
    {
        return new TransactionCommand(payload: $this->payload)->build();
    }
}
