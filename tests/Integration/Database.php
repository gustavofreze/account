<?php

declare(strict_types=1);

namespace Test\Integration;

use TinyBlocks\DockerContainer\GenericDockerContainer;
use TinyBlocks\DockerContainer\MySQLDockerContainer;
use TinyBlocks\DockerContainer\Waits\Conditions\MySQL\MySQLReady;
use TinyBlocks\DockerContainer\Waits\ContainerWaitForDependency;
use TinyBlocks\DockerContainer\Waits\ContainerWaitForTime;
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

final readonly class Database
{
    private function __construct(
        private string $host,
        private string $network,
        private string $database,
        private string $username,
        private string $password,
        private string $migrations
    ) {
    }

    public static function instance(): Database
    {
        $host = EnvironmentVariable::from(name: 'DATABASE_HOST')->toString();
        $network = 'account-test_default';
        $database = EnvironmentVariable::from(name: 'DATABASE_NAME')->toString();
        $username = EnvironmentVariable::from(name: 'DATABASE_USER')->toString();
        $password = EnvironmentVariable::from(name: 'DATABASE_PASSWORD')->toString();
        $migrations = '/account-adm-migrations';

        return new Database(
            host: $host,
            network: $network,
            database: $database,
            username: $username,
            password: $password,
            migrations: $migrations
        );
    }

    public function start(): void
    {
        $environmentVariable = EnvironmentVariable::fromOrDefault(name: 'RUN_MIGRATIONS', defaultValueIfNotFound: '0');
        $shouldRunMigrations = $environmentVariable->toBoolean();

        if ($shouldRunMigrations === false) {
            return;
        }

        $mySQLContainer = MySQLDockerContainer::from(image: 'mysql:8.1', name: $this->host)
            ->withNetwork(name: $this->network)
            ->withTimezone(timezone: 'America/Sao_Paulo')
            ->withPassword(password: $this->password)
            ->withDatabase(database: $this->database)
            ->withRootPassword(rootPassword: $this->password)
            ->withGrantedHosts()
            ->withVolumeMapping(pathOnHost: '/var/lib/mysql', pathOnContainer: '/var/lib/mysql')
            ->runIfNotExists();

        $jdbcUrl = $mySQLContainer->getJdbcUrl();

        GenericDockerContainer::from(image: 'flyway/flyway:11.0.1')
            ->withNetwork(name: $this->network)
            ->copyToContainer(pathOnHost: $this->migrations, pathOnContainer: '/flyway/sql')
            ->withVolumeMapping(pathOnHost: $this->migrations, pathOnContainer: '/flyway/sql')
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
