<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InvalidDocument;
use Account\Application\Domain\Models\Account\Documents\CNPJ;
use Account\Application\Domain\Models\Account\Documents\CPF;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HolderTest extends TestCase
{
    #[DataProvider('invalidCPFDataProvider')]
    public function testExceptionWhenInvalidCPF(string $number): void
    {
        /** @Given an invalid CPF number is provided */
        /** @Then an InvalidDocument exception is thrown */
        $this->expectException(InvalidDocument::class);
        $this->expectExceptionMessage(sprintf('The value <%s> is not a valid CPF.', $number));

        /** @When creating a new CPF instance with an invalid value */
        new CPF(number: $number);
    }

    #[DataProvider('invalidCNPJDataProvider')]
    public function testExceptionWhenInvalidCNPJ(string $number): void
    {
        /** @Given an invalid CNPJ number is provided */
        /** @Then an InvalidDocument exception is thrown */
        $this->expectException(InvalidDocument::class);
        $this->expectExceptionMessage(sprintf('The value <%s> is not a valid CNPJ.', $number));

        /** @When creating a new CNPJ instance with an invalid value */
        new CNPJ(number: $number);
    }

    public static function invalidCPFDataProvider(): array
    {
        return [
            'Empty CPF'          => ['number' => ''],
            'CPF with 10 digits' => ['number' => '1234567890']
        ];
    }

    public static function invalidCNPJDataProvider(): array
    {
        return [
            'Empty CNPJ'          => ['number' => ''],
            'CNPJ with 13 digits' => ['number' => '1234567890123']
        ];
    }
}
