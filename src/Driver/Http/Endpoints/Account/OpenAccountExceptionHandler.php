<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Account;

use Account\Application\Domain\Exceptions\AccountAlreadyExists;
use Account\Driver\Http\Endpoints\ExceptionHandler;
use Account\Driver\Http\Endpoints\InvalidRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TinyBlocks\Http\Response;

final readonly class OpenAccountExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception): ResponseInterface
    {
        $error = ['error' => $exception->getMessage()];

        return match (get_class($exception)) {
            InvalidRequest::class,       => Response::unprocessableEntity(body: ['error' => $exception->getMessages()]),
            AccountAlreadyExists::class, => Response::conflict(body: $error),
            default                      => Response::internalServerError(body: $error)
        };
    }
}
