# University

Retrieves the classrooms (and every other room) of the University of Orléans from a static database.

Rooms are grouped in room groups (e.g. a building's floor). Room groups are grouped in buildings. Buildings are grouped
in building groups (e.g. a campus).

## Endpoints

### Building group

Fetches the requested building groups containing all their buildings' IDs. If no ID is given then it fetches all
building groups IDs instead. It is the most encompassing object of the tool.

#### Parameters
| Name | Type   | Description                                                                                                          |
|------|--------|----------------------------------------------------------------------------------------------------------------------|
| `id` | string | ID of the building group to fetch. Multiple IDs can be requested at once using the comma character `,` as separator. |

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
      "deg",
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

Or, if `id` is provided:

```json5
{
  "metadata": {
    // ...
  },
  "data": {
    "type": "array",
    "size": 1,
    "data": [
      {
        "building_group_id": "st",
        "buildings": {
          "type": "array",
          "size": 10,
          "data": [
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11
          ]
        },
        "color": {
          "b": 145,
          "g": 127,
          "hex": "#1b7f91",
          "r": 27,
          "type": "color"
        },
        "legend": "UFR ST",
        "name": "UFR Sciences et Techniques",
        "type": "building_group"
      }
    ]
  }
}
```

### Buildings

Fetches the requested buildings containing all their room groups' IDs. To minimize payload's size, GeoJson data are not
fetched by default.

#### Parameters
| Name     | Type   | Description                                                                                                    |
|----------|--------|----------------------------------------------------------------------------------------------------------------|
| `id`     | string | ID of the building to fetch. Multiple IDs can be requested at once using the comma character `,` as separator. |
| `geoJson | void   | Whether GeoJson data is fetched. This cas be very big.                                                         |

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
    "data": [
      {
        "building_group_id": "st",
        "buildings": {
          "type": "array",
          "size": 10,
          "data": [
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11
          ]
        },
        "color": {
          "b": 145,
          "g": 127,
          "hex": "#1b7f91",
          "r": 27,
          "type": "color"
        },
        "legend": "UFR ST",
        "name": "UFR Sciences et Techniques",
        "type": "building_group"
      }
    ]
  }
}
```

### Room groups

Fetches the requested room groups containing all their rooms' IDs.

#### Parameters
| Name     | Type   | Description                                                                                                      |
|----------|--------|------------------------------------------------------------------------------------------------------------------|
| `id`     | string | ID of the room group to fetch. Multiple IDs can be requested at once using the comma character `,` as separator. |

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
    "data": [
      {
        "building_group_id": "osuc",
        "building_id": 1,
        "name": "Rez-de-chaussée",
        "room_group_id": 1,
        "rooms": {
          "type": "array",
          "size": 9,
          "data": [
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9
          ]
        },
        "type": "room_group"
      }
    ]
  }
}
```

### Room groups

Fetches the requested room.

#### Parameters
| Name | Type   | Description                                                                                                |
|------|--------|------------------------------------------------------------------------------------------------------------|
| `id` | string | ID of the room to fetch. Multiple IDs can be requested at once using the comma character `,` as separator. |

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
    "data": [
      {
        "building_group_id": "osuc",
        "building_id": 1,
        "name": "E001",
        "room_group_id": 1,
        "room_id": 1,
        "type": "room"
      }
    ]
  }
}
```
