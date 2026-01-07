<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Challenge;
use App\Model\Nsi\Profile;
use App\Model\Nsi\Request;
use App\Model\Nsi\Score;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Vérifications des paramètres
        if (!isset($_GET["count"]))
            RestResponse::set(400, UserError::new("Missing URI parameter 'limit': number of profiles (in descending order of number of stars) as positive int 'limit' must be provided"));

        RestResponse::set(200, data: Score::fetch_best_scores(50));
        
    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Throwable $throwable) {
    RestResponse::set(500, ServerError::new($throwable));
}
