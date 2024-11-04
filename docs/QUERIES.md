* [Retrieve account balance](#retrieve_account_balance)
* [Retrieve an account by id](#retrieve_account_by_id)
* [Retrieve account transactions](#retrieve_account_transactions)

<div id=retrieve_account_balance></div> 

## Retrieve account balance

###### It is the process of retrieving an account's balance using its unique identifier.

**GET** `{{account-dns}}/accounts/{{accountId}}/balance`

**Request**

| Parameter   |  Type  | Description                       | Constraints                     | Required |
|:------------|:------:|:----------------------------------|:--------------------------------|:--------:|
| `accountId` | String | Unique identifier of the account. | Must be a valid UUID version 4. |   Yes    |

**Responses**

- `200 OK`

  **Description**: Indicates that the account balance was successfully retrieved.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "amount": 200.00
  }
  ```

- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <d6e00e91-ec4f-45b3-aa33-06696fe3983a> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "accountId": "The value <dc3e4613-bd46-4c11-0000-9b741815010d> is not a valid UUID."
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

<div id=retrieve_account_by_id></div> 

## Retrieve an account by id

###### It is the process of retrieving an account's information using its unique identifier.

**GET** `{{account-dns}}/accounts/{accountId}`

**Request**

| Parameter   |  Type  | Description                       | Constraints                     | Required |
|:------------|:------:|:----------------------------------|:--------------------------------|:--------:|
| `accountId` | String | Unique identifier of the account. | Must be a valid UUID version 4. |   Yes    |

**Responses**

- `200 OK`

  **Description**: Indicates that the account was successfully found.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "id": "d6e00e91-ec4f-45b3-aa33-06696fe3983a",
      "holder": {
          "document": "761692090043413414"
      }
  }
  ```

- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <d6e00e91-ec4f-45b3-aa33-06696fe3983a> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "accountId": "The value <dc3e4613-bd46-4c11-0000-9b741815010d> is not a valid UUID."
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

<div id=retrieve_account_transactions></div> 

## Retrieve account transactions

###### It is the process of retrieving transactions associated with a specific account using its unique identifier. Optionally, filters can be applied to narrow down the results.

**GET** `{{account-dns}}/accounts/{{accountId}}/transactions?operationTypeIds[]=1`

**Request**

| Parameter          |  Type  | Description                              | Constraints                                                                                                                                         | Required |
|:-------------------|:------:|:-----------------------------------------|:----------------------------------------------------------------------------------------------------------------------------------------------------|:--------:|
| `accountId`        | String | Unique identifier of the account.        | Must be a valid UUID version 4.                                                                                                                     |   Yes    |
| `operationTypeIds` | Array  | An optional array of operation type IDs. | Must be a positive integer (e.g., 1 for **Normal purchase**, 2 for **Purchase with Installments**, 3 for **Withdrawal**, 4 for **Credit Voucher**). |    No    |

**Responses**

- `200 OK`

  **Description**: Indicates that the transactions were successfully retrieved.

  **Content-Type**: application/json

  **Body**:
  ```json
  [
      {
        "id": "83c26b99-c310-43ff-be3a-82c745339b0a",
        "amount": -25.5,
        "created_at": "2024-11-04T10:55:25-03:00",
        "account_id": "250e71ca-1bac-4b32-822f-c786cc0129a2",
        "operation_type_id": 3
      },
      {
        "id": "84099b99-8b6b-4ccb-aade-3ccfa55a3b6f",
        "amount": 60.0,
        "created_at": "2024-11-04T10:42:23-03:00",
        "account_id": "250e71ca-1bac-4b32-822f-c786cc0129a2",
        "operation_type_id": 4
      }
  ]
  ```

- `404 Not Found`

  **Description**: Indicates that the specified account ID does not exist.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": "Account with ID <7d1ce6a5-98d0-4c85-a543-b8620212818c> not found."
  }
  ```

- `422 Unprocessable Entity`

  **Description**: Indicates that one or more of the provided values are invalid.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "error": {
          "accountId": "The value <dc3e4613-bd46-4c11-0000-9b741815010d> is not a valid UUID."
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
