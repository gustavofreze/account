<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Application\Domain\Commands\OpenAccount;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use Account\Application\Domain\Models\Account\Holder;
use Account\Driver\Http\Endpoints\InvalidRequest;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

final readonly class Request
{
    public function __construct(private array $payload)
    {
        $this->validate();
    }

    public function toCommand(): OpenAccount
    {
        $holder = (array)$this->payload['holder'];
        $document = SimpleIdentity::from(number: (string)$holder['document']);

        return new OpenAccount(id: AccountId::generate(), holder: Holder::from(document: $document));
    }

    private function validate(): void
    {
        try {
            $documentValidator = Validator::stringType()->digit()->length(11);
            $holderValidator = Validator::key('document', $documentValidator);
            $payloadValidator = Validator::key('holder', $holderValidator);
            $payloadValidator->assert($this->payload);
        } catch (NestedValidationException $exception) {
            throw new InvalidRequest(errors: $exception->getMessages());
        }
    }
}
