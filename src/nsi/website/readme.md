# Exam

### GET Method

Fetch all websites available.

#### URI Parameters
No URI parameter.

#### Body Parameters
No body parameter.

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 200 | `array` | If no error is made. |
| 405 | `UserError` | HTTP method is not allowed. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is an array of websites.

```json5
{
  "metadata": { /*...*/ },
  "data": {
    "type": "array",
    "size": 2,
    "values": [
      {
        "type": "website",
        "year": "2026-2027",
        "title": "Les pizza",
        "authors": "Toto et Lulu",
        "directory": "pizza"
      },
      /*...*/
    ]
  }
}
```
