# Random

Gives a certain number of pseudorandom integers from a given seed.

#### Parameters
| Name   | Type    | Description                                                                                                                      |
|--------|---------|----------------------------------------------------------------------------------------------------------------------------------|
| `seed` | string  | Seed used for pseudorandom number generation.                                                                                    |
| `min`  | integer | Smallest possible value of generated pseudorandom integers (included). Must be smaller than `max`.                               |
| `max`  | integer | Largest possible value of generated pseudorandom integers (included). Must be greater than `min`.                                |
| `n`    | integer | (optional) The number of pseudorandom integers to generate, defaults to 1. Must be positive. Limited to 1000 at most per request |

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
    "size": 1,
    "values": [4, 5, 9]
  }
}
```
