<?php

declare(strict_types=1);

namespace Account\Query\Account\FindTransactions\ReadModel;

final readonly class AccountTransaction
{
    private function __construct(
        public string $id,
        public float $amount,
        public string $accountId,
        public string $createdAt,
        public int $operationTypeId
    ) {
    }

    public static function from(
        string $id,
        float $amount,
        string $accountId,
        string $createdAt,
        int $operationTypeId
    ): AccountTransaction {
        return new AccountTransaction(
            id: $id,
            amount: $amount,
            accountId: $accountId,
            createdAt: $createdAt,
            operationTypeId: $operationTypeId
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'amount'            => $this->amount,
            'account_id'        => $this->accountId,
            'created_at'        => $this->createdAt,
            'operation_type_id' => $this->operationTypeId
        ];
    }
}
