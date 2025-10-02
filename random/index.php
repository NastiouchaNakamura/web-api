<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\UserError;
use App\Model\ServerError;
use App\Model\University\Building;
use App\Response\RestResponse;

try {
    if (isset($_GET["seed"]) and isset($_GET["min"]) and isset($_GET["max"])) {
        // Vérification des paramètres...
        if (!is_numeric($_GET["min"])) {
            echo RestResponse::get(400, UserError::new("Invalid parameter: 'min' must be an integer value"));
        } elseif (!is_numeric($_GET["max"])) {
            echo RestResponse::get(400, UserError::new("Invalid parameter: 'max' must be an integer value"));
        } else {
            srand(intval(hash("sha256", $_GET["seed"]), 16));
            $min = intval($_GET["min"]);
            $max = intval($_GET["max"]);

            if ($min > $max) {
                echo RestResponse::get(400, UserError::new("Invalid parameter: 'max' must be greater than 'min'"));
            }

            if (isset($_GET["n"])) {
                if (!is_numeric($_GET["n"]) or intval($_GET["n"]) <= 0) {
                    echo RestResponse::get(400, UserError::new("Invalid parameter: 'n' must be a positive integer value"));
                } else {
                    $n = intval($_GET["n"]);
                }
            } else {
                $n = 1;
            }

            $random = array();

            for ($i = 0; $i < $n; $i++) {
                $random[] = rand($min, $max);
            }

            echo RestResponse::get(200, $random);
        }
    } else {
        echo RestResponse::get(400, UserError::new("Missing one or more necessary parameters : string 'seed' must be provided, integer 'min' must be provided and integer 'max' must be provided"));
    }
} catch (Exception $exception) {
    echo RestResponse::get(500, ServerError::new($exception));
}
