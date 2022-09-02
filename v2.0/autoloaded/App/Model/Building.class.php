<?php
namespace App\Model;

use App\Request\SqlRequest;

class Building {
    // Fetcheur statique
    public static function fetchById(array $ids): array {
        if (count($ids) == 0) return array();

        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    short_label,
    long_label,
    id_building_group
FROM
    api_university_buildings
WHERE id IN $unpreparedArray;
EOF
        )->execute($ids);

        $count = 0;
        $buildingIdToCount = array();
        $buildings = array();

        foreach ($responses as $response) {
            $building = new Building();

            $building->buildingId = $response->id;
            $building->buildingGroupId = $response->id_building_group;
            $building->shortLabel = $response->short_label;
            $building->longLabel = $response->long_label;
            $building->roomGroups = array();
            $building->geoJson = null;

            $buildingIdToCount[$building->buildingId] = $count++;

            $buildings[] = $building;
        }

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    name,
    id_building
FROM
    api_university_room_groups
WHERE id_building IN $unpreparedArray;
EOF
        )->execute($ids);

        foreach ($responses as $response) {
            $buildings[$buildingIdToCount[$response->id_building]]->roomGroups[] = $response->id;
        }

        return $buildings;
    }

    // Constructeurs
    private function __construct() {}

    // Attributs
    private int $buildingId;
    private string $buildingGroupId;
    private string $shortLabel;
    private string $longLabel;
    private array $roomGroups;
    private array|null $geoJson;

    // Getteurs
    public function getBuildingId(): int {
        return $this->buildingId;
    }

    public function getBuildingGroupId(): string {
        return $this->buildingGroupId;
    }

    public function getShortLabel(): string {
        return $this->shortLabel;
    }

    public function getLongLabel(): string {
        return $this->longLabel;
    }

    public function getRoomGroups(): array {
        return $this->roomGroups;
    }

    public function getGeoJson(): array|null {
        return $this->geoJson;
    }
}
