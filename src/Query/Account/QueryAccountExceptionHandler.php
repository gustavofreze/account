<?php

declare(strict_types=1);

namespace Account\Query\Account;

use Account\Query\ExceptionHandler;
use Account\Query\InvalidRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TinyBlocks\Http\Response;

final readonly class QueryAccountExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception): ResponseInterface
    {
        $error = ['error' => $exception->getMessage()];

        return match (get_class($exception)) {
            InvalidRequest::class,  => Response::unprocessableEntity(body: ['error' => $exception->getMessages()]),
            AccountNotFound::class, => Response::notFound(body: $error),
            default                 => Response::internalServerError(body: $error)
        };
    }
}
