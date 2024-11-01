<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Driver\Http\Endpoints\ExceptionHandler;
use Account\Driver\Http\Endpoints\InvalidRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TinyBlocks\Http\HttpResponse;

final readonly class CreateTransactionExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception): ResponseInterface
    {
        $error = ['error' => $exception->getMessage()];

        return match (get_class($exception)) {
            InvalidRequest::class, => HttpResponse::unprocessableEntity(data: [
                'error' => $exception->getErrors()
            ]),
            AccountNotFound::class => HttpResponse::notFound(data: $error),
            default                => HttpResponse::internalServerError(data: $error)
        };
    }
}
