<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InvalidDocument;
use Account\Application\Domain\Models\Account\Documents\SimpleIdentity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HolderTest extends TestCase
{
    #[DataProvider('invalidSimpleIdentityDataProvider')]
    public function testExceptionWhenInvalidSimpleIdentity(string $number): void
    {
        /** @Given an invalid SimpleIdentity number is provided */
        /** @Then an InvalidDocument exception is thrown */
        $template = 'The value <%s> is not a valid SimpleIdentity.';
        $this->expectException(InvalidDocument::class);
        $this->expectExceptionMessage(sprintf($template, $number));

        /** @When creating a new SimpleIdentity instance with an invalid value */
        SimpleIdentity::from(number: $number);
    }

    public static function invalidSimpleIdentityDataProvider(): array
    {
        return [
            'Too short'             => ['number' => '123'],
            'Empty string'          => ['number' => ''],
            'Only whitespace'       => ['number' => '           '],
            'Contains spaces'       => ['number' => '123 456 7890'],
            'Mixed alphanumeric'    => ['number' => '12345abcde'],
            'Special characters'    => ['number' => '1234!@#$%^'],
            'Alphabetic characters' => ['number' => 'abcdefghijk']
        ];
    }
}
