<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Vérifications des paramètres
    
    if (!isset($_GET["seed"]) || !isset($_GET["min"]) || !isset($_GET["max"])) {
        echo RestResponse::get(400, UserError::new("Missing one or more necessary parameters : string 'seed' must be provided, integer 'min' must be provided and integer 'max' must be provided"));
        exit();
    }

    if (!is_numeric($_GET["min"])) {
        echo RestResponse::get(400, UserError::new("Invalid parameter: 'min' must be an integer value"));
        exit();
    }
    
    if (!is_numeric($_GET["max"])) {
        echo RestResponse::get(400, UserError::new("Invalid parameter: 'max' must be an integer value"));
        exit();
    }

    $min = intval($_GET["min"]);
    $max = intval($_GET["max"]);
    if ($min > $max) {
        echo RestResponse::get(400, UserError::new("Invalid parameter: 'max' must be greater than 'min'"));
        exit();
    }

    if (isset($_GET["n"]) && (!is_numeric($_GET["n"]) or intval($_GET["n"]) <= 0)) {
        echo RestResponse::get(400, UserError::new("Invalid parameter: 'n' must be a positive integer value"));
        exit();
    }

    if (isset($_GET["n"]) && intval($_GET["n"]) > 1000) {
        echo RestResponse::get(400, UserError::new("Invalid parameter: Maximum value count is 1000 per request"));
        exit();
    }
    
    // Opérations

    srand(intval(hash("sha256", $_GET["seed"]), 16));
    $n = isset($_GET["n"]) ? intval($_GET["n"]) : 1;

    $random = array();
    for ($i = 0; $i < $n; $i++) {
        $random[] = rand($min, $max);
    }

    echo RestResponse::get(200, $random);
} catch (Exception $exception) {
    echo RestResponse::get(500, ServerError::new($exception));
}
