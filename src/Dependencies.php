<?php

declare(strict_types=1);

namespace Account;

use Account\Application\Handlers\AccountCreditingHandler;
use Account\Application\Handlers\AccountDebitingHandler;
use Account\Application\Handlers\AccountOpeningHandler;
use Account\Application\Handlers\AccountWithdrawalHandler;
use Account\Application\Ports\Inbound\AccountCrediting;
use Account\Application\Ports\Inbound\AccountDebiting;
use Account\Application\Ports\Inbound\AccountOpening;
use Account\Application\Ports\Inbound\AccountWithdrawal;
use Account\Application\Ports\Outbound\Accounts;
use Account\Driven\Account\Outbox\AccountEventPayloadSerializer;
use Account\Driven\Account\Outbox\AccountEventTranslator;
use Account\Driven\Account\Outbox\Event\AccountOpened;
use Account\Driven\Account\Repository\AccountRepository;
use Account\Driven\Shared\Database\MySql\MySqlEngine;
use Account\Driven\Shared\Database\RelationalConnection;
use Account\Driver\Http\DriverExceptionMapping;
use Account\Query\Account\FindBalance\AccountBalanceFinding;
use Account\Query\Account\FindBalance\Database\AccountBalanceFindingAdapter;
use Account\Query\Account\FindById\AccountFinding;
use Account\Query\Account\FindById\Database\AccountFindingAdapter;
use Account\Query\Account\FindTransactions\AccountTransactionsFinding;
use Account\Query\Account\FindTransactions\Database\AccountTransactionsFindingAdapter;
use Account\Query\Shared\Http\QueryExceptionMapping;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Pdo\Mysql;
use Psr\Container\ContainerInterface;
use TinyBlocks\BuildingBlocks\Event\IntegrationEventTranslators;
use TinyBlocks\Http\ErrorHandler\ErrorHandlingSettings;
use TinyBlocks\Http\ErrorHandler\ErrorMiddleware;
use TinyBlocks\Http\Logging\LogMiddleware;
use TinyBlocks\HttpHealthCheck\DoctrineHealthCheck;
use TinyBlocks\HttpHealthCheck\DrainMarker;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\LivenessHandler;
use TinyBlocks\HttpHealthCheck\ReadinessHandler;
use TinyBlocks\Logger\Logger;
use TinyBlocks\Logger\Redactions\DocumentRedaction;
use TinyBlocks\Logger\Redactions\SecretRedaction;
use TinyBlocks\Logger\StreamLogger;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Structured;
use TinyBlocks\Outbox\DoctrineOutboxRepository;
use TinyBlocks\Outbox\OutboxRepository;
use TinyBlocks\Outbox\Serialization\PayloadSerializers;

use function DI\autowire;

final readonly class Dependencies
{
    private const int DOCUMENT_VISIBLE_SUFFIX_LENGTH = 5;

    public static function definitions(): array
    {
        return [
            ...self::query(),
            ...self::driven(),
            ...self::driver(),
            ...self::shared(),
            ...self::application()
        ];
    }

    private static function query(): array
    {
        return [
            AccountFinding::class             => autowire(AccountFindingAdapter::class),
            AccountBalanceFinding::class     => autowire(AccountBalanceFindingAdapter::class),
            AccountTransactionsFinding::class => autowire(AccountTransactionsFindingAdapter::class)
        ];
    }

    private static function driven(): array
    {
        return [
            Connection::class           => static function (ContainerInterface $container): Connection {
                /** @var DatabaseSettings $settings */
                $settings = $container->get(DatabaseSettings::class);

                return DriverManager::getConnection([
                    'driver'        => 'pdo_mysql',
                    'host'          => $settings->host,
                    'user'          => $settings->user,
                    'port'          => $settings->port,
                    'dbname'        => $settings->name,
                    'charset'       => 'utf8mb4',
                    'password'      => $settings->password,
                    'driverOptions' => [
                        Mysql::ATTR_INIT_COMMAND     => 'SET time_zone = "-03:00"',
                        Mysql::ATTR_EMULATE_PREPARES => false
                    ]
                ], new Configuration());
            },
            Accounts::class             => autowire(AccountRepository::class),
            OutboxRepository::class     => static function (ContainerInterface $container): OutboxRepository {
                $mapper = Mapper::create()
                    ->withNaming(namingStrategy: SnakeCase::create())
                    ->withMapping(type: AccountOpened::class, mapping: Structured::create());

                return new DoctrineOutboxRepository(
                    connection: $container->get(Connection::class),
                    serializers: PayloadSerializers::createFrom(
                        elements: [new AccountEventPayloadSerializer(mapper: $mapper)]
                    ),
                    translators: IntegrationEventTranslators::createFrom(
                        elements: [new AccountEventTranslator()]
                    )
                );
            },
            RelationalConnection::class => autowire(MySqlEngine::class)
        ];
    }

    private static function driver(): array
    {
        return [
            LogMiddleware::class    => static function (ContainerInterface $container): LogMiddleware {
                return LogMiddleware::create()
                    ->withLogger(logger: $container->get(Logger::class))
                    ->build();
            },
            ErrorMiddleware::class  => static function (ContainerInterface $container): ErrorMiddleware {
                /** @var AppSettings $appSettings */
                $appSettings = $container->get(AppSettings::class);

                return ErrorMiddleware::create()
                    ->withLogger(logger: $container->get(Logger::class))
                    ->withMappings(new DriverExceptionMapping(), new QueryExceptionMapping())
                    ->withSettings(
                        settings: ErrorHandlingSettings::from(
                            logErrors: true,
                            logErrorDetails: true,
                            displayErrorDetails: $appSettings->debug
                        )
                    )
                    ->build();
            },
            LivenessHandler::class  => static fn(): LivenessHandler => LivenessHandler::create(),
            ReadinessHandler::class => static function (ContainerInterface $container): ReadinessHandler {
                $checks = HealthChecks::createFromEmpty()
                    ->withCritical(check: DoctrineHealthCheck::from(connection: $container->get(Connection::class)));

                return ReadinessHandler::from(checks: $checks, drainMarker: DrainMarker::default());
            }
        ];
    }

    private static function shared(): array
    {
        return [
            Logger::class           => static function (ContainerInterface $container): Logger {
                /** @var AppSettings $appSettings */
                $appSettings = $container->get(AppSettings::class);

                return StreamLogger::builder()
                    ->withComponent(component: $appSettings->appName)
                    ->withRedactions(
                        SecretRedaction::default(),
                        DocumentRedaction::from(
                            fields: ['document'],
                            visibleSuffixLength: self::DOCUMENT_VISIBLE_SUFFIX_LENGTH
                        )
                    )
                    ->build();
            },
            AppSettings::class      => static fn(): AppSettings => AppSettings::fromEnvironment(),
            DatabaseSettings::class => static fn(): DatabaseSettings => DatabaseSettings::fromEnvironment()
        ];
    }

    private static function application(): array
    {
        return [
            AccountOpening::class    => autowire(AccountOpeningHandler::class),
            AccountDebiting::class   => autowire(AccountDebitingHandler::class),
            AccountCrediting::class  => autowire(AccountCreditingHandler::class),
            AccountWithdrawal::class => autowire(AccountWithdrawalHandler::class)
        ];
    }
}
