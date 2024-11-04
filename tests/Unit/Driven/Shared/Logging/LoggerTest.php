<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging;

use Account\Driven\Shared\Logging\Obfuscator\Fields\SimpleIdentity;
use Account\Driven\Shared\Logging\Obfuscator\Obfuscators;
use Account\LoggerMock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private LoggerMock $logger;

    private Logger $loggerHandler;

    private const string LOG_INFO_PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z) component=[^ ]+ type=INFO key=%s data={.*}/';
    private const string LOG_ERROR_PATTERN = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z) component=[^ ]+ type=ERROR key=%s data={.*}/';

    protected function setUp(): void
    {
        $this->logger = new LoggerMock();
        $obfuscators = Obfuscators::createFrom(elements: [new SimpleIdentity()]);
        $this->loggerHandler = new LoggerHandler(logger: $this->logger, obfuscators: $obfuscators);
    }

    #[DataProvider('logInfoProvider')]
    public function testLogInfoOutputsFormattedMessage(string $key, array $context, string $expected): void
    {
        /** @When logInfo is called */
        $this->loggerHandler->logInfo(key: $key, context: $context);

        /** @Then the output should match the expected format */
        $pattern = sprintf(self::LOG_INFO_PATTERN, preg_quote($key), $expected);

        self::assertMatchesRegularExpression($pattern, trim($this->logger->firstInfoOutputOrAt()));
    }

    #[DataProvider('logErrorProvider')]
    public function testLogErrorOutputsFormattedMessage(string $key, array $context, string $expected): void
    {
        /** @When logError is called */
        $this->loggerHandler->logError(key: $key, context: $context);

        /** @Then the output should match the expected format */
        $pattern = sprintf(self::LOG_ERROR_PATTERN, preg_quote($key), preg_quote($expected));

        self::assertMatchesRegularExpression($pattern, trim($this->logger->firstErrorOutputOrAt()));
    }

    public static function logInfoProvider(): array
    {
        return [
            'Info log with withdrawal action' => [
                'key'      => 'any_key',
                'context'  => ['user' => 'Gustavo', 'action' => 'withdraw'],
                'expected' => '{"user":"Gustavo","action":"withdraw"}'
            ],
            'HTTP request log'                => [
                'key'      => 'http_request',
                'context'  => ['uri' => 'http://account.localhost/transactions', 'method' => 'POST'],
                'expected' => '{"uri":"http://account.localhost/transactions","method":"POST"}'
            ]
        ];
    }

    public static function logErrorProvider(): array
    {
        return [
            'Error log with withdrawal action' => [
                'key'      => 'any_key',
                'context'  => ['user' => 'Gustavo', 'action' => 'withdraw'],
                'expected' => '{"user":"Gustavo","action":"withdraw"}'
            ],
            'HTTP response error log'          => [
                'key'      => 'http_response',
                'context'  => ['status' => 404, 'payload' => ['error' => 'Not Found']],
                'expected' => '{"status":404,"payload":{"error":"Not Found"}}'
            ]
        ];
    }
}
