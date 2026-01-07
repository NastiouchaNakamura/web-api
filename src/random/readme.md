# Random

### GET Method

Gives a certain number of pseudorandom integers from a given seed.

#### URI Parameters
| Name | Type | Description |
|---|---|---|
| `seed` | string | Seed used for pseudorandom number generation. |
| `min` | integer | Smallest possible value of generated pseudorandom integers (included). Must be smaller than `max`. |
| `max` | integer | Largest possible value of generated pseudorandom integers (included). Must be greater than `min`. |
| `n` | integer | (optional) The number of pseudorandom integers to generate, defaults to 1. Must be positive. Limited to 1000 at most per request |

#### Body Parameters
No body parameter.

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 201 | `boolean` | No error is made.  |
| 400 | `UserError` | A required parameter is missing or has an incorrect type or format. |
| 405 | `UserError` | HTTP method is not allowed. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is array of int.

```json5
{
  "metadata": { /*...*/ },
  "data": {
    "type": "array",
    "size": 1,
    "values": [4, 5, 9]
  }
}
```
