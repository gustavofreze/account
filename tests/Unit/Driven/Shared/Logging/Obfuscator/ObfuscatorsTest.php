<?php

declare(strict_types=1);

namespace Account\Driven\Shared\Logging\Obfuscator;

use PHPUnit\Framework\TestCase;

final class ObfuscatorsTest extends TestCase
{
    private Obfuscators $obfuscators;

    protected function setUp(): void
    {
        $this->obfuscators = Obfuscators::createFrom(elements: [new Name()]);
    }

    public function testApplyInEmptyContext(): void
    {
        /** @Given an empty context */
        $context = [];

        /** @When obfuscators are applied */
        $actual = $this->obfuscators->applyIn(data: $context);

        /** @Then the output should be an empty array */
        self::assertEmpty($actual);
    }

    public function testApplyInWithNameObfuscator(): void
    {
        /** @Given a context with a name */
        $context = ['name' => 'Pyrothrax Flamekeeper'];

        /** @When obfuscators are applied */
        $actual = $this->obfuscators->applyIn(data: $context);

        /** @Then the output should match the expected obfuscated result */
        self::assertSame(['name' => '***********lamekeeper'], $actual);
    }

    public function testApplyInWithoutObfuscators(): void
    {
        /** @Given a context with a name and no obfuscators */
        $context = ['name' => 'Visible Name'];

        /** @When obfuscators are applied */
        $actual = Obfuscators::createFromEmpty()->applyIn(data: $context);

        /** @Then the output should match the original context */
        self::assertSame($context, $actual);
    }

    public function testApplyInReturnsAllItemsWhenMultipleItemsProvided(): void
    {
        /** @Given a context with multiple names */
        $context = [
            ['name' => 'First Name'],
            ['name' => 'Second Name'],
        ];

        /** @When obfuscators are applied */
        $actual = $this->obfuscators->applyIn(data: $context);

        /** @Then the output should match the expected obfuscated results for all items */
        self::assertCount(2, $actual);
        self::assertSame(['name' => '***** Name'], $actual[0]);
        self::assertSame(['name' => '****** Name'], $actual[1]);
    }
}
