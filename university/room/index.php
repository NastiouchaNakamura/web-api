<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\InternalError;
use App\Model\Room;
use App\Model\Error;
use App\Response\RestResponse;

try {
    if (isset($_GET["id"]) && isset($_GET["name"])) {
        echo RestResponse::get(400, [Error::new("Ambiguous GET parameters: only one of 'id' and 'name' must be passed")]);
    } elseif (isset($_GET["id"])) {
        // Vérification des paramètres
        if (preg_match("/^([0-9]+)(,[0-9]+)*$/", $_GET["id"]) == 0) {
            echo RestResponse::get(400, [Error::new("Malformed GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
        } else {
            echo RestResponse::get(200, Room::fetchById(explode(",", $_GET["id"])));
        }
    } elseif (isset($_GET["name"])) {
        echo RestResponse::get(200, Room::fetchByName(str_replace( ["\n", "\t", " ", ".", "(", ")"], "", strtolower($_GET["name"]))));
    } else {
        echo RestResponse::get(400, [Error::new("Missing GET parameter: 'id' or 'name'")]);
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, [InternalError::new($exception)]);
}
