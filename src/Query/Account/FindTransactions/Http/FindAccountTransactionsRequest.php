<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\Http;

use Account\Query\Account\Shared\Http\AccountIdentifier;
use Psr\Http\Message\ServerRequestInterface;
use TinyBlocks\HttpQuery\Cursor\Criteria;
use TinyBlocks\HttpQuery\Cursor\Keyset;
use TinyBlocks\HttpQuery\Operator;
use TinyBlocks\HttpQuery\Schema;
use TinyBlocks\HttpQuery\Sort;
use TinyBlocks\HttpQuery\ValueKind;

final readonly class FindAccountTransactionsRequest
{
    private function __construct(public Keyset $keyset, public string $accountId, public array $comparisons)
    {
    }

    public static function from(ServerRequestInterface $request): FindAccountTransactionsRequest
    {
        $schema = Schema::create()
            ->sortable(fields: ['created_at', 'id'])
            ->filterable(
                field: 'operation_type_id',
                operators: [Operator::EQUAL, Operator::IN],
                valueKind: ValueKind::INTEGER
            )
            ->defaultSort(sort: Sort::fromExpression(expression: '-created_at,-id'));

        $criteria = Criteria::fromQuery(schema: $schema, request: $request);
        $accountId = AccountIdentifier::from(request: $request)->value;

        return new FindAccountTransactionsRequest(
            keyset: $criteria->keyset(),
            accountId: $accountId,
            comparisons: $criteria->comparisons()
        );
    }
}
