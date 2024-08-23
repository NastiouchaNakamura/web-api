<?php

namespace App\Model\GeoJson;

interface GeoJsonGeometry {
    public function toGeoJson();
    public function toCoordinates();
}