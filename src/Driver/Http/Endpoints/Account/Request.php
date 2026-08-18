<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Application\Commands\OpenAccount;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Driver\Http\InvalidRequest;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\ValidatorBuilder;

final readonly class Request
{
    public function __construct(private array $payload)
    {
        try {
            $document = ValidatorBuilder::stringType()->regex(SimpleIdentity::PATTERN);
            $holder = ValidatorBuilder::arrayType()->key('document', $document);

            ValidatorBuilder::arrayType()->key('holder', $holder)->assert($this->payload);
        } catch (ValidationException $exception) {
            throw new InvalidRequest(messages: $exception->getMessages());
        }
    }

    public function toCommand(): OpenAccount
    {
        $holder = $this->payload['holder'];
        $document = SimpleIdentity::from(number: $holder['document']);

        return new OpenAccount(id: AccountId::generate(), holder: Holder::from(document: $document));
    }
}
