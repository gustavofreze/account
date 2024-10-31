<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\NonNegativeAmount;
use PHPUnit\Framework\TestCase;

final class NegativeAmountTest extends TestCase
{
    public function testNegativeAmountCreation(): void
    {
        /** @Given a negative value is provided */
        $value = -10.0;

        /** @When creating a NegativeAmount instance with this value */
        $actual = NegativeAmount::from(value: $value);

        /** @Then the NegativeAmount instance should be created with the correct value */
        self::assertSame($value, $actual->toFloat());
    }

    public function testExceptionWhenNonNegativeAmountIsProvided(): void
    {
        /** @Given a non-negative value is provided */
        $value = 10.0;

        /** @Then a NonNegativeAmount exception should be thrown */
        $this->expectException(NonNegativeAmount::class);
        $this->expectExceptionMessage('Negative amount <10.00> is invalid. Amount must be negative.');

        /** @When attempting to create a NegativeAmount with a non-negative value */
        NegativeAmount::from(value: $value);
    }
}
