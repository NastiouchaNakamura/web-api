<?php
namespace App\Model;

use App\Request\SqlRequest;

class Building {
    // Fetcheur statique
    public static function fetchById(array $ids): array {
        // S'il n'y a aucun ID dans l'array, on ne revoit aucun résultat donc un array vide.
        if (count($ids) == 0) return array();

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID en paramètres.
        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        // Requête des bâtiments d'ID fourni.
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

        // Initialisation du tableau de retour de méthode.
        $buildings = array();

        // Pour chaque bâtiment dans la réponse...
        foreach ($responses as $response) {
            // Instanciation de la classe bâtiment.
            $building = new Building();

            // Mise à jour des attributs accessibles.
            $building->buildingId = $response->id;
            $building->buildingGroupId = $response->id_building_group;
            $building->shortLabel = $response->short_label;
            $building->longLabel = $response->long_label;

            // Initialisation des attributs à aller chercher.
            $building->roomGroups = array();
            $building->geoJson = null;

            // Stockage dans le tableau de retour de méthode.
            $buildings[$building->buildingId] = $building;
        }

        // Requête des groupes de salles d'ID de bâtiment fourni.
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

        // Pour chaque groupe de salle dans la réponse...
        foreach ($responses as $response) {
            // Stockage du groupe de salle dans l'attribut groupes de salles du bâtiment selon l'ID du bâtiment.
            $buildings[$response->id_building]->roomGroups[] = $response->id;
        }

        // Retour du tableau de retour de méthode.
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
