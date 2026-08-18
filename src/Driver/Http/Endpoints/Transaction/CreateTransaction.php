<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

final readonly class CreateTransaction implements RequestHandlerInterface
{
    public function __construct(private TransactionDispatcher $dispatcher)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload = json_decode($request->getBody()->__toString(), true);
        $command = new Request(payload: $payload)->toCommand();

        $this->dispatcher->dispatch(command: $command);

        return Response::noContent();
    }
}
