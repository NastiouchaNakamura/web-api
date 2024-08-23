<?php
namespace App\Model\GeoJson;

class GeoJsonMultiPolygon implements GeoJsonGeometry {
    public array $polygons = array();

    public function toGeoJson(): array {
        return [
            "type" => "MultiPolygon",
            "coordinates" => $this->toCoordinates()
        ];
    }

    public function toCoordinates(): array {
        $partialJson = array();
        foreach ($this->polygons as $polygon) {
            if ($polygon instanceof GeoJsonMultiRingPolygon) {
                $partialJson[] = $polygon->toCoordinates();
            } elseif ($polygon instanceof GeoJsonPolygon) {
                $partialJson[] = [$polygon->toCoordinates()];
            }
        }
        return $partialJson;
    }
}