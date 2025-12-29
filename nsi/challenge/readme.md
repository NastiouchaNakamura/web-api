# Challenge

### GET Method

Checks the flag of a specific challenge from NSI Website, and if authentication is provided, saves challenge's success in profile.

#### URI Parameters
| Name | Type | Description |
|---|---|---|
| `challenge` | string | Challenge string identifier. |
| `flag` | string | Challenge flag to check. |

#### Body Parameters
No body parameter.

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 200 | `boolean` | If no error is made. |
| 400 | `UserError` | A required parameter is missing or has an incorrect type or format. |
| 401 | `UserError` | Used 'Authorization' header using bad scheme, without providing credentials or using bad credentials (must use 'Basic' HTTP authentication scheme described in [RFC-7617](https://datatracker.ietf.org/doc/html/rfc7617) with `NSI` as realm). |
| 404 | `UserError` | The ID given as parameters does not correspond to an existing challenge. |
| 405 | `UserError` | HTTP method is not allowed. |
| 429 | `UserError` | Too many request : only one request per minute is allowed. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is boolean : `true` if the flag is correct, `false` if the flag is incorrect. If the method is POST and the profile authentication succeeded, and the flag is correct, then the success on this challenge is quietly saved.

```json5
{
  "metadata": { /*...*/ },
  "data": true
}
```
