<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Nsi\Profile;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Vérification du MIME du body.
        if (!array_key_exists("Content-Type", getallheaders()) && !array_key_exists("content-type", getallheaders()))
            RestResponse::set(400, UserError::new("Missing 'Content-Type' header"));

        // Vérification du body.
        $body = json_decode(file_get_contents("php://input"));
        if (is_null($body))
            RestResponse::set(400, UserError::new("Malformed JSON in body"));
        
        // Présence username.
        if (!isset($body->username))
            RestResponse::set(400, UserError::new("Missing body parameter 'username'"));
        $username = $body->username;

        // Type username.
        if (!is_string($username))
            RestResponse::set(400, UserError::new("Username must be of type string"));

        // Présence password.
        if (!isset($body->password))
            RestResponse::set(400, UserError::new("Missing body parameter 'password'"));
        $password = $body->password;

        // Type password.
        if (!is_string($password))
            RestResponse::set(400, UserError::new("Password must be of type string"));

        // Présence prénom.
        if (!isset($body->first_name))
            RestResponse::set(400, UserError::new("Missing body parameter 'first_name'"));
        $first_name = $body->first_name;

        // Type prénom.
        if (!is_string($first_name))
            RestResponse::set(400, UserError::new("First name must be of type string"));

        // Présence nom de famille.
        if (!isset($body->last_name))
            RestResponse::set(400, UserError::new("Missing body parameter 'last_name'"));
        $last_name = $body->last_name;

        // Type nom de famille.
        if (!is_string($last_name))
            RestResponse::set(400, UserError::new("Last name must be of type string"));

        // Présence classe.
        if (!isset($body->class))
            RestResponse::set(400, UserError::new("Missing body parameter 'class'"));
        $class = $body->class;

        // Type classe.
        if (!is_string($class))
            RestResponse::set(400, UserError::new("Class must be of type string"));

        // Longueur username.
        if (strlen($username) > 63)
            RestResponse::set(400, UserError::new("Username too long: must be less than 64 characters"));

        // Format username.
        foreach (str_split($username) as $b)
            if (ord($b) < 0x21 || ord($b) > 0x7E || ord($b) == 0x3A)
                RestResponse::set(400, UserError::new("Bad username format: characters must be non-whitespace ASCII except ':' (codepoint 0x3A)"));

        // Longueur password.
        if (strlen($password) > 63)
            RestResponse::set(400, UserError::new("Password too long: must be less than 64 bytes"));

        // Longueur prénom.
        if (strlen($first_name) > 255)
            RestResponse::set(400, UserError::new("First name too long: must be less than 256 bytes"));

        // Longueur nom de famille.
        if (strlen($last_name) > 255)
            RestResponse::set(400, UserError::new("Last name too long: must be less than 256 bytes"));

        // Longueur classe.
        if (strlen($class) > 255)
            RestResponse::set(400, UserError::new("Class too long: must be less than 256 bytes"));

        // Déjà pris ?
        if (!is_null(Profile::fetchByUsername($username)))
            RestResponse::set(409, UserError::new("Username '$username' is already used"));

        // Création !
        Profile::create($username, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $first_name, $last_name, $class);
        RestResponse::set(201, null);

    } elseif ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
        // Vérification du MIME du body.
        if (!array_key_exists("Content-Type", getallheaders()) && !array_key_exists("content-type", getallheaders()))
            RestResponse::set(400, UserError::new("Missing 'Content-Type' header"));

        // Vérification du body.
        $body = json_decode(file_get_contents("php://input"));
        if (is_null($body))
            RestResponse::set(400, UserError::new("Malformed JSON in body"));
        
        // Présence password.
        if (!isset($body->password))
            RestResponse::set(400, UserError::new("Missing body parameter 'password'"));
        $new_password = $body->password;

        // Type password.
        if (!is_string($new_password))
            RestResponse::set(400, UserError::new("Password must be of type string"));

        // Longueur password.
        if (strlen($new_password) > 63)
            RestResponse::set(400, UserError::new("Password too long: must be less than 64 bytes"));

        // Présence authentification.
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) // Serveur APACHE (voir .htaccess)
            $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        elseif (array_key_exists("authorization", getallheaders())) // Serveur PHP & client mkdocs notamment
            $authorization = getallheaders()["authorization"];
        elseif (array_key_exists("Authorization", getallheaders())) // Serveur PHP & client classique
            $authorization = getallheaders()["Authorization"];
        else {
            header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
            RestResponse::set(401, UserError::new("Authentification required"));
        }
        
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

        // Mise à jour !
        Profile::changePassword($username, password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]));
        RestResponse::set(200, null);

    } else {
        RestResponse::set(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
    }
} catch (Throwable $throwable) {
    echo RestResponse::set(500, ServerError::new($throwable));
}
