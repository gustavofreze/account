<?php

declare(strict_types=1);

namespace Account\Query\Shared\Http;

use Account\Query\Shared\Exceptions\AccountNotFound;
use TinyBlocks\Http\Code;
use TinyBlocks\Http\ErrorHandler\ExceptionMapping;
use TinyBlocks\Http\ErrorHandler\ExceptionMappingTable;
use TinyBlocks\Http\ErrorHandler\MappedError;
use TinyBlocks\HttpQuery\Exceptions\HttpQueryException;

final readonly class QueryExceptionMapping implements ExceptionMapping
{
    public function mappings(): ExceptionMappingTable
    {
        return ExceptionMappingTable::create()
            ->when(exceptionClass: AccountNotFound::class)
            ->mapsTo(
                code: 'ACCOUNT_NOT_FOUND',
                status: Code::NOT_FOUND->value,
                message: 'Account not found.'
            )
            ->whenSubclassOf(baseException: HttpQueryException::class)
            ->resolvesWith(resolver: static fn(HttpQueryException $error): MappedError => new MappedError(
                code: 'INVALID_REQUEST',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: $error->getMessage()
            ))
            ->when(exceptionClass: InvalidRequest::class)
            ->resolvesWith(resolver: static fn(InvalidRequest $error): MappedError => new MappedError(
                code: 'INVALID_REQUEST',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: $error->reason
            ));
    }
}
