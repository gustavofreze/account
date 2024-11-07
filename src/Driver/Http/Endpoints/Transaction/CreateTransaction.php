<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Ports\Inbound\AccountCrediting;
use Account\Application\Ports\Inbound\AccountDebiting;
use Account\Application\Ports\Inbound\AccountWithdrawal;
use Account\Driver\Http\Endpoints\Transaction\Factories\UseCaseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\HttpResponse;

final readonly class CreateTransaction implements RequestHandlerInterface
{
    public function __construct(
        private AccountDebiting $accountDebiting,
        private AccountCrediting $accountCrediting,
        private AccountWithdrawal $accountWithdrawal
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload = json_decode($request->getBody()->__toString(), true);
        $request = new Request(payload: $payload);
        $command = $request->toCommand();

        $useCase = new UseCaseFactory(
            accountDebiting: $this->accountDebiting,
            accountCrediting: $this->accountCrediting,
            accountWithdrawal: $this->accountWithdrawal
        );
        $useCase->handle(command: $command);

        return HttpResponse::noContent();
    }
}
