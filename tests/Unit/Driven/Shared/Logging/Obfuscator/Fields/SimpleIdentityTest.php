<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SimpleIdentityTest extends TestCase
{
    private SimpleIdentity $simpleIdentity;

    protected function setUp(): void
    {
        $this->simpleIdentity = new SimpleIdentity();
    }

    #[DataProvider('sensitiveDataProvider')]
    public function testObfuscate(array $context, string $expected): void
    {
        /** @Given a context with a document */
        /** @When obfuscate is called */
        $actual = $this->simpleIdentity->obfuscate(data: $context);

        /** @Then the output should match the expected result */
        self::assertJsonStringEqualsJsonString($expected, json_encode($actual));
    }

    public static function sensitiveDataProvider(): array
    {
        return [
            'Empty'                   => [
                'context'  => [],
                'expected' => '[]'
            ],
            'Holder CPF'              => [
                'context'  => ['holder' => ['document' => '19125131028']],
                'expected' => '{"holder":{"document":"******31028"}}'
            ],
            'Holder CNPJ'             => [
                'context'  => ['holder' => ['document' => '19774988000174']],
                'expected' => '{"holder":{"document":"*********00174"}}'
            ],
            'Null document'           => [
                'context'  => ['document' => null],
                'expected' => '{"document":null}'
            ],
            'Multiple documents'      => [
                'context'  => [
                    'documents' => [
                        ['document' => '34115528087'],
                        ['document' => '67027619000160']
                    ]
                ],
                'expected' => '{"documents":[{"document":"******28087"},{"document":"*********00160"}]}'
            ],
            'Holder CPF from payload' => [
                'context'  => ['payload' => ['holder' => ['document' => '19774988000174']]],
                'expected' => '{"payload":{"holder":{"document":"*********00174"}}}'
            ]
        ];
    }
}
