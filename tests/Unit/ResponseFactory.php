<?php

declare(strict_types=1);

namespace Account;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

final class ResponseFactory
{
    private string $body;

    private int $statusCode;

    public function __construct(int $statusCode, array $data)
    {
        $this->body = json_encode($data);
        $this->statusCode = $statusCode;
    }

    public function build(): ResponseInterface
    {
        /** @var resource $stream */
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->body);
        rewind($stream);

        $response = new Response($this->statusCode);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}
