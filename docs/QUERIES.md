* [Find account by id](#find_account_by_id)

<div id='find_account_by_id'></div> 

## Find account by id

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

> Requests are available for import into
`Postman`.
> [Get the collection here](/docs/Account.postman_collection.json).
