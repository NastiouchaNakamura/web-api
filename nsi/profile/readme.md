# Challenge

### POST Method

Creates a profile using specified username and password, except if username is already used.

The body must use MIME type `application/json` and indicate that use in `Content-Type` header : `Content-Type: application/json`.

#### URI Parameters
No URI parameter.

#### Body Parameters
| Name | Type | Description |
|---|---|---|
| `username` | string | Username of profile, max length 63 characters and must be non-whitespace ASCII (regex: `/[\x21-\x7E]{1,63}/`). |
| `password` | string | Username of profile, max length 63 bytes and can contain any sequence of UTF-8 characters. |

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 201 | `boolean` | If no error is made. |
| 400 | `UserError` | A required parameter is missing or has an incorrect type or format. |
| 405 | `UserError` | HTTP method is not allowed. |
| 409 | `UserError` | Username is already used by an existing profile. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is null.

```json5
{
  "metadata": { /*...*/ },
  "data": null
}
```
