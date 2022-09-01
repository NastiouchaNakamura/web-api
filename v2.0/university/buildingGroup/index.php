<?php
const ROOT = '../../';
require ROOT . 'controleur.php';

use App\Model\BuildingGroup;
use App\Response\RestResponse;

// Appel de la réponse
try {
    echo RestResponse::get(200, BuildingGroup::fetch("cost"));
} catch (Exception $exception) {
    echo RestResponse::get();
}
echo RestResponse::get(200, BuildingGroup::fetch("cost"));
