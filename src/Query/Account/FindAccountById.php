<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\HttpResponse;

final readonly class FindAccountById implements RequestHandlerInterface
{
    public function __construct(private AccountQuery $query)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = new Request(request: $request);
        $accountId = $request->getAccountId();

        $account = $this->query->findAccountById(id: $accountId);

        if ($account === null) {
            throw new AccountNotFound(id: $accountId);
        }

        return HttpResponse::ok(data: $account->toArray());
    }
}
