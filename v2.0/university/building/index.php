<?php
const ROOT = "../../";
require ROOT . "controleur.php";

use App\Model\Building;
use App\Model\Error;
use App\Response\RestResponse;

try {
    if (isset($_GET["id"])) {
        // Vérification des paramètres
        if (preg_match("/^([0-9]+)(,[0-9]+)*$/", $_GET["id"]) == 0) {
            echo RestResponse::get(400, [Error::new(null, "Malformed GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
        } else {
            echo RestResponse::get(200, Building::fetchById(explode(",", $_GET["id"])));
        }
    } else {
        echo RestResponse::get(400, [Error::new(null, "Missing GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, [Error::new($exception->getCode(), $exception->getMessage())]);
}
