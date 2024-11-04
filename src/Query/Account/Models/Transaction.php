<?php

declare(strict_types=1);

namespace Account\Query\Account\Models;

use DateTimeImmutable;
use DateTimeInterface;
use TinyBlocks\Math\BigDecimal;

final readonly class Transaction
{
    private const int SCALE = 2;

    private function __construct(
        public string $id,
        public float $amount,
        public string $createdAt,
        public string $accountId,
        public int $operationTypeId,
    ) {
    }

    public static function from(array $data): Transaction
    {
        return new Transaction(
            id: $data['id'],
            amount: BigDecimal::fromString(value: $data['amount'], scale: self::SCALE)->toFloat(),
            createdAt: (new DateTimeImmutable($data['createdAt']))->format(DateTimeInterface::ATOM),
            accountId: $data['accountId'],
            operationTypeId: $data['operationTypeId']
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'amount'            => $this->amount,
            'created_at'        => $this->createdAt,
            'account_id'        => $this->accountId,
            'operation_type_id' => $this->operationTypeId
        ];
    }
}
