<?php
namespace App\Model\University;

use App\Model\GeoJson\GeoJsonMultiPolygon;
use App\Model\GeoJson\GeoJsonMultiRingPolygon;
use App\Model\GeoJson\GeoJsonPoint;
use App\Model\GeoJson\GeoJsonPolygon;
use App\Request\SqlRequest;

class Building {
    // Attributes
    public int $buildingId;
    public string $buildingGroupId;
    public string $shortLabel;
    public string $longLabel;
    public array $roomGroups;
    public GeoJsonMultiPolygon | null $geoJson;

    // Static constructors
    public static function fetchById(array $ids, bool $geoJson): array {
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
            $building->geoJson = $geoJson ? new GeoJsonMultiPolygon() : null;

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

        // Si on veut le GeoJSON...
        if ($geoJson) {
            // Requête des polygones
            $sqlPolygons = SqlRequest::new(<<< EOF
SELECT
    id,
    carved,
    polygon_no,
    id_building
FROM
    api_university_polygons
WHERE
    id_building IN $unpreparedArray
ORDER BY carved;
EOF
            )->execute($ids);

            $PolygonsToRequest = array();
            foreach ($sqlPolygons as $sqlPolygon) {
                $PolygonsToRequest[] = $sqlPolygon->id;
            }

            // Requête des coordonnées
            $unpreparedArrayCoordinates = "(?" . str_repeat(",?", count($PolygonsToRequest) - 1) . ")";

            $sqlPoints = SqlRequest::new(<<< EOF
SELECT
    id,
    id_polygon,
    c1,
    c2,
    seq_no
FROM
    api_university_coordinates
WHERE
    id_polygon IN $unpreparedArrayCoordinates
ORDER BY id_polygon, seq_no;
EOF
            )->execute($PolygonsToRequest);

            // Création des polygones
            // L'ordre des points est assuré via ORDER BY seq_no
            // L'ordre des polygones est assuré via ORDER BY id_polygon
            $polygons = array();
            foreach ($sqlPoints as $sqlPoint) {
                $point = new GeoJsonPoint();
                $point->c1 = $sqlPoint->c1;
                $point->c2 = $sqlPoint->c2;

                if (!key_exists($sqlPoint->id_polygon, $polygons))
                    $polygons[$sqlPoint->id_polygon] = new GeoJsonPolygon();
                $polygons[$sqlPoint->id_polygon]->points[] = $point;
            }

            // Création des polygones à plusieurs anneaux
            foreach ($sqlPolygons as $sqlPolygon) {
                if (!key_exists($sqlPolygon->polygon_no, $buildings[$sqlPolygon->id_building]->geoJson->polygons))
                    $buildings[$sqlPolygon->id_building]->geoJson->polygons[$sqlPolygon->polygon_no] = new GeoJsonMultiRingPolygon();

                if ($sqlPolygon->carved == 0) {
                    $buildings[$sqlPolygon->id_building]->geoJson->polygons[$sqlPolygon->polygon_no]->exteriorRings[] = $polygons[$sqlPolygon->id];
                } else {
                    $buildings[$sqlPolygon->id_building]->geoJson->polygons[$sqlPolygon->polygon_no]->interiorRings[] = $polygons[$sqlPolygon->id];
                }
            }
        }

        // Retour du tableau de retour de méthode.
        return $buildings;
    }
}
