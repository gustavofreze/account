<?php

declare(strict_types=1);

namespace Account;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Stream;

final class RequestFactory
{
    private const string LOCALHOST = 'account.localhost';

    public static function getFrom(string $path, array $parameters): ServerRequestInterface
    {
        $uri = (new UriFactory())
            ->createUri()
            ->withScheme('https')
            ->withHost(self::LOCALHOST)
            ->withPath($path);

        $serverRequestFactory = new ServerRequestFactory();
        $request = $serverRequestFactory->createServerRequest('GET', $uri);

        return $request->withAttribute('__route__', new RouteMock(arguments: $parameters));
    }

    public static function postFrom(array $payload): ServerRequestInterface
    {
        $uri = (new UriFactory())
            ->createUri()
            ->withScheme('https')
            ->withHost(self::LOCALHOST)
            ->withPath('/');

        /** @var resource $stream */
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, json_encode($payload));
        rewind($stream);

        $body = new Stream($stream);
        $headers = new Headers(['Content-Type' => 'application/json']);

        return new SlimRequest(
            method: 'POST',
            uri: $uri,
            headers: $headers,
            cookies: [],
            serverParams: [],
            body: $body
        );
    }
}
