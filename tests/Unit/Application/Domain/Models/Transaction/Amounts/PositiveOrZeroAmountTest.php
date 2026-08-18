<?php

declare(strict_types=1);

namespace Test\Unit\Application\Domain\Models\Transaction\Amounts;

use Account\Application\Domain\Exceptions\InvalidAmount;
use Account\Application\Domain\Models\Transaction\Amounts\PositiveOrZeroAmount;
use PHPUnit\Framework\TestCase;

final class PositiveOrZeroAmountTest extends TestCase
{
    public function testZeroAmountCreation(): void
    {
        /** @Given a zero value */
        $value = 0.0;

        /** @When creating a PositiveOrZeroAmount with a zero value */
        $actual = PositiveOrZeroAmount::from(value: $value);

        /** @Then the value should be zero */
        self::assertSame(0.0, $actual->toFloat());
    }

    public function testPositiveAmountCreation(): void
    {
        /** @Given a positive value */
        $value = 20.0;

        /** @When creating a PositiveOrZeroAmount with a positive value */
        $actual = PositiveOrZeroAmount::from(value: $value);

        /** @Then the value should match the expected positive amount */
        self::assertSame(20.0, $actual->toFloat());
    }

    public function testExceptionWhenNegativeAmount(): void
    {
        /** @Given a negative value */
        $value = -10.00;

        /** @Then an exception of type InvalidAmount should be thrown */
        $this->expectException(InvalidAmount::class);

        /** @When attempting to create a PositiveOrZeroAmount with a negative value */
        PositiveOrZeroAmount::from(value: $value);
    }
}
