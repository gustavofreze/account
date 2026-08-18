CREATE TABLE transactions
(
    id                BINARY(16)     NOT NULL COMMENT '[NONE] The transaction identifier in Version 7 UUID format (e.g., 0190d09e-a7a8-7e89-b48c-09fff0f0f0f0).',
    amount            DECIMAL(15, 2) NOT NULL COMMENT '[NONE] The signed value of the movement in the account currency, negative on a debit and positive on a credit (e.g., -50.00).',
    account_id        BINARY(16)     NOT NULL COMMENT '[NONE] The identifier of the account that owns the transaction in Version 7 UUID format (e.g., 0190d09e-a7a8-7e89-b48c-09fff0f0f0f0).',
    operation_type_id TINYINT        NOT NULL COMMENT '[NONE] The kind of movement the transaction records (e.g., 1 for a normal purchase, 2 for a purchase with installments, 3 for a withdrawal, 4 for a credit voucher).',
    created_at        TIMESTAMP(6)   NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '[NONE] The UTC date and time when the record was created in ISO 8601 format (e.g., 2026-02-13T08:49:44.931408+00:00).',
    PRIMARY KEY (id),
    KEY idx_transactions_account_id (account_id),
    CONSTRAINT fk_transactions_account_id FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_0900_ai_ci
    COMMENT = 'Table used to persist transaction records.';
