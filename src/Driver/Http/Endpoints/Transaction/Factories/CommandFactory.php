<?php

declare(strict_types=1);

namespace Account\Driver\Http\Endpoints\Transaction\Factories;

use Account\Application\Domain\Commands\Command;
use Account\Application\Domain\Commands\CreditAccount;
use Account\Application\Domain\Commands\DebitAccount;
use Account\Application\Domain\Commands\RequestWithdrawal;
use Account\Application\Domain\Models\Account\AccountId;
use Account\Driven\Account\OperationType;
use InvalidArgumentException;

final readonly class CommandFactory
{
    public function __construct(private array $payload)
    {
    }

    public function build(): Command
    {
        $amount = (float)$this->payload['amount'];
        $accountId = new AccountId(value: (string)$this->payload['accountId']);

        $operationTypeId = (int)$this->payload['operationTypeId'];
        $operationType = OperationType::tryFrom(value: $operationTypeId);

        $template = 'Unsupported operation type id <%s>.';

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
            default                                   => throw new InvalidArgumentException(
                message: sprintf($template, $operationTypeId)
            )
        };
    }
}
