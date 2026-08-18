* [Account opening](#account-opening)
* [Transaction creating](#transaction-creating)

## Account opening

#### Opens an account for the cardholder identified by a document number.

**POST** `{{account-dns}}/accounts`

### Headers

| Header         |  Type  | Description               | Constraints               | Required |
|:---------------|:------:|:--------------------------|:--------------------------|:--------:|
| `Content-Type` | String | The request content type. | Must be application/json. |   Yes    |

### Request

**Body parameters**:

| Parameter         |  Type  | Description                                 | Constraints                                                | Required |
|:------------------|:------:|:--------------------------------------------|:-----------------------------------------------------------|:--------:|
| `holder`          | Object | Cardholder the account belongs to.          | Must carry the document of the cardholder.                 |   Yes    |
| `holder.document` | String | Document number identifying the cardholder. | Must contain only digits, with a length between 11 and 50. |   Yes    |

```json
{
    "holder": {
        "document": "76169209004341414"
    }
}
```

### Response

- `201 Created`

  **Description**: Indicates that the account was opened and its identifier was assigned.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "id": "d6e00e91-ec4f-45b3-aa33-06696fe3983a"
  }
  ```

- `409 Conflict`

  **Description**: Indicates that an account already exists for the holder document number.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "ACCOUNT_ALREADY_EXISTS",
      "message": "An account already exists for this holder document number."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that the request payload failed validation.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "`.holder` must be present"
  }
  ```

  or when the document number does not match the accepted format:

  ```json
  {
      "code": "DOCUMENT_FORMAT_NOT_VALID",
      "message": "The document number must contain only digits, with a length between 11 and 50."
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INTERNAL_ERROR",
      "message": "An unexpected error occurred."
  }
  ```

## Transaction creating

#### Records a transaction against an account and moves its balance.

A normal purchase, a purchase with installments, and a withdrawal are recorded as debits and stored with a negative
amount. A credit voucher is recorded as a credit and stored with a positive amount. The request always carries the
amount as a positive number, and the sign comes from the operation type.

**POST** `{{account-dns}}/transactions`

### Headers

| Header         |  Type  | Description               | Constraints               | Required |
|:---------------|:------:|:--------------------------|:--------------------------|:--------:|
| `Content-Type` | String | The request content type. | Must be application/json. |   Yes    |

### Request

**Body parameters**:

| Parameter           |  Type   | Description                                                           | Constraints                                                                                        | Required |
|:--------------------|:-------:|:----------------------------------------------------------------------|:---------------------------------------------------------------------------------------------------|:--------:|
| `amount`            | Number  | Absolute amount moved by the operation.                               | Must be a positive number.                                                                         |   Yes    |
| `account_id`        | String  | Unique identifier of the account the transaction is recorded against. | Must be a valid UUID.                                                                              |   Yes    |
| `operation_type_id` | Integer | Kind of operation being recorded.                                     | One of 1 (normal purchase), 2 (purchase with installments), 3 (withdrawal), or 4 (credit voucher). |   Yes    |

```json
{
    "amount": 123.45,
    "account_id": "d6e00e91-ec4f-45b3-aa33-06696fe3983a",
    "operation_type_id": 1
}
```

### Response

- `204 No Content`

  **Description**: Indicates that the transaction was recorded and the account balance was moved.

  **Content-Type**: _(no content)_

- `404 Not Found`

  **Description**: Indicates that no account holds the given identifier.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "ACCOUNT_NOT_FOUND",
      "message": "Account not found."
  }
  ```

- `409 Conflict`

  **Description**: Indicates that the account does not hold enough funds for the debit.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "ACCOUNT_WITH_INSUFFICIENT_FUNDS",
      "message": "Account has insufficient funds for this transaction."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that the request payload failed validation.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "The value <\"xxxxxx\"> is not a valid UUID."
  }
  ```

  or when the operation type identifier is outside the accepted set:

  ```json
  {
      "code": "UNSUPPORTED_OPERATION_TYPE",
      "message": "The operation type is not supported."
  }
  ```

  or when the amount is negative:

  ```json
  {
      "code": "INVALID_AMOUNT",
      "message": "The amount must be positive or zero."
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INTERNAL_ERROR",
      "message": "An unexpected error occurred."
  }
  ```
