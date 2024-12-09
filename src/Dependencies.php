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
use Account\Driven\Account\Repository\Adapter as AccountsAdapter;
use Account\Driven\Shared\Database\MySql\MySqlEngine;
use Account\Driven\Shared\Database\RelationalConnection;
use Account\Driven\Shared\Logging\Logger;
use Account\Driven\Shared\Logging\LoggerHandler;
use Account\Driven\Shared\Logging\Obfuscator\Fields\SimpleIdentity;
use Account\Driven\Shared\Logging\Obfuscator\Obfuscators;
use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Query\Account\AccountQuery;
use Account\Query\Account\Database\Facade as AccountQueryFacade;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use PDO;
use TinyBlocks\EnvironmentVariable\EnvironmentVariable;

use function DI\autowire;
use function DI\create;
use function DI\get;

final class Dependencies
{
    public static function definitions(): array
    {
        return [
            Logger::class               => static function () {
                $logger = new MonoLogger(name: 'StreamLogger');
                $formatter = (new LineFormatter(format: '%message%'))->allowInlineLineBreaks();
                $streamHandler = new StreamHandler(stream: 'php://stdout');
                $streamHandler->setFormatter(formatter: $formatter);
                $logger->pushHandler(handler: $streamHandler);
                $obfuscators = Obfuscators::createFrom(elements: [new SimpleIdentity()]);

                return new LoggerHandler(logger: $logger, obfuscators: $obfuscators);
            },
            Accounts::class             => autowire(AccountsAdapter::class),
            Connection::class           => static fn(): Connection => DriverManager::getConnection([
                'driver'        => 'pdo_mysql',
                'host'          => EnvironmentVariable::from(name: 'DATABASE_HOST')->toString(),
                'user'          => EnvironmentVariable::from(name: 'DATABASE_USER')->toString(),
                'port'          => EnvironmentVariable::from(name: 'DATABASE_PORT')->toInteger(),
                'dbname'        => EnvironmentVariable::from(name: 'DATABASE_NAME')->toString(),
                'password'      => EnvironmentVariable::from(name: 'DATABASE_PASSWORD')->toString(),
                'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
            ], new Configuration()),
            OpenAccount::class          => create(OpenAccount::class)->constructor(get(AccountOpeningHandler::class)),
            AccountQuery::class         => autowire(AccountQueryFacade::class),
            AccountOpening::class       => autowire(AccountOpeningHandler::class),
            AccountDebiting::class      => autowire(AccountDebitingHandler::class),
            AccountCrediting::class     => autowire(AccountCreditingHandler::class),
            AccountWithdrawal::class    => autowire(AccountWithdrawalHandler::class),
            CreateTransaction::class    => autowire(CreateTransaction::class),
            RelationalConnection::class => autowire(MySqlEngine::class)
        ];
    }
}
