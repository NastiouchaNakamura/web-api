<?php
namespace App\Model;

use App\Request\SqlRequest;

class Room {
    // Fetcheurs statique
    public static function fetchByName(string $name): array {
        // Nettoyage de la chaîne.
        $name = str_replace(" ", "", trim($name));

        // Si la chaîne est vide, on ne revoit aucun résultat donc un array vide.
        if (empty($name)) return array();

        // Requête des groupes de salles d'ID fourni.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_room_group,
    name
FROM
    api_university_rooms
WHERE name LIKE ?;
EOF
        )->execute(["%$name%"]);

        if (empty($responses)) {
            return array();
        } else {
            return self::finishFetching($responses);
        }
    }

    public static function fetchById(array $ids): array {
        // S'il n'y a aucun ID dans l'array, on ne revoit aucun résultat donc un array vide.
        if (count($ids) == 0) return array();

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID en paramètres.
        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";

        // Requête des groupes de salles d'ID fourni.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_room_group,
    name
FROM
    api_university_rooms
WHERE id IN $unpreparedArray;
EOF
        )->execute($ids);

        if (empty($responses)) {
            return array();
        } else {
            return self::finishFetching($responses);
        }
    }

    private static function finishFetching(array $responses): array {
        // Initialisation du tableau de retour de méthode.
        $roomGroupIds = array();
        $buildingIds = array();
        $rooms = array();

        // Pour chaque salle dans la réponse...
        foreach ($responses as $response) {
            // Instanciation de la classe salle.
            $room = new Room();

            // Mise à jour des attributs accessibles.
            $room->roomId = $response->id;
            $room->roomGroupId = $response->id_room_group;
            $room->name = $response->name;

            // Initialisation des attributs à aller chercher.
            $room->buildingId = null;
            $room->buildingGroupId = null;

            // Stockage dans le tableau de retour de méthode.
            $rooms[$room->roomId] = $room;

            // Pour pouvoir chercher l'ID du bâtiment, stockage de l'ID du groupe de salles dans une variable.
            $roomGroupIds[] = $room->roomGroupId;
        }

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID de groupes de salles.
        $unpreparedArrayRoomGroups = "(?" . str_repeat(",?", count($roomGroupIds) - 1) . ")";

        // Requête des bâtiments d'ID de bâtiments fournis.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building
FROM
    api_university_room_groups
WHERE id IN $unpreparedArrayRoomGroups;
EOF
        )->execute($roomGroupIds);

        // Pour chaque salle...
        foreach ($rooms as $room) {
            // On parcourt tous les groupes de salles récupérés...
            foreach ($responses as $response) {
                // Si c'est le bon groupe de salle...
                if ($room->roomGroupId == $response->id) {
                    // Alors on récupère l'ID du bâtiment.
                    $room->buildingId = $response->id_building;

                    // Pour pouvoir chercher l'ID du groupe de bâtiments, stockage de l'ID du bâtiment dans une variable.
                    $buildingIds[] = $room->buildingId;

                    // On passe à la prochaine salle.
                    break;
                }
            }
        }

        // Préparation de la chaîne de caractère à insérer dans le SQL, en fonction du nombre d'ID de bâtiments.
        $unpreparedArrayBuildings = "(?" . str_repeat(",?", count($buildingIds) - 1) . ")";

        // Requête des bâtiments d'ID de bâtiments fournis.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    id_building_group
FROM
    api_university_buildings
WHERE id IN $unpreparedArrayBuildings;
EOF
        )->execute($buildingIds);

        // Pour chaque salle...
        foreach ($rooms as $room) {
            // On parcourt tous les bâtiments récupérés...
            foreach ($responses as $response) {
                // Si c'est le bon bâtiment...
                if ($room->buildingId == $response->id) {
                    // Alors on récupère l'ID du bâtiment.
                    $room->buildingGroupId = $response->id_building_group;

                    // On passe à la prochaine salle.
                    break;
                }
            }
        }

        // Retour du tableau de retour de méthode.
        return $rooms;
    }

    // Constructeurs
    private function __construct() {}

    // Attributs
    private int $roomId;
    private int $roomGroupId;
    private int|null $buildingId;
    private string|null $buildingGroupId;
    private string $name;

    // Getteurs
    public function getRoomId(): int {
        return $this->roomId;
    }

    public function getRoomGroupId(): int {
        return $this->roomGroupId;
    }

    public function getBuildingId(): int|null {
        return $this->buildingId;
    }

    public function getBuildingGroupId(): string|null {
        return $this->buildingGroupId;
    }

    public function getName(): string {
        return $this->name;
    }
}