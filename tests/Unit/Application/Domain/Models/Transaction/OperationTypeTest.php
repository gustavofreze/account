<?php

declare(strict_types=1);

namespace Test\Unit\Application\Domain\Models\Transaction;

use Account\Application\Domain\Exceptions\UnsupportedOperationType;
use Account\Application\Domain\Models\Transaction\Amounts\NegativeAmount;
use Account\Application\Domain\Models\Transaction\Operations\Withdrawal;
use Account\Application\Domain\Models\Transaction\OperationType;
use PHPUnit\Framework\TestCase;

/**
 * Justification for a dedicated non-root test (php-testing-unit § Default stance). The unsupported branch of
 * OperationType::fromDebitTransaction is unreachable through the Account aggregate root, which never resolves an
 * operation type, and unreachable through the endpoint, whose request validation admits only the four supported
 * identifiers. Only the debit repository path reaches it, with a transaction the aggregate itself accepted.
 */
final class OperationTypeTest extends TestCase
{
    public function testExceptionWhenUnsupportedTransactionType(): void
    {
        /** @Given a transaction that is not a debit operation */
        $transaction = Withdrawal::createFrom(amount: NegativeAmount::from(value: 100.00));

        /** @Then an UnsupportedOperationType should be thrown */
        $this->expectException(UnsupportedOperationType::class);

        /** @When fromDebitTransaction is called with the unsupported transaction */
        OperationType::fromDebitTransaction(transaction: $transaction);
    }
}
