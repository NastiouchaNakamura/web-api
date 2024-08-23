<?php
namespace App\Model\GeoJson;

class GeoJsonPolygon implements GeoJsonGeometry {
    public array $points = array();

    public function toGeoJson(): array {
        return [
            "type" => "Polygon",
            "coordinates" => [$this->toCoordinates()]
        ];
    }

    public function toCoordinates(): array {
        return $this->toCounterClockwiseCoordinates();
    }

    public function toCounterClockwiseCoordinates(): array { // For exterior rings
        if (count($this->points) < 2) {
            return $this->points;
        }

        // Déterminer le point le plus haut càd avec la plus grande C2.
        $highestPointIndex = 0;
        for ($i = 0; $i < count($this->points); $i++) {
            if ($this->points[$highestPointIndex]->c2 < $this->points[$i]->c2)
                $highestPointIndex = $i;
        }

        $partialJson = [$this->points[$highestPointIndex]->toCoordinates()];
        for ($i = ($highestPointIndex + 1) % count($this->points); $i != $highestPointIndex; $i = ($i + 1) % count($this->points)) {
            $partialJson[] = $this->points[$i]->toCoordinates();
        }

        if ($partialJson[0][0] != $partialJson[count($partialJson) - 1][0] || $partialJson[0][1] != $partialJson[count($partialJson) - 1][1]) {
            $partialJson[] = $partialJson[0];
        }

        // Déterminer le point le plus à gauche parmi les voisins du point le plus haut pour le sens de rotation.
        // Plus petit = plus au gauche.
        $previousIndex = $highestPointIndex == 0 ? count($this->points) - 1 : $highestPointIndex - 1;
        $nextIndex = $highestPointIndex + 1;
        $reverse = $this->points[$previousIndex]->c1 < $this->points[$nextIndex]->c1;
        if ($reverse) $partialJson = array_reverse($partialJson);

        return $partialJson;
    }

    public function toClockwiseCoordinates(): array { // For interior rings
        return array_reverse($this->toCounterClockwiseCoordinates());
    }
}