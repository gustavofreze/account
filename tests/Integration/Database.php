<?php

declare(strict_types=1);

namespace Test\Integration;

use Account\Environment;
use TinyBlocks\DockerContainer\GenericDockerContainer;
use TinyBlocks\DockerContainer\MySQLDockerContainer;
use TinyBlocks\DockerContainer\Waits\Conditions\MySQL\MySQLReady;
use TinyBlocks\DockerContainer\Waits\ContainerWaitForDependency;
use TinyBlocks\DockerContainer\Waits\ContainerWaitForTime;

final readonly class Database
{
    private string $host;

    private string $database;

    private string $username;

    private string $password;

    public function __construct()
    {
        $this->host = Environment::get(variable: 'DATABASE_HOST')->toString();
        $this->database = Environment::get(variable: 'DATABASE_NAME')->toString();
        $this->username = Environment::get(variable: 'DATABASE_USER')->toString();
        $this->password = Environment::get(variable: 'DATABASE_PASSWORD')->toString();
    }

    public function start(): void
    {
        $mySQLContainer = MySQLDockerContainer::from(image: 'mysql:8.1', name: $this->host)
            ->withNetwork(name: 'account_default')
            ->withTimezone(timezone: 'America/Sao_Paulo')
            ->withUsername(user: $this->username)
            ->withPassword(password: $this->password)
            ->withDatabase(database: $this->database)
            ->withRootPassword(rootPassword: $this->password)
            ->withGrantedHosts()
            ->withVolumeMapping(pathOnHost: '/var/lib/mysql', pathOnContainer: '/var/lib/mysql')
            ->runIfNotExists();

        $jdbcUrl = $mySQLContainer->getJdbcUrl();

        GenericDockerContainer::from(image: 'flyway/flyway:11.0.0')
            ->withNetwork(name: 'account_default')
            ->copyToContainer(pathOnHost: '/account-adm-migrations', pathOnContainer: '/flyway/sql')
            ->withVolumeMapping(pathOnHost: '/account-adm-migrations', pathOnContainer: '/flyway/sql')
            ->withWaitBeforeRun(
                wait: ContainerWaitForDependency::untilReady(
                    condition: MySQLReady::from(
                        container: $mySQLContainer
                    )
                )
            )
            ->withEnvironmentVariable(key: 'FLYWAY_URL', value: $jdbcUrl)
            ->withEnvironmentVariable(key: 'FLYWAY_USER', value: $this->username)
            ->withEnvironmentVariable(key: 'FLYWAY_TABLE', value: 'schema_history')
            ->withEnvironmentVariable(key: 'FLYWAY_SCHEMAS', value: $this->database)
            ->withEnvironmentVariable(key: 'FLYWAY_EDITION', value: 'community')
            ->withEnvironmentVariable(key: 'FLYWAY_PASSWORD', value: $this->password)
            ->withEnvironmentVariable(key: 'FLYWAY_LOCATIONS', value: 'filesystem:/flyway/sql')
            ->withEnvironmentVariable(key: 'FLYWAY_CLEAN_DISABLED', value: 'false')
            ->withEnvironmentVariable(key: 'FLYWAY_VALIDATE_MIGRATION_NAMING', value: 'true')
            ->run(
                commands: ['-connectRetries=15', 'clean', 'migrate'],
                waitAfterStarted: ContainerWaitForTime::forSeconds(seconds: 5)
            );
    }
}
