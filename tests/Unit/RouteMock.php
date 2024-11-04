<?php

declare(strict_types=1);

namespace Account;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteInterface;

final class RouteMock implements RouteInterface
{
    private array $arguments;

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        // TODO Mocked method
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): RouteInterface
    {
        // TODO Mocked method
    }

    public function getMethods(): array
    {
        // TODO Mocked method
    }

    public function getPattern(): string
    {
        // TODO Mocked method
    }

    public function setPattern(string $pattern): RouteInterface
    {
        // TODO Mocked method
    }

    public function getCallable()
    {
        // TODO Mocked method
    }

    public function setCallable($callable): RouteInterface
    {
        // TODO Mocked method
    }

    public function getName(): ?string
    {
        // TODO Mocked method
    }

    public function setName(string $name): RouteInterface
    {
        // TODO Mocked method
    }

    public function getIdentifier(): string
    {
        return 'route_mock';
    }

    public function getArgument(string $name, ?string $default = null): ?string
    {
        return $this->arguments[$name] ?? $default;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArgument(string $name, string $value): RouteInterface
    {
        $this->arguments[$name] = $value;
        return $this;
    }

    public function setArguments(array $arguments): RouteInterface
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function add($middleware): RouteInterface
    {
        // TODO Mocked method
    }

    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface
    {
        // TODO Mocked method
    }

    public function prepare(array $arguments): RouteInterface
    {
        // TODO Mocked method
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        // TODO Mocked method
    }
}
