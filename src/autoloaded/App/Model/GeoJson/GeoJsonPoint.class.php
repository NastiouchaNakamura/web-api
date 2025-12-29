<?php
namespace App\Model\GeoJson;

class GeoJsonPoint implements GeoJsonGeometry {
    public float $c1;
    public float $c2;

    public function toGeoJson(): array {
        return [
            "type" => "Point",
            "coordinates" => [$this->c1, $this->c2]
        ];
    }

    public function toCoordinates(): array {
        return [$this->c1, $this->c2];
    }
}