<?php
namespace App\Model;

use App\Request\SqlRequest;

class RoomGroup {
    // Fetcheur statique
    public static function fetchById(array $ids): array {
        if (count($ids) == 0) return array();

        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building,
    name
FROM
    api_university_room_groups
WHERE id IN $unpreparedArray;
EOF
        )->execute($ids);

        $count = 0;
        $roomGroupIdToCount = array();
        $buildingIds = array();
        $roomGroups = array();

        foreach ($responses as $response) {
            $roomGroup = new RoomGroup();

            $roomGroup->roomGroupId = $response->id;
            $roomGroup->buildingId = $response->id_building;
            $roomGroup->name = $response->name;
            $roomGroup->rooms = array();

            $roomGroupIdToCount[$roomGroup->roomGroupId] = $count++;

            $buildingIds[] = $roomGroup->buildingId;

            $roomGroups[] = $roomGroup;
        }

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    name,
    id_room_group
FROM
    api_university_rooms
WHERE id_room_group IN $unpreparedArray;
EOF
        )->execute($ids);

        foreach ($responses as $response) {
            $roomGroups[$roomGroupIdToCount[$response->id_room_group]]->rooms[] = $response->id;
        }

        $unpreparedArrayBuildings = "(?" . str_repeat(",?", count($buildingIds) - 1) . ")";

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building_group
FROM
    api_university_buildings
WHERE id IN $unpreparedArrayBuildings;
EOF
        )->execute($buildingIds);

        $remainingRoomGroups = range(0, count($roomGroups) - 1);
        foreach ($remainingRoomGroups as $remainingRoomGroup) {
            foreach ($responses as $response) {
                if ($roomGroups[$remainingRoomGroup]->buildingId == $response->id) {
                    $roomGroups[$remainingRoomGroup]->buildingGroupId = $response->id_building_group;
                    unset($remainingRoomGroups[$remainingRoomGroup]);
                    break;
                }
            }
        }

        return $roomGroups;
    }

    // Constructeurs
    private function __construct() {}

    // Attributs
    private int $roomGroupId;
    private int $buildingId;
    private string $buildingGroupId;
    private string $name;
    private array $rooms;

    // Getteurs
    public function getRoomGroupId(): int {
        return $this->roomGroupId;
    }

    public function getBuildingId(): int {
        return $this->buildingId;
    }

    public function getBuildingGroupId(): string {
        return $this->buildingGroupId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRooms(): array {
        return $this->rooms;
    }
}
