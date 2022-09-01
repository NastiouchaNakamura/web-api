<?php
const ROOT = '../../';
require ROOT . 'controleur.php';

use App\Model\BuildingGroup;
use App\Model\Error;
use App\Response\RestResponse;

// Appel de la réponse
try {
    echo RestResponse::get(200, BuildingGroup::fetch("cost"));
} catch (Exception $exception) {
    echo RestResponse::get(500, [Error::new($exception->getCode(), $exception->getMessage())]);
}
