<?php

declare(strict_types=1);

namespace Account\Driver\Http;

use Account\Application\Domain\Exceptions\AccountWithInsufficientFunds;
use Account\Application\Domain\Exceptions\DocumentFormatNotValid;
use Account\Application\Domain\Exceptions\InvalidAmount;
use Account\Application\Domain\Exceptions\UnsupportedOperationType;
use Account\Application\Exceptions\AccountAlreadyExists;
use Account\Application\Exceptions\AccountNotFound;
use TinyBlocks\Http\Code;
use TinyBlocks\Http\ErrorHandler\ExceptionMapping;
use TinyBlocks\Http\ErrorHandler\ExceptionMappingTable;
use TinyBlocks\Http\ErrorHandler\MappedError;

final readonly class DriverExceptionMapping implements ExceptionMapping
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
            ->when(exceptionClass: AccountAlreadyExists::class)
            ->mapsTo(
                code: 'ACCOUNT_ALREADY_EXISTS',
                status: Code::CONFLICT->value,
                message: 'An account already exists for this holder document number.'
            )
            ->when(exceptionClass: AccountWithInsufficientFunds::class)
            ->mapsTo(
                code: 'ACCOUNT_WITH_INSUFFICIENT_FUNDS',
                status: Code::CONFLICT->value,
                message: 'Account has insufficient funds for this transaction.'
            )
            ->when(exceptionClass: UnsupportedOperationType::class)
            ->mapsTo(
                code: 'UNSUPPORTED_OPERATION_TYPE',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: 'The operation type is not supported.'
            )
            ->when(exceptionClass: DocumentFormatNotValid::class)
            ->mapsTo(
                code: 'DOCUMENT_FORMAT_NOT_VALID',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: 'The document number must contain only digits, with a length between 11 and 50.'
            )
            ->when(exceptionClass: InvalidAmount::class)
            ->mapsTo(
                code: 'INVALID_AMOUNT',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: 'The amount must be positive or zero.'
            )
            ->when(exceptionClass: InvalidRequest::class)
            ->resolvesWith(resolver: static fn(InvalidRequest $error): MappedError => new MappedError(
                code: 'INVALID_REQUEST',
                status: Code::UNPROCESSABLE_ENTITY->value,
                message: implode(', ', $error->messages)
            ));
    }
}
