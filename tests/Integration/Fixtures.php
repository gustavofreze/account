<?php

declare(strict_types=1);

namespace Test\Integration;

use Doctrine\DBAL\Connection;

final readonly class Fixtures
{
    private function __construct(private Connection $connection)
    {
    }

    public static function from(Connection $connection): Fixtures
    {
        return new Fixtures(connection: $connection);
    }

    public function purgeAll(): void
    {
        $this->truncateTransactions();
        $this->truncateAccounts();
        $this->truncateOutboxEvents();
    }

    public function insertAccount(string $id, string $document): void
    {
        $this->connection->executeStatement(
            sql: '
                INSERT INTO accounts (id, holder_document_number)
                VALUES (UUID_TO_BIN(:id), :document)
            ',
            params: [
                'id'       => $id,
                'document' => $document
            ]
        );
    }

    public function truncateAccounts(): void
    {
        $this->connection->executeStatement(sql: 'DELETE FROM accounts');
    }

    public function balanceOf(string $accountId): float
    {
        $amount = $this->connection->fetchOne(
            query: '
                SELECT COALESCE(SUM(trs.amount), 0.00) AS amount
                FROM transactions AS trs
                WHERE trs.account_id = UUID_TO_BIN(:accountId)
            ',
            params: ['accountId' => $accountId]
        );

        return (float)$amount;
    }

    public function insertTransaction(
        string $id,
        float $amount,
        string $accountId,
        string $createdAt,
        int $operationTypeId
    ): void {
        $this->connection->executeStatement(
            sql: '
                INSERT INTO transactions (id, account_id, operation_type_id, amount, created_at)
                VALUES (UUID_TO_BIN(:id), UUID_TO_BIN(:accountId), :operationTypeId, :amount, :createdAt)
            ',
            params: [
                'id'              => $id,
                'amount'          => $amount,
                'accountId'       => $accountId,
                'createdAt'       => $createdAt,
                'operationTypeId' => $operationTypeId
            ]
        );
    }

    public function truncateTransactions(): void
    {
        $this->connection->executeStatement(sql: 'DELETE FROM transactions');
    }

    public function truncateOutboxEvents(): void
    {
        $this->connection->executeStatement(sql: 'DELETE FROM outbox_events');
    }

    public function lastOutboxAggregateVersionOf(string $accountId): int
    {
        $version = $this->connection->fetchOne(
            query: '
                SELECT COALESCE(MAX(evt.aggregate_version), 0) AS aggregateVersion
                FROM outbox_events AS evt
                WHERE evt.aggregate_id = UUID_TO_BIN(:accountId)
            ',
            params: ['accountId' => $accountId]
        );

        return (int)$version;
    }

    public function outboxPayloadOf(string $accountId, string $eventType): array
    {
        $payload = $this->connection->fetchOne(
            query: '
                SELECT evt.payload
                FROM outbox_events AS evt
                WHERE evt.aggregate_id = UUID_TO_BIN(:accountId)
                  AND evt.event_type = :eventType
            ',
            params: [
                'eventType' => $eventType,
                'accountId' => $accountId
            ]
        );

        return (array)json_decode((string)$payload, true);
    }

    public function outboxEventTypesOf(string $accountId): array
    {
        return $this->connection->fetchFirstColumn(
            query: '
                SELECT evt.event_type
                FROM outbox_events AS evt
                WHERE evt.aggregate_id = UUID_TO_BIN(:accountId)
                ORDER BY evt.aggregate_version
            ',
            params: ['accountId' => $accountId]
        );
    }
}
