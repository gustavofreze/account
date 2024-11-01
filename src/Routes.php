<?php

namespace Account;

use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Driver\Http\Endpoints\Account\OpenAccountExceptionHandler;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Driver\Http\Endpoints\Transaction\CreateTransactionExceptionHandler;
use Account\Driver\Http\Middlewares\ErrorHandling;
use Account\Query\Account\FindAccountById;
use Account\Query\Account\QueryAccountExceptionHandler;
use Account\Query\QueryErrorHandling;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Interfaces\RouteCollectorProxyInterface;

final readonly class Routes
{
    /**
     * @param App<ContainerInterface> $app
     */
    public function __construct(private App $app)
    {
        $routeCollector = $this->app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->app->addErrorMiddleware(true, true, true);
        $this->app->addBodyParsingMiddleware();
    }

    public function register(): void
    {
        $this->app->group('/accounts', function (RouteCollectorProxyInterface $route) {
            $route->get('/{accountId}', FindAccountById::class)->addMiddleware(
                new QueryErrorHandling(exceptionHandler: new QueryAccountExceptionHandler())
            );
            $route->post('', OpenAccount::class)->addMiddleware(
                new ErrorHandling(exceptionHandler: new OpenAccountExceptionHandler())
            );
        });

        $this->app->group('/transactions', function (RouteCollectorProxyInterface $route) {
            $route->post('', CreateTransaction::class)->addMiddleware(
                new ErrorHandling(exceptionHandler: new CreateTransactionExceptionHandler())
            );
        });
    }
}
