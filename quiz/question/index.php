<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\UserError;
use App\Model\ServerError;
use App\Model\Quiz\Question;
use App\Response\RestResponse;

try {
    if (isset($_GET["id"])) {
        // Vérification des paramètres...
        if (preg_match("/^([0-9]+)(,[0-9]+)*$/", $_GET["id"]) == 0) {
            echo RestResponse::get(400, [UserError::new("Malformed GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
        } else {
            echo RestResponse::get(200, Question::fetchById(explode(",", $_GET["id"])));
        }
    } else {
        echo RestResponse::get(400, [UserError::new("Missing GET parameter 'id': all IDs must be numbers, separated by commas ',' without spaces if more than one; corresponding regex: /^([0-9]+)(,[0-9]+)*$/")]);
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, [ServerError::new($exception)]);
}
