<?php

declare(strict_types=1);

namespace Account\Application\Domain\Exceptions;

use Account\Application\Domain\Models\Account\Documents\Document;
use DomainException;

final class AccountAlreadyExists extends DomainException
{
    public function __construct(Document $document)
    {
        $template = 'An account with document number <%s> already exists.';
        parent::__construct(message: sprintf($template, $document->getNumber()));
    }
}
