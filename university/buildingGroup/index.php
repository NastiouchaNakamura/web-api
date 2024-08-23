<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\BuildingGroup;
use App\Model\Error;
use App\Model\InternalError;
use App\Response\RestResponse;

try {
    if (isset($_GET["id"])) {
        // Vérification des paramètres
        if (preg_match("/^([a-z]+)(,[a-z]+)*$/", $_GET["id"]) == 0) {
            echo RestResponse::get(400, [Error::new("Malformed GET parameter 'id': all IDs must be lowercase English alphabet words, separated by commas ',' without spaces if more than one; corresponding regex: /^([a-z]+)(,[a-z]+)*$/")]);
        } else {
            echo RestResponse::get(200, BuildingGroup::fetchById(explode(",", $_GET["id"])));
        }
    } else {
        echo RestResponse::get(200, BuildingGroup::fetchAllId());
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, [InternalError::new($exception)]);
}
