<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Challenge;
use App\Model\Nsi\Request;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Vérifications des paramètres
    if (!isset($_GET["id"])) {
        echo RestResponse::get(400, UserError::new("Missing GET parameter 'id' : challenge ID as string 'id' must be provided"));
        exit();
    }

    if (!isset($_GET["flag"])) {
        echo RestResponse::get(400, UserError::new("Missing GET parameter 'flag' : answer flag as string 'flag' must be provided"));
        exit();
    }

    // Ni GET ni POST ?
    if ($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST') {
        echo RestResponse::get(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
        exit();
    }

    // Méthode POST ? Si oui alors authentification requise
    $authenticated = false;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Authentification standard 'Basic' (RFC-7617)
        // https://datatracker.ietf.org/doc/html/rfc7617
        $realm = "NSI";

        // Y a-t-il un en-tête d'autorisation ?
        if (!array_key_exists("authorization", getallheaders()) && !array_key_exists("Authorization", getallheaders())) {
            header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
            echo RestResponse::get(401, UserError::new("POST method require authentication"));
            exit();
        }
        
        // Est-ce que l'en-tête d'autorisation a le bon format ?
        $authorization = array_key_exists("authorization", getallheaders()) ? getallheaders()["authorization"] : getallheaders()["Authorization"];
        if (!str_starts_with($authorization, "Basic ")) {
            header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
            echo RestResponse::get(401, UserError::new("Bad HTTP authentication scheme : please use 'Basic' HTTP authentication scheme format"));
            exit();
        }

        // Est-ce que les identifiants sont correctement encodés en base64 ?
        $credentials = base64_decode(substr($authorization, 6), true);
        if ($credentials == false) {
            header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
            echo RestResponse::get(401, UserError::new("Bad credential format : please use 'Basic' HTTP authentication scheme format"));
            exit();
        }

        // Est-ce que ces identifiants sont corrects ?
        [$username, $password] = explode(":", $credentials);
        // Check authentication TODO
        if ($username != "toto" || $password != "admin") {
            header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
            echo RestResponse::get(401, UserError::new("Bad credentials"));
            exit();
        }

        $authenticated = true;
    }
    
    // Récupération du challenge
    $challenge = Challenge::fetch($_GET["id"]);

    // Le challenge existe-t-il ?
    if (is_null($challenge)) {
        echo RestResponse::get(404, UserError::new("Challenge of id '$id' not found"));
        exit();
    }

    // Limite de requêtes (empêcher le bruteforce)
    if (Request::has_requested((new DateTime())->sub(DateInterval::createFromDateString('1 minute')), $_SERVER['REMOTE_ADDR'])) {
        echo RestResponse::get(429, UserError::new("Too many requests ! Please wait one full minute between each request"));
        exit();
    }
    
    // Enregistrement
    Request::save($_SERVER['REMOTE_ADDR']);

    // Vérif du flag
    $good_guess = $challenge->flag == $_GET["flag"];

    // Réponse HTTP
    echo RestResponse::get(200, $good_guess);

    // Enregistrement de l'étoile
    if ($good_guess && $authenticated) {
        // Save result TODO
    }

    exit();
} catch (Exception $exception) {
    echo RestResponse::get(500, ServerError::new($exception));
}
