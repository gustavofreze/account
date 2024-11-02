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
            'Only whitespace spaces only'             => ['number' => '           '],
            'Too long more than 50 digits'            => ['number' => str_repeat('1', 51)],
            'Too short less than 11 digits'           => ['number' => '123'],
            'Alphabetic characters letters only'      => ['number' => 'abcdefghijk'],
            'Empty string no characters provided'     => ['number' => ''],
            'Contains spaces numbers with spaces'     => ['number' => '123 456 7890'],
            'Mixed alphanumeric letters and numbers'  => ['number' => '12345abcde'],
            'Special characters numbers with symbols' => ['number' => '1234!@#$%^']
        ];
    }
}
