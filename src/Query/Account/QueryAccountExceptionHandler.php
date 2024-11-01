<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\ExceptionHandler;
use Account\Query\InvalidRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TinyBlocks\Http\HttpResponse;

final readonly class QueryAccountExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception): ResponseInterface
    {
        $error = ['error' => $exception->getMessage()];

        return match (get_class($exception)) {
            InvalidRequest::class,  => HttpResponse::unprocessableEntity(data: [
                'error' => $exception->getErrors()
            ]),
            AccountNotFound::class, => HttpResponse::notFound(data: $error),
            default                 => HttpResponse::internalServerError(data: $error)
        };
    }
}
