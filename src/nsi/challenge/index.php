<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Challenge;
use App\Model\Nsi\Profile;
use App\Model\Nsi\Request;
use App\Model\Nsi\Star;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Vérifications des paramètres
        if (!isset($_GET["id"]))
            RestResponse::set(400, UserError::new("Missing URI parameter 'id': challenge ID as string 'id' must be provided"));

        if (!isset($_GET["flag"]))
            RestResponse::set(400, UserError::new("Missing URI parameter 'flag': answer flag as string 'flag' must be provided"));

        // Récupération du challenge
        $challenge = Challenge::fetch($_GET["id"]);

        // Le challenge existe-t-il ?
        if (is_null($challenge))
            RestResponse::set(404, UserError::new("Challenge of id '$id' not found"));

        // Y a-t-il authentification ?
        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) { // Serveur APACHE (voir .htaccess)
            $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (array_key_exists("authorization", getallheaders())) { // Serveur PHP & client mkdocs notamment
            $authorization = getallheaders()["authorization"];
        } elseif (array_key_exists("Authorization", getallheaders())) { // Serveur PHP & client classique
            $authorization = getallheaders()["Authorization"];
        }
        
        // Vérifications d'authentification.
        if (isset($authorization)) {
            // Authentification standard 'Basic' (RFC-7617)
            // https://datatracker.ietf.org/doc/html/rfc7617
            $realm = "NSI";
            
            // Est-ce que l'en-tête d'autorisation a le bon format ?
            if (!str_starts_with($authorization, "Basic ")) {
                header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
                RestResponse::set(401, UserError::new("Bad HTTP authentication scheme: please use 'Basic' HTTP authentication scheme format"));
            }

            // Est-ce que les identifiants sont correctement encodés en base64 ?
            $credentials = base64_decode(substr($authorization, 6), true);
            if ($credentials == false) {
                header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
                RestResponse::set(401, UserError::new("Bad credential format: please use 'Basic' HTTP authentication scheme format"));
            }

            // Est-ce que ces identifiants sont corrects ?
            [$username, $password] = explode(":", $credentials);
            $profile = Profile::fetchByUsername($username);
            if (!password_verify($password, $profile->pw_hash)) {
                header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
                RestResponse::set(401, UserError::new("Bad credentials"));
            }
        }

        // Limite de requêtes (empêcher le bruteforce)
        if (isset($profile)) {
            if (Request::has_requested_authentified((new DateTime())->sub(DateInterval::createFromDateString('1 minute')), $profile->username))
                RestResponse::set(429, UserError::new("Too many requests! Please wait one full minute between each request"));
        } else {
            if (Request::has_requested_anonymously((new DateTime())->sub(DateInterval::createFromDateString('1 minute')), $_SERVER['REMOTE_ADDR']))
                RestResponse::set(429, UserError::new("Too many requests! Please wait one full minute between each request"));
        }
        
        // Enregistrement de la requête
        Request::save($_SERVER['REMOTE_ADDR'], $_GET["id"], $profile->username ?? null);

        // Vérif du flag
        $good_guess = $challenge->flag == $_GET["flag"];

        // Enregistrement de l'étoile
        if ($good_guess && isset($profile) && !Star::has_been_obtained($profile->username, $challenge->id)) {
            Star::save($profile->username, $challenge->id, "BASIC");
        }

        // Réponse HTTP
        RestResponse::set(200, $good_guess);
        
    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Throwable $throwable) {
    RestResponse::set(500, ServerError::new($throwable));
}
