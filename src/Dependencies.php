<?php

declare(strict_types=1);

namespace Account;

use Account\Application\Domain\Handlers\AccountCreditingHandler;
use Account\Application\Domain\Handlers\AccountDebitingHandler;
use Account\Application\Domain\Handlers\AccountOpeningHandler;
use Account\Application\Domain\Handlers\AccountWithdrawalHandler;
use Account\Application\Domain\Ports\Inbound\AccountCrediting;
use Account\Application\Domain\Ports\Inbound\AccountDebiting;
use Account\Application\Domain\Ports\Inbound\AccountOpening;
use Account\Application\Domain\Ports\Inbound\AccountWithdrawal;
use Account\Application\Domain\Ports\Outbound\Accounts;
use Account\Driven\Account\Repository\Adapter as AccountsAdapter;
use Account\Driven\Shared\Database\MySql\MySqlEngine;
use Account\Driven\Shared\Database\RelationalConnection;
use Account\Driver\Http\Endpoints\Account\OpenAccount;
use Account\Driver\Http\Endpoints\Transaction\CreateTransaction;
use Account\Query\Account\AccountQuery;
use Account\Query\Account\Database\Facade as AccountQueryFacade;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;

use function DI\autowire;
use function DI\create;
use function DI\get;

final class Dependencies
{
    public static function definitions(): array
    {
        return [
            Accounts::class             => autowire(AccountsAdapter::class),
            OpenAccount::class          => create(OpenAccount::class)->constructor(get(AccountOpeningHandler::class)),
            Connection::class           => fn(): Connection => DriverManager::getConnection([
                'driver'        => 'pdo_mysql',
                'host'          => Environment::get(variable: 'DATABASE_HOST')->toString(),
                'user'          => Environment::get(variable: 'DATABASE_USER')->toString(),
                'port'          => Environment::get(variable: 'DATABASE_PORT')->toInt(),
                'dbname'        => Environment::get(variable: 'DATABASE_NAME')->toString(),
                'password'      => Environment::get(variable: 'DATABASE_PASSWORD')->toString(),
                'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
            ], new Configuration()),
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
