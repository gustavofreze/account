<?php

declare(strict_types=1);

namespace Account\Driven\Account;

use Account\Application\Domain\Models\Transaction\Transaction;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OperationTypeTest extends TestCase
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function testExceptionWhenUnsupportedTransactionType(): void
    {
        /** @Given an unsupported transaction type */
        $unsupportedTransaction = $this->createMock(Transaction::class);

        /** @Then an InvalidArgumentException should be thrown */
        $template = 'Unsupported transaction type <%s>.';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf($template, get_class($unsupportedTransaction)));

        /** @When fromTransaction is called with the unsupported transaction */
        OperationType::fromTransaction(transaction: $unsupportedTransaction);
    }
}
