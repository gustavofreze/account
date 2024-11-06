<?php

declare(strict_types=1);

namespace Account\Driver\Http\Middlewares;

use Account\Driven\Shared\Logging\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\HttpCode;

final readonly class Logging implements MiddlewareInterface
{
    public function __construct(private Logger $logger)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->logInfo(key: 'http_request', context: [
            'uri'     => $request->getUri()->__toString(),
            'method'  => $request->getMethod(),
            'payload' => $request->getParsedBody()
        ]);

        $response = $handler->handle(request: $request);

        $data = json_decode($response->getBody()->__toString(), true);
        $statusCode = $response->getStatusCode();

        if ($statusCode < HttpCode::BAD_REQUEST->value) {
            $this->logger->logInfo(key: 'http_response', context: [
                'status'  => $statusCode,
                'payload' => $data
            ]);

            return $response;
        }

        $this->logger->logError(key: 'http_response', context: [
            'status'  => $statusCode,
            'payload' => $data
        ]);

        return $response;
    }
}
