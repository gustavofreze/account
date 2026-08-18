<?php

declare(strict_types=1);

namespace Account\Query\Account\FindBalance\Http;

use Account\Query\Account\FindBalance\AccountBalanceFinding;
use Account\Query\Account\Shared\Http\AccountIdentifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

final readonly class FindAccountBalance implements RequestHandlerInterface
{
    public function __construct(private AccountBalanceFinding $balances)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $accountId = AccountIdentifier::from(request: $request)->value;

        $balance = $this->balances->findBalance(accountId: $accountId);

        return Response::ok(body: $balance->toArray());
    }
}
