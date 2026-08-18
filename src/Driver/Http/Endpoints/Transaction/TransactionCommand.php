<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction;

use Account\Application\Commands\Command;
use Account\Application\Commands\CreditAccount;
use Account\Application\Commands\DebitAccount;
use Account\Application\Commands\RequestWithdrawal;
use Account\Application\Domain\Exceptions\UnsupportedOperationType;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Application\Domain\Models\Transaction\OperationType;

final readonly class TransactionCommand
{
    public function __construct(private array $payload)
    {
    }

    public function build(): Command
    {
        $amount = $this->payload['amount'];
        $accountId = new AccountId(value: $this->payload['account_id']);

        $operationTypeId = $this->payload['operation_type_id'];
        $operationType = OperationType::tryFrom($operationTypeId);

        return match ($operationType) {
            OperationType::WITHDRAWAL                 => new RequestWithdrawal(
                id: $accountId,
                transaction: $operationType->toTransaction(amount: $amount)
            ),
            OperationType::CREDIT_VOUCHER             => new CreditAccount(
                id: $accountId,
                transaction: $operationType->toTransaction(amount: $amount)
            ),
            OperationType::NORMAL_PURCHASE,
            OperationType::PURCHASE_WITH_INSTALLMENTS => new DebitAccount(
                id: $accountId,
                transaction: $operationType->toTransaction(amount: $amount)
            ),
            default                                   => throw new UnsupportedOperationType(
                value: (string)$operationTypeId
            )
        };
    }
}
