<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\Account\Database\TransactionFilters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyBlocks\Http\HttpResponse;

final readonly class RetrieveAccountTransactions implements RequestHandlerInterface
{
    public function __construct(private AccountQuery $query)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $filters = TransactionFilters::from(data: $request->getQueryParams());
        $request = new Request(request: $request);
        $accountId = $request->getAccountId();

        $account = $this->query->findById(accountId: $accountId);

        if ($account === null) {
            throw new AccountNotFound(id: $accountId);
        }

        $transactions = $this->query->transactionsOf(accountId: $accountId, filters: $filters);

        return HttpResponse::ok(data: $transactions->all());
    }
}
