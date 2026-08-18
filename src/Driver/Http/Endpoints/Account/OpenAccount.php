<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Application\Ports\Inbound\AccountOpening;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

final readonly class OpenAccount implements RequestHandlerInterface
{
    public function __construct(private AccountOpening $accountOpening)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload = json_decode($request->getBody()->__toString(), true);
        $command = new Request(payload: $payload)->toCommand();

        $this->accountOpening->handle(command: $command);

        return Response::created(body: ['id' => $command->id->identityValue()]);
    }
}
