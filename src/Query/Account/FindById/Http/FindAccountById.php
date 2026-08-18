<?php

declare(strict_types=1);

namespace Account\Query\Account\FindById\Http;

use Account\Query\Account\FindById\AccountFinding;
use Account\Query\Account\Shared\Http\AccountIdentifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\Server\Response;

final readonly class FindAccountById implements RequestHandlerInterface
{
    public function __construct(private AccountFinding $accounts)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $accountId = AccountIdentifier::from(request: $request)->value;

        $account = $this->accounts->findById(accountId: $accountId);

        return Response::ok(body: $account->toArray());
    }
}
