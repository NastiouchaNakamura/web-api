<?php
namespace App\Model\University;

use App\Request\SqlRequest;

class RoomGroup {
    // Attributs
    public int $roomGroupId;
    public int $buildingId;
    public string|null $buildingGroupId;
    public string $name;
    public array $rooms;

    // Fetcheur statique
    public static function fetchById(array $ids): array {
        // S'il n'y a aucun ID dans l'array, on ne revoit aucun résultat donc un array vide.
        if (count($ids) == 0) return array();

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID en paramètres.
        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        // Requête des groupes de salles d'ID fourni.
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

        // Initialisation du tableau de retour de méthode.
        $buildingIds = array();
        $roomGroups = array();

        // Pour chaque groupe de salles dans la réponse...
        foreach ($responses as $response) {
            // Instanciation de la classe groupe de salles.
            $roomGroup = new RoomGroup();

            // Mise à jour des attributs accessibles.
            $roomGroup->roomGroupId = $response->id;
            $roomGroup->buildingId = $response->id_building;
            $roomGroup->name = $response->name;

            // Initialisation des attributs à aller chercher.
            $roomGroup->buildingGroupId = null;
            $roomGroup->rooms = array();

            // Stockage dans le tableau de retour de méthode.
            $roomGroups[$roomGroup->roomGroupId] = $roomGroup;

            // Pour pouvoir chercher l'ID du groupe de bâtiments, stockage de l'ID du bâtiment dans une variable.
            $buildingIds[] = $roomGroup->buildingId;
        }

        // Requête des salles d'ID de groupes de salle fournis.
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

        // Pour chaque salle dans la réponse...
        foreach ($responses as $response) {
            // Stockage du groupe de salle dans l'attribut groupes de salles du bâtiment selon l'ID du bâtiment.
            $roomGroups[$response->id_room_group]->rooms[] = $response->id;
        }

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID de bâtiments.
        $unpreparedArrayBuildings = "(?" . str_repeat(",?", count($buildingIds) - 1) . ")";

        // Requête des bâtiments d'ID de bâtiment fourni.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building_group
FROM
    api_university_buildings
WHERE id IN $unpreparedArrayBuildings;
EOF
        )->execute($buildingIds);

        // Pour chaque groupe de salles...
        foreach ($roomGroups as $roomGroup) {
            // On parcourt tous les bâtiments récupérés...
            foreach ($responses as $response) {
                // Si c'est le bon bâtiment...
                if ($roomGroup->buildingId == $response->id) {
                    // Alors on récupère l'ID du groupe de bâtiment.
                    $roomGroup->buildingGroupId = $response->id_building_group;

                    // On passe au prochain groupe de salles.
                    break;
                }
            }
        }

        // Retour du tableau de retour de méthode.
        return $roomGroups;
    }
}
