<?php

declare(strict_types=1);

namespace Account;

use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Query\Account\FindBalance\Http\FindAccountBalance;
use Account\Query\Account\FindById\Http\FindAccountById;
use Account\Query\Account\FindTransactions\Http\FindAccountTransactions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Interfaces\RouteCollectorProxyInterface;
use TinyBlocks\Http\Code;
use TinyBlocks\Http\ErrorHandler\ErrorMiddleware;
use TinyBlocks\Http\Logging\LogMiddleware;
use TinyBlocks\HttpHealthCheck\LivenessHandler;
use TinyBlocks\HttpHealthCheck\ReadinessHandler;

final readonly class Routes
{
    public function __construct(private App $app)
    {
        $container = $this->app->getContainer();

        $this->app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->app->add($container->get(ErrorMiddleware::class));
        $this->app->add($container->get(LogMiddleware::class));
        $this->app->addBodyParsingMiddleware();
    }

    public function register(): void
    {
        $container = $this->app->getContainer();

        /** @var AppSettings $appSettings */
        $appSettings = $container->get(AppSettings::class);

        $this->app->get('/health/liveness', LivenessHandler::class);
        $this->app->get('/health/readiness', ReadinessHandler::class);

        $this->app->any(
            '/',
            fn(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface => $response
                ->withHeader('Location', $appSettings->source)
                ->withStatus(Code::FOUND->value)
        );

        $this->app->group('/accounts', function (RouteCollectorProxyInterface $accounts): void {
            $accounts->post('', OpenAccount::class);
            $accounts->get('/{accountId}', FindAccountById::class);
            $accounts->get('/{accountId}/balance', FindAccountBalance::class);
            $accounts->get('/{accountId}/transactions', FindAccountTransactions::class);
        });

        $this->app->group('/transactions', function (RouteCollectorProxyInterface $transactions): void {
            $transactions->post('', CreateTransaction::class);
        });
    }
}
