<?php

declare(strict_types=1);

namespace Test\Integration;

use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteInterface;

final class RouteSpy implements RouteInterface
{
    public function __construct(private array $arguments = [])
    {
    }

    public function getIdentifier(): string
    {
        return 'route_spy';
    }

    public function getArgument(string $name, ?string $default = null): ?string
    {
        return ($this->arguments[$name] ?? $default);
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

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        throw new BadMethodCallException('Route invocation strategy is never read.');
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): RouteInterface
    {
        throw new BadMethodCallException('Route invocation strategy is never set.');
    }

    public function getMethods(): array
    {
        throw new BadMethodCallException('Route methods are never read.');
    }

    public function getPattern(): string
    {
        throw new BadMethodCallException('Route pattern is never read.');
    }

    public function setPattern(string $pattern): RouteInterface
    {
        throw new BadMethodCallException('Route pattern is never set.');
    }

    public function getCallable(): callable|string
    {
        throw new BadMethodCallException('Route callable is never read.');
    }

    public function setCallable(mixed $callable): RouteInterface
    {
        throw new BadMethodCallException('Route callable is never set.');
    }

    public function getName(): ?string
    {
        throw new BadMethodCallException('Route name is never read.');
    }

    public function setName(string $name): RouteInterface
    {
        throw new BadMethodCallException('Route name is never set.');
    }

    public function add(mixed $middleware): RouteInterface
    {
        throw new BadMethodCallException('Route middleware is never added.');
    }

    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface
    {
        throw new BadMethodCallException('Route middleware is never added.');
    }

    public function prepare(array $arguments): RouteInterface
    {
        throw new BadMethodCallException('Route is never prepared.');
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        throw new BadMethodCallException('Route is never run.');
    }
}
