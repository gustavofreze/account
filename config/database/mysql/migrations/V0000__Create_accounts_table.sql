CREATE TABLE `accounts`
(
    `id`                     BINARY(16)     NOT NULL COMMENT 'Unique identifier for the account.',
    `holder_document_number` VARCHAR(50)    NOT NULL COMMENT 'Unique document number of the account holder.',
    `created_at`             TIMESTAMP(6)   NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Date when the record was inserted.',
    PRIMARY KEY (`id`),
    CONSTRAINT accounts_uk01 UNIQUE (`holder_document_number`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci COMMENT ='Table used to persist account records.'
