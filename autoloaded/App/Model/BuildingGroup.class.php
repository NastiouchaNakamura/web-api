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
        // S'il n'y a aucun ID dans l'array, on ne revoit aucun résultat donc un array vide.
        if (count($ids) == 0) return array();

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID en paramètres.
        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        // Requête des groupes de bâtiments d'ID fourni.
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

        // Initialisation du tableau de retour de méthode.
        $buildingGroups = array();

        // Pour chaque bâtiment dans la réponse...
        foreach ($responses as $response) {
            // Instanciation de la classe groupe de bâtiments
            $buildingGroup = new BuildingGroup();

            // Mise à jour des attributs accessibles.
            $buildingGroup->buildingGroupId = $response->id;
            $buildingGroup->legend = $response->legend;
            $buildingGroup->name = $response->name;
            $buildingGroup->color = [$response->color_r, $response->color_g, $response->color_b];

            // Initialisation des attributs à aller chercher.
            $buildingGroup->buildings = array();

            // Stockage dans le tableau de retour de méthode.
            $buildingGroups[$buildingGroup->buildingGroupId] = $buildingGroup;
        }

        // Requête des bâtiments d'ID de groupe de bâtiments fourni.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building_group
FROM
    api_university_buildings
WHERE id_building_group IN $unpreparedArray;
EOF
        )->execute($ids);

        // Pour chaque bâtiment dans la réponse...
        foreach ($responses as $response) {
            // Stockage du bâtiment dans l'attribut bâtiments du groupe de bâtiments selon l'ID du groupe de bâtiments.
            $buildingGroups[$response->id_building_group]->buildings[] = $response->id;
        }

        // Retour du tableau de retour de méthode.
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
