<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Vérifications des paramètres
        if (!isset($_GET["seed"]) || !isset($_GET["min"]) || !isset($_GET["max"]))
            RestResponse::set(400, UserError::new("Missing one or more necessary parameters : string 'seed' must be provided, integer 'min' must be provided and integer 'max' must be provided"));
        if (!is_numeric($_GET["min"]))
            RestResponse::set(400, UserError::new("Invalid parameter: 'min' must be an integer value"));
        if (!is_numeric($_GET["max"]))
            RestResponse::set(400, UserError::new("Invalid parameter: 'max' must be an integer value"));
        if (intval($_GET["min"]) > intval($_GET["max"]))
            RestResponse::set(400, UserError::new("Invalid parameter: 'max' must be greater than 'min'"));
        if (isset($_GET["n"]) && (!is_numeric($_GET["n"]) or intval($_GET["n"]) <= 0))
            RestResponse::set(400, UserError::new("Invalid parameter: 'n' must be a positive integer value"));
        if (isset($_GET["n"]) && intval($_GET["n"]) > 1000)
            RestResponse::set(400, UserError::new("Invalid parameter: Maximum value count is 1000 per request"));
        
        // Opérations
        srand(intval(hash("sha256", $_GET["seed"]), 16));
        $n = isset($_GET["n"]) ? intval($_GET["n"]) : 1;
        $random = array();
        for ($i = 0; $i < $n; $i++)
            $random[] = rand(intval($_GET["min"]), intval($_GET["max"]));

        RestResponse::set(200, $random);
        
    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Exception $exception) {
    RestResponse::set(500, ServerError::new($exception));
}
