<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Error;
use App\Model\InternalError;
use App\Model\University\Building;
use App\Response\RestResponse;

try {
    if (isset($_GET["id"])) {
        // Vérification des paramètres...
        if (preg_match("/^([0-9]+)(,[0-9]+)*$/", $_GET["id"]) == 0) {
            echo RestResponse::get(400, [Error::new("Malformed GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
        } else {
            if (isset($_GET["geoJson"])) {
                echo RestResponse::get(200, Building::fetchById(explode(",", $_GET["id"]), true));
            } else {
                echo RestResponse::get(200, Building::fetchById(explode(",", $_GET["id"]), false));
            }
        }
    } else {
        echo RestResponse::get(400, [Error::new("Missing GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, [InternalError::new($exception)]);
}
