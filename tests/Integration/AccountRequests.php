<?php

declare(strict_types=1);

namespace Test\Integration;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;

final readonly class AccountRequests
{
    private const string BASE_URL = 'https://account.localhost';

    public static function get(string $path, string $accountId): ServerRequestInterface
    {
        $uri = new UriFactory()->createUri(self::BASE_URL . $path);
        $request = new ServerRequestFactory()->createServerRequest('GET', $uri);

        return $request->withAttribute('__route__', new RouteSpy(arguments: ['accountId' => $accountId]));
    }
}
