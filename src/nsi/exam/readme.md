# Exam

### GET Method

Fetch all exams available, or display a requested subject/correction.

#### URI Parameters
| Name | Type | Description |
|---|---|---|
| `id` | string | (optional) The ID of the subject to display (append '-c' for correction) |

#### Body Parameters
No body parameter.

#### Responses

| HTTP response code | Data type | Description |
|---|---|---|
| 200 | `array` | If no error is made. |
| 404 | `UserError` | The given ID does not correspond to an existing subject or correction. |
| 405 | `UserError` | HTTP method is not allowed. |
| 500 | `ServerError` | Unidentified server error. |

#### Response format

The data type is an array of exams if no ID is given (see below), or a display of the PDF (MIME: application/pdf).

```json5
{
  "metadata": { /*...*/ },
  "data": {
    "type": "array",
    "size": 1,
    "values": [
      {
        "type": "exam",
        "id": "25-1NSI-05",
        "title": "Introduction à Python",
        "theme": 1,
        "update_time": {
          "type": "date_time",
          "value": "2026-11-02 11:37:41"
        },
        "skills": {
          "type": "array",
          "size": 2,
          "values": [
            "Briques élémentaires de Python",
            "Validation d'expressions"
          ]
        },
        "subjects": {
          "type": "array",
          "size": 2,
          "values": [
            {
              "type": "subject",
              "id": "25-1NSI-05A",
              "has_correction": true
            },
            {
              "type": "subject",
              "id": "25-1NSI-05B",
              "has_correction": false
            }
          ]
        }
      }
    ]
  }
}
```
