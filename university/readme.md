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

#### Response schema
```json
{
  
}
```
