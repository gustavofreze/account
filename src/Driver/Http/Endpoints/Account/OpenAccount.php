<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Application\Ports\Inbound\AccountOpening;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\HttpResponse;

final readonly class OpenAccount implements RequestHandlerInterface
{
    public function __construct(private AccountOpening $useCase)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload = json_decode($request->getBody()->__toString(), true);
        $request = new Request(payload: $payload);
        $command = $request->toCommand();

        $this->useCase->handle(command: $command);

        return HttpResponse::created(data: ['id' => $command->id->toString()]);
    }
}
