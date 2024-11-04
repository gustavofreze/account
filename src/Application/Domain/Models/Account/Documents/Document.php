<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Account\Documents;

/**
 * Defines a contract for various types of documents with validation.
 */
interface Document
{
    /**
     * Retrieves the document number.
     *
     * @return string The document number.
     */
    public function getNumber(): string;
}
