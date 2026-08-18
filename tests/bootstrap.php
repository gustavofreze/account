<?php

declare(strict_types=1);

use DG\BypassFinals;
use TinyBlocks\DockerContainer\EnvironmentFlag;
use TinyBlocks\DockerContainer\FlywayDockerContainer;
use TinyBlocks\DockerContainer\MySQL\MySQLContainerStarted;
use TinyBlocks\DockerContainer\MySQLDockerContainer;

require_once __DIR__ . '/../vendor/autoload.php';

BypassFinals::enable();

$network = (string)(getenv('TEST_NETWORK') ?: 'account-test_default');

MySQLDockerContainer::from(image: 'mysql:8.4', name: 'account-adm-test')
    ->withNetwork(name: $network)
    ->withDatabase(database: 'account_adm_test')
    ->withRootPassword(rootPassword: 'root')
    ->runWhen(
        gate: EnvironmentFlag::enabled(name: 'RUN_MIGRATIONS'),
        then: static function (MySQLContainerStarted $mySQLStarted) use ($network): void {
            $template = '%s/../database/migrations';
            $migrations = sprintf($template, __DIR__);

            FlywayDockerContainer::from(image: 'flyway/flyway:13.3', name: 'account-flyway-test')
                ->withSource(password: 'root', username: 'root', container: $mySQLStarted)
                ->withNetwork(name: $network)
                ->withMigrations(pathOnHost: $migrations)
                ->withCleanDisabled(disabled: false)
                ->withConnectRetries(retries: 60)
                ->withValidateMigrationNaming(enabled: true)
                ->cleanAndMigrate();
        }
    );
