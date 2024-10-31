<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account;

use Account\Application\Domain\Exceptions\InvalidTransaction;
use Account\Application\Domain\Models\Transaction\Transaction;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class BalanceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testExceptionWhenInvalidTransactionType(): void
    {
        /** @Given a balance is initialized */
        $balance = Balance::initialize();

        /** @And an invalid transaction type is provided */
        $invalidTransaction = $this->createMock(Transaction::class);

        /** @Then an InvalidTransaction exception should be thrown */
        $template = 'The transaction type <%s> is invalid or not supported.';
        $this->expectException(InvalidTransaction::class);
        $this->expectExceptionMessage(sprintf($template, $invalidTransaction::class));

        /** @When attempting to apply the invalid transaction */
        $balance->apply(transaction: $invalidTransaction);
    }
}
