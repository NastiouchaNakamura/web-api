<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Website;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Récupération des sites
        $websites = Website::fetch_websites();
        
        // Réponse HTTP
        RestResponse::set(200, $websites);
    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Throwable $throwable) {
    echo $throwable;
    RestResponse::set(500, ServerError::new($throwable));
}
