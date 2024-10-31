<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\NonPositiveAmount;
use PHPUnit\Framework\TestCase;

final class PositiveAmountTest extends TestCase
{
    public function testPositiveAmountCreation(): void
    {
        /** @Given a positive value is provided */
        $value = 10.0;

        /** @When creating a PositiveAmount instance with this value */
        $actual = PositiveAmount::from(value: $value);

        /** @Then the PositiveAmount instance should be created with the correct value */
        self::assertSame($value, $actual->toFloat());
    }

    public function testExceptionWhenNonPositiveAmountIsProvided(): void
    {
        /** @Given a non-positive value is provided */
        $value = 0.00;

        /** @Then a NonPositiveAmount exception should be thrown */
        $this->expectException(NonPositiveAmount::class);
        $this->expectExceptionMessage('Non-positive amount <0.00> is invalid. Amount must be greater than zero.');

        /** @When attempting to create a PositiveAmount with a non-positive value */
        PositiveAmount::from(value: $value);
    }
}
