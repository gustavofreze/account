<?php

declare(strict_types=1);

namespace Test\Integration;

use Account\Dependencies;
use DI\Container;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class IntegrationTestCase extends TestCase
{
    private static Connection $connection;
    private static ContainerInterface $container;

    public static function setUpBeforeClass(): void
    {
        self::$container = new Container(Dependencies::definitions());
        self::$connection = self::$container->get(Connection::class);
    }

    protected function tearDown(): void
    {
        self::$connection->executeStatement('DELETE FROM transactions');
        self::$connection->executeStatement('DELETE FROM accounts');
    }

    public function get(string $class): mixed
    {
        return self::$container->get($class);
    }
}
