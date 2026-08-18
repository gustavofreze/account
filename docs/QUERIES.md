* [Find account by id](#find-account-by-id)
* [Find account balance](#find-account-balance)
* [Find account transactions](#find-account-transactions)

## Find account by id

#### Recovers the account and the document of its cardholder.

**GET** `{{account-dns}}/accounts/{accountId}`

### Request

**Path and query parameters**:

| Parameter   |  Type  | Description                       | Constraints                                                         | Required |
|:------------|:------:|:----------------------------------|:--------------------------------------------------------------------|:--------:|
| `accountId` | String | Unique identifier of the account. | Must be a valid UUID. E.g., `d6e00e91-ec4f-45b3-aa33-06696fe3983a`. |   Yes    |

### Response

- `200 OK`

  **Description**: Indicates that the account was found.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "holder": {
          "document": "76169209004341414"
      },
      "account_id": "d6e00e91-ec4f-45b3-aa33-06696fe3983a"
  }
  ```

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

- `422 Unprocessable Entity`

  **Description**: Indicates that the account identifier failed validation.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "The value <not-a-uuid> is not a valid UUID."
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

## Find account balance

#### Recovers the current balance of the account, the sum of every transaction recorded against it.

An account that never moved answers zero.

**GET** `{{account-dns}}/accounts/{accountId}/balance`

### Request

**Path and query parameters**:

| Parameter   |  Type  | Description                       | Constraints                                                         | Required |
|:------------|:------:|:----------------------------------|:--------------------------------------------------------------------|:--------:|
| `accountId` | String | Unique identifier of the account. | Must be a valid UUID. E.g., `d6e00e91-ec4f-45b3-aa33-06696fe3983a`. |   Yes    |

### Response

- `200 OK`

  **Description**: Indicates that the current balance of the account was recovered.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "amount": 100.5
  }
  ```

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

- `422 Unprocessable Entity`

  **Description**: Indicates that the account identifier failed validation.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "The value <not-a-uuid> is not a valid UUID."
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

## Find account transactions

#### Lists the transactions recorded against the account, most recent first.

The page is a forward-only keyset cursor, so there is no page number and no total. The next cursor is carried in
`links.next` whenever `meta.has_next` is true, and the `links.next` entry is absent otherwise.

**GET** `{{account-dns}}/accounts/{accountId}/transactions`

### Request

**Path and query parameters**:

| Parameter      |  Type   | Description                                                                  | Constraints                                                                                                                     | Required |
|:---------------|:-------:|:-----------------------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------|:--------:|
| `sort`         | String  | Deterministic ordering of the page.                                          | Sortable by `created_at` and `id`, with a leading minus marking descending. Default: `-created_at,-id`.                         |    No    |
| `filter`       | String  | Filter over the transaction fields.                                          | Only `operation_type_id` is filterable, under the `==` and `=in=` operators, with integer values. E.g., `operation_type_id==4`. |    No    |
| `accountId`    | String  | Unique identifier of the account.                                            | Must be a valid UUID. E.g., `d6e00e91-ec4f-45b3-aa33-06696fe3983a`.                                                             |   Yes    |
| `page[size]`   | Integer | Items per page. E.g., `20`.                                                  | Must be between 1 and 100. Default: 20.                                                                                         |    No    |
| `page[cursor]` | String  | Opaque forward-only cursor carried by `links.next` of the previous response. | Must be a token this endpoint issued, never a value the client builds.                                                          |    No    |

### Response

- `200 OK`

  **Description**: Indicates that the transactions page of the account was recovered.

  **Headers**:
  | Header |  Type  | Description                                                                      | Constraints                                                                           | Required |
  |:-------|:------:|:---------------------------------------------------------------------------------|:--------------------------------------------------------------------------------------|:--------:|
  | `Link` | String | RFC 8288 navigation carrying the same targets as the `links` object of the body. | Carries the `self` target always, and the `next` target when `meta.has_next` is true. |   Yes    |

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "data": [
          {
              "id": "83c26b99-c310-43ff-be3a-82c745339b0a",
              "amount": -10.0,
              "account_id": "d6e00e91-ec4f-45b3-aa33-06696fe3983a",
              "created_at": "2026-08-13T09:26:03.093299+00:00",
              "operation_type_id": 1
          }
      ],
      "meta": {
          "per_page": 1,
          "has_next": true
      },
      "links": {
          "self": "/accounts/d6e00e91-ec4f-45b3-aa33-06696fe3983a/transactions?page[size]=1",
          "next": "/accounts/d6e00e91-ec4f-45b3-aa33-06696fe3983a/transactions?page[cursor]=WyIyMDI2LTA4LTEzIDA5OjI2OjAzLjA5MzI5OSIsIjgzYzI2Yjk5LWMzMTAtNDNmZi1iZTNhLTgyYzc0NTMzOWIwYSJd&page[size]=1"
      }
  }
  ```

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

- `422 Unprocessable Entity`

  **Description**: Indicates that the account identifier or a query parameter failed validation.

  **Content-Type**: application/json

  **Body**:
  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "The value <not-a-uuid> is not a valid UUID."
  }
  ```

  or when the sort targets a field that is not sortable:

  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "Sort field <amount> is not allowed."
  }
  ```

  or when the filter targets a field that is not filterable:

  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "Filter field <amount> is not allowed."
  }
  ```

  or when the page size is above the maximum:

  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "Page size <500> must be less than or equal to 100."
  }
  ```

  or when the cursor cannot be decoded:

  ```json
  {
      "code": "INVALID_REQUEST",
      "message": "Cursor token <broken> is invalid and could not be decoded."
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
