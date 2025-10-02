# Random

Gives a certain number of pseudorandom integers from a given seed.

#### Parameters
| Name   | Type    | Description                                                                |
|--------|---------|----------------------------------------------------------------------------|
| `seed` | string  | Seed used for pseudorandom number generation.                              |
| `min`  | integer | Smallest possible value of generated pseudorandom integers (included).     |
| `max`  | integer | Largest possible value of generated pseudorandom integers (included).      |
| `n`    | integer | (optional) The number of pseudorandom integers to generate, defaults to 1. |

#### Exceptions

No exception.

#### Response example

```json5
{
  "metadata": {
    // ...
  },
  "data": {
    "type": "array",
    "size": 10,
    "data": [
      "10",
      "euk",
      "iut",
      "llsh",
      "osuc",
      "poly",
      "res",
      "ru",
      "st",
      "univ"
    ]
  }
}
```
