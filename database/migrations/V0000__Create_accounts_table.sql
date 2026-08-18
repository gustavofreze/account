CREATE TABLE accounts
(
    id                     BINARY(16)   NOT NULL COMMENT '[NONE] The account identifier in Version 7 UUID format (e.g., 0190d09e-a7a8-7e89-b48c-09fff0f0f0f0).',
    holder_document_number VARCHAR(50)  NOT NULL COMMENT '[PII] The national document number that identifies the account holder (e.g., 12345678901).',
    created_at             TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '[NONE] The UTC date and time when the record was created in ISO 8601 format (e.g., 2026-02-13T08:49:44.931408+00:00).',
    PRIMARY KEY (id),
    CONSTRAINT unq_accounts_holder_document_number UNIQUE (holder_document_number)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_0900_ai_ci
    COMMENT = '[PII] Table used to persist account records.';
