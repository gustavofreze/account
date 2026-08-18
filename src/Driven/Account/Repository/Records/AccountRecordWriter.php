<?php

declare(strict_types=1);

namespace Account\Driven\Account\Repository\Records;

use Account\Application\Domain\Models\Account\Account;
use Account\Application\Domain\Models\Transaction\OperationType;
use Account\Application\Domain\Models\Transaction\Transaction;
use Account\Application\Exceptions\AccountAlreadyExists;
use Account\Driven\Account\Repository\Queries;
use Account\Driven\Shared\Database\DatabaseConstraint;
use Account\Driven\Shared\Database\DatabaseFailure;
use Account\Driven\Shared\Database\RelationalConnection;

final readonly class AccountRecordWriter
{
    public function __construct(private RelationalConnection $connection)
    {
    }

    public function insert(Account $account): void
    {
        try {
            $this->connection->execute(sql: Queries::INSERT, bindings: [
                'id'                   => $account->id->identityValue(),
                'holderDocumentNumber' => $account->holder->document->getNumber()
            ]);
        } catch (DatabaseFailure $failure) {
            throw match (true) {
                $failure->hasViolated(constraint: DatabaseConstraint::UNIQUE) => new AccountAlreadyExists(),
                default                                                       => $failure
            };
        }
    }

    public function record(Account $account, Transaction $transaction, OperationType $operationType): void
    {
        $this->connection->execute(sql: Queries::INSERT_TRANSACTION, bindings: [
            'id'              => $transaction->getId()->toString(),
            'amount'          => $transaction->getAmount()->toFloat(),
            'accountId'       => $account->id->identityValue(),
            'operationTypeId' => $operationType->value
        ]);
    }
}
