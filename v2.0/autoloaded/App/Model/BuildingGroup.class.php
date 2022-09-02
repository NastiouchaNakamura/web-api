<?php
namespace App\Model;

use App\Request\SqlRequest;

class BuildingGroup {
    // Fetcheur statique
    public static function fetchAllId(): array {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    api_university_building_groups;
EOF
        )->execute();

        $buildingGroupIds = array();

        foreach ($responses as $response) {
            $buildingGroupIds[] = $response->id;
        }

        return $buildingGroupIds;
    }

    public static function fetchById(array $ids): array {
        if (count($ids) == 0) return array();

        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    legend,
    name,
    color_r,
    color_g,
    color_b
FROM
    api_university_building_groups
WHERE id IN $unpreparedArray;
EOF
        )->execute($ids);

        $count = 0;
        $buildingGroupIdToCount = array();
        $buildingGroups = array();

        foreach ($responses as $response) {
            $buildingGroup = new BuildingGroup();

            $buildingGroup->buildingGroupId = $response->id;
            $buildingGroup->legend = $response->legend;
            $buildingGroup->name = $response->name;
            $buildingGroup->color = [$response->color_r, $response->color_g, $response->color_b];
            $buildingGroup->buildings = array();

            $buildingGroupIdToCount[$buildingGroup->buildingGroupId] = $count++;

            $buildingGroups[] = $buildingGroup;
        }

        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building_group
FROM
    api_university_buildings
WHERE id_building_group IN $unpreparedArray;
EOF
        )->execute($ids);

        foreach ($responses as $response) {
            $buildingGroups[$buildingGroupIdToCount[$response->id_building_group]]->buildings[] = $response->id;
        }

        return $buildingGroups;
    }

    // Constructeurs
    private function __construct() {}

    // Attributs
    private string $buildingGroupId;
    private string $legend;
    private string $name;
    private array $color;
    private array $buildings;

    // Getteurs
    public function getBuildingGroupId(): string {
        return $this->buildingGroupId;
    }

    public function getLegend(): string {
        return $this->legend;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getColor(): array {
        return [
            "rgb" => $this->color,
            "hex" => sprintf("#%02x%02x%02x", $this->color[0], $this->color[1], $this->color[2])
        ];
    }

    public function getBuildings(): array {
        return $this->buildings;
    }
}
