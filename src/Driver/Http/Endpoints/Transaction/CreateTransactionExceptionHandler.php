<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Domain\Exceptions\AccountNotFound;
use Account\Driver\Http\Endpoints\ExceptionHandler;
use Account\Driver\Http\Endpoints\InvalidRequest;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TinyBlocks\Http\Response;

final readonly class CreateTransactionExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception): ResponseInterface
    {
        $error = ['error' => $exception->getMessage()];

        return match (get_class($exception)) {
            InvalidRequest::class,          => Response::unprocessableEntity(body: [
                'error' => $exception->getMessages()
            ]),
            AccountNotFound::class          => Response::notFound(body: $error),
            InvalidArgumentException::class => Response::unprocessableEntity(body: $error),
            default                         => Response::internalServerError(body: $error)
        };
    }
}
