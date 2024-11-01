CREATE TABLE `transactions`
(
    `id`                BINARY(16)     NOT NULL COMMENT 'Unique identifier for the transaction.',
    `account_id`        BINARY(16)     NOT NULL COMMENT 'Unique identifier for the account.',
    `operation_type_id` TINYINT        NOT NULL COMMENT 'Type of operation.',
    `amount`            DECIMAL(15, 2) NOT NULL COMMENT 'Transaction amount.',
    `created_at`        TIMESTAMP(6)   NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Date when the record was inserted.',
    PRIMARY KEY (`id`),
    CONSTRAINT fk_account_id FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci COMMENT ='Table used to persist transaction records.';
