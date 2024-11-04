<?php

declare(strict_types=1);

namespace Account;

use Account\Driven\Shared\Logging\Logger;
use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Driver\Http\Endpoints\Account\OpenAccountExceptionHandler;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Driver\Http\Endpoints\Transaction\CreateTransactionExceptionHandler;
use Account\Driver\Http\Middlewares\ErrorHandling;
use Account\Driver\Http\Middlewares\Logging;
use Account\Query\Account\FindAccountById;
use Account\Query\Account\QueryAccountExceptionHandler;
use Account\Query\QueryErrorHandling;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Interfaces\RouteCollectorProxyInterface;
use TinyBlocks\Http\HttpCode;

final class Routes
{
    private Logging $logging;

    /**
     * @param App<ContainerInterface> $app
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(private readonly App $app)
    {
        $routeCollector = $this->app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->app->addErrorMiddleware(true, true, true);
        $this->app->addBodyParsingMiddleware();

        /** @var Logger $logger */
        $logger = $this->app->getContainer()->get(Logger::class);
        $this->logging = new Logging(logger: $logger);
    }

    public function register(): void
    {
        $this->app->any('/', fn($request, $response) => $response
            ->withHeader('Location', Environment::get(variable: 'SOURCE')->toString())
            ->withStatus(HttpCode::FOUND->value));

        $this->app->group('/accounts', function (RouteCollectorProxyInterface $route) {
            $errorHandling = new ErrorHandling(exceptionHandler: new OpenAccountExceptionHandler());
            $queryErrorHandling = new QueryErrorHandling(exceptionHandler: new QueryAccountExceptionHandler());

            $route->get('/{accountId}', FindAccountById::class)
                ->addMiddleware($queryErrorHandling);

            $route->post('', OpenAccount::class)
                ->addMiddleware($errorHandling)
                ->addMiddleware($this->logging);
        });

        $this->app->group('/transactions', function (RouteCollectorProxyInterface $route) {
            $errorHandling = new ErrorHandling(exceptionHandler: new CreateTransactionExceptionHandler());

            $route->post('', CreateTransaction::class)
                ->addMiddleware($errorHandling)
                ->addMiddleware($this->logging);
        });
    }
}
