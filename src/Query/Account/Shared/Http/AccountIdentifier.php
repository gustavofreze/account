<?php

declare(strict_types=1);

namespace Account\Query\Account\Shared\Http;

use Account\Query\Shared\Http\InvalidRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\ValidatorBuilder;
use Slim\Routing\Route;

final readonly class AccountIdentifier
{
    private function __construct(public string $value)
    {
    }

    public static function from(ServerRequestInterface $request): AccountIdentifier
    {
        /** @var Route<ContainerInterface> $route */
        $route = $request->getAttribute('__route__');
        $accountId = $route->getArgument('accountId');

        try {
            $template = 'The value <%s> is not a valid UUID.';

            ValidatorBuilder::uuid()->assert($accountId, sprintf($template, $accountId));

            return new AccountIdentifier(value: $accountId);
        } catch (ValidationException $exception) {
            throw new InvalidRequest(reason: $exception->getMessage());
        }
    }
}
