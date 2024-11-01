<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\InvalidRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Slim\Routing\Route;

final class Request
{
    private string $accountId;

    public function __construct(private readonly ServerRequestInterface $request)
    {
        $this->validate();
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    private function validate(): void
    {
        try {
            $template = 'The value <%s> is not a valid UUID.';

            /** @var Route<ContainerInterface> $route */
            $route = $this->request->getAttribute('__route__');
            $accountId = (string)$route->getArgument('accountId');

            $uuidValidator = Validator::uuid()
                ->setName('accountId')
                ->setTemplate(sprintf($template, $accountId));

            $uuidValidator->assert($accountId);

            $this->accountId = $accountId;
        } catch (NestedValidationException $exception) {
            throw new InvalidRequest(errors: $exception->getMessages());
        }
    }
}
