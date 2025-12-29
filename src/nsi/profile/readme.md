# Challenge

### POST Method

Creates a profile using specified username and password, except if username is already used.

The body must use MIME type `application/json` and indicate that use in `Content-Type` header : `Content-Type: application/json`.

#### URI Parameters
No URI parameter.

#### Body Parameters
| Name | Type | Description |
|---|---|---|
| `username` | string | Username of profile, max length 63 characters and must be non-whitespace ASCII (regex: `/[\x21-\x30\x3B-\x7E]{1,63}/`). |
| `password` | string | Username of profile, max length 63 bytes and can contain any sequence of UTF-8 characters. |

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 201 | `boolean` | The profile has successfully been created. |
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

### PATCH Method

Updates the password of a profile.

The body must use MIME type `application/json` and indicate that use in `Content-Type` header : `Content-Type: application/json`.

#### URI Parameters
No URI parameter.

#### Body Parameters
| Name | Type | Description |
|---|---|---|
| `password` | string | Username of profile, max length 63 bytes and can contain any sequence of UTF-8 characters. |

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 200 | `boolean` | The password of the profile has successfully been changed. |
| 400 | `UserError` | A required parameter is missing or has an incorrect type or format. |
| 401 | `UserError` | Used 'Authorization' header using bad scheme, without providing credentials or using bad credentials (must use 'Basic' HTTP authentication scheme described in [RFC-7617](https://datatracker.ietf.org/doc/html/rfc7617) with `NSI` as realm). |
| 405 | `UserError` | HTTP method is not allowed. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is null.

```json5
{
  "metadata": { /*...*/ },
  "data": null
}
```
