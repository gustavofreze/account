<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Http;

use Account\Query\Account\FindTransactions\AccountTransactionsFinding;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FindAccountTransactions implements RequestHandlerInterface
{
    public function __construct(private AccountTransactionsFinding $transactions)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = FindAccountTransactionsRequest::from(request: $request);

        $template = '/accounts/%s/transactions';
        $baseUri = sprintf($template, $request->accountId);

        return $this->transactions
            ->findAll(keyset: $request->keyset, accountId: $request->accountId, comparisons: $request->comparisons)
            ->toResponse(baseUri: $baseUri);
    }
}
