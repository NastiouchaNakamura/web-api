<?php
namespace App\Model\GeoJson;

class GeoJsonMultiRingPolygon implements GeoJsonGeometry {
    public array $exteriorRings = array();
    public array $interiorRings = array();

    public function toGeoJson(): string {
        return json_encode([
            "type" => "Polygon",
            "coordinates" => $this->toCoordinates()
        ]);
    }

    public function toCoordinates(): array {
        $partialJson = array();
        foreach ($this->exteriorRings as $polygon)
            $partialJson[] = $polygon->toCounterClockwiseCoordinates();
        foreach ($this->interiorRings as $polygon)
            $partialJson[] = $polygon->toClockwiseCoordinates();
        return $partialJson;
    }
}