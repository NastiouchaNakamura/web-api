<?php
namespace App\Model;

use App\Request\SqlRequest;

class BuildingGroup {
    // Fetcheur statique
    public static function fetch($id): array {
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
WHERE id=?;
EOF
        )->execute([$id]);

        $buildingGroups = array();

        foreach ($responses as $response) {
            $buildingGroup = new BuildingGroup();

            $buildingGroup->id = $response->id;
            $buildingGroup->legend = $response->legend;
            $buildingGroup->name = $response->name;
            $buildingGroup->color = [$response->color_r, $response->color_g, $response->color_b];

            $response = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    api_university_buildings
WHERE idBuildingGroups=?;
EOF
            )->execute([$response->id]);

            $buildingGroup->buildings = array();
            foreach ($response as $building) {
                $buildingGroup->buildings[] = $building->id;
            }

            $buildingGroups[] = $buildingGroup;
        }

        return $buildingGroups;
    }

    // Constructeurs
    private function __construct() {}

    // Attributs
    private string $id;
    private string $legend;
    private string $name;
    private array $color;
    private array $buildings;

    // Getteurs
    public function getId(): string {
        return $this->id;
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
