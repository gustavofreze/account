<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Transaction;

use Account\Application\Domain\Models\Transaction\Amounts\Amount;

/**
 * Represents a financial transaction.
 */
interface Transaction
{
    /**
     * Retrieves the unique identifier of the transaction.
     *
     * @return TransactionId The transaction's unique identifier.
     */
    public function getId(): TransactionId;

    /**
     * Retrieves the amount of the transaction.
     *
     * @return Amount The transaction amount.
     */
    public function getAmount(): Amount;
}
