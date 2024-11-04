* [Account opening](#account_opening)
* [Account crediting](#account_crediting)
* [Account debiting](#account_debiting)
* [Account withdrawal](#account_withdrawal)

<div id='account_opening'></div> 

## Account opening

###### It is the process of creating a new account.

**POST** `{{account-dns}}/accounts`

**Request**

| Parameter         |  Type  | Description                                   | Constraints                                         | Required |
|:------------------|:------:|:----------------------------------------------|:----------------------------------------------------|:--------:|
| `holder`          | Object | Account holder.                               | N/A                                                 |   Yes    |
| `holder.document` | String | Unique document number of the account holder. | Must contain only digits, length between 11 and 50. |   Yes    |

```json
{
    "holder": {
        "document": "76169209004341414"
    }
}
```

**Responses**

- `201 Created`

  **Description**: Indicates that the account was successfully created.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "id": "50aaf160-0e69-444b-b625-e83cc76d6fcd"
  }
  ```

- `409 Conflict`

  **Description**: Indicates that an account with the provided document number already exists.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "An account with document number <76169209004341414> already exists."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "holder": "document must contain only digits (0-9)."
      }
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "An internal server error occurred."
  }
  ```

<div id='account_crediting'></div> 

## Account crediting

###### It is the process of adding funds to an existing account.

**POST** `{{account-dns}}/transactions`

**Request**

| Parameter           |  Type   | Description                        | Constraints                                                  | Required |
|:--------------------|:-------:|:-----------------------------------|:-------------------------------------------------------------|:--------:|
| `amount`            | Number  | Amount to credit to the account.   | Must be a positive decimal value.                            |   Yes    |
| `account_id`        | String  | Unique identifier of the account.  | Must be a valid UUID version 4.                              |   Yes    |
| `operation_type_id` | Integer | Type of operation being performed. | Must be a positive integer (e.g., 4 for **Credit voucher**). |   Yes    |

```json
{
    "amount": 60.00,
    "account_id": "50aaf160-0e69-444b-b625-e83cc76d6fcd",
    "operation_type_id": 4
}
```

**Responses**

- `204 No Content`

  **Description**: Indicates that the funds were successfully credited to the account.

  **Content-Type**: application/json

  **Body**: N/A


- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <50aaf160-0e69-444b-b625-e83cc76d6fcd> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "amount": "must be a positive decimal."
      }
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "An internal server error occurred."
  }
  ```

<div id='account_debiting'></div> 

## Account debiting

###### It is the process of debiting funds from an existing account.

**POST** `{{account-dns}}/transactions`

**Request**

| Parameter           |  Type   | Description                        | Constraints                                                                                         | Required |
|:--------------------|:-------:|:-----------------------------------|:----------------------------------------------------------------------------------------------------|:--------:|
| `amount`            | Number  | Amount to debit from the account.  | Must be a positive decimal value.                                                                   |   Yes    |
| `account_id`        | String  | Unique identifier of the account.  | Must be a valid UUID version 4.                                                                     |   Yes    |
| `operation_type_id` | Integer | Type of operation being performed. | Must be a positive integer (e.g., 1 for **Normal purchase**, 2 for **Purchase with installments**). |   Yes    |

```json
{
    "amount": 60.00,
    "account_id": "50aaf160-0e69-444b-b625-e83cc76d6fcd",
    "operation_type_id": 1
}
```

**Responses**

- `204 No Content`

  **Description**: Indicates that the funds were successfully debited from the account.

  **Content-Type**: application/json

  **Body**: N/A


- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <50aaf160-0e69-444b-b625-e83cc76d6fcd> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "amount": "must be a positive decimal."
      }
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "An internal server error occurred."
  }
  ```

<div id='account_withdrawal'></div> 

## Account withdrawal

###### It is the process of withdrawing funds from an existing account.

**POST** `{{account-dns}}/transactions`

**Request**

| Parameter           |  Type   | Description                          | Constraints                                              | Required |
|:--------------------|:-------:|:-------------------------------------|:---------------------------------------------------------|:--------:|
| `amount`            | Number  | Amount to withdraw from the account. | Must be a positive decimal value.                        |   Yes    |
| `account_id`        | String  | Unique identifier of the account.    | Must be a valid UUID version 4.                          |   Yes    |
| `operation_type_id` | Integer | Type of operation being performed.   | Must be a positive integer (e.g., 3 for **Withdrawal**). |   Yes    |

```json
{
    "amount": 60.00,
    "account_id": "50aaf160-0e69-444b-b625-e83cc76d6fcd",
    "operation_type_id": 3
}
```

**Responses**

- `204 No Content`

  **Description**: Indicates that the funds were successfully withdrawn from the account.

  **Content-Type**: application/json

  **Body**: N/A


- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <50aaf160-0e69-444b-b625-e83cc76d6fcd> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "amount": "must be a positive decimal."
      }
  }
  ```

- `500 Internal Server Error`

  **Description**: Indicates that an unexpected error occurred on the server while processing the request.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "An internal server error occurred."
  }
  ```

<br>

> Requests and environment variables are available for import in `Postman`. You can access them [here](/docs/postman).
