<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Color;
use App\Model\Nsi\Challenge;
use App\Model\Nsi\Profile;
use App\Model\Nsi\Request;
use App\Model\UserError;
use App\Model\ServerError;
use App\Response\RestResponse;

try {
    // Méthode POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Vérification du MIME du body.
        if (!array_key_exists("Content-Type", getallheaders()) && !array_key_exists("content-type", getallheaders())) {
            echo RestResponse::get(400, UserError::new("Missing 'Content-Type' header"));
            exit();
        }

        // Vérification du body.
        $body = json_decode(file_get_contents("php://input"));
        if (is_null($body)) {
            echo RestResponse::get(400, UserError::new("Malformed JSON in body"));
            exit();
        }
        
        // Présence username.
        if (!isset($body->username)) {
            echo RestResponse::get(400, UserError::new("Missing body parameter 'username'"));
            exit();
        }
        $username = $body->username;

        // Type username.
        if (!is_string($username)) {
            echo RestResponse::get(400, UserError::new("Username must be of type string"));
            exit();
        }

        // Présence password.
        if (!isset($body->password)) {
            echo RestResponse::get(400, UserError::new("Missing body parameter 'password'"));
            exit();
        }
        $password = $body->password;

        // Type password.
        if (!is_string($password)) {
            echo RestResponse::get(400, UserError::new("Password must be of type string"));
            exit();
        }

        // Longueur username.
        if (strlen($username) > 63) {
            echo RestResponse::get(400, UserError::new("Username too long: must be less than 64 characters"));
            exit();
        }

        // Format username.
        if (!preg_match("/[\x21-\x7E]+/", $username)) {
            echo RestResponse::get(400, UserError::new("Bad username format: characters must be non-whitespace ASCII"));
            exit();
        }

        // Longueur password.
        if (strlen($password) > 63) {
            echo RestResponse::get(400, UserError::new("Password too long: must be less than 64 bytes"));
            exit();
        }

        // Déjà pris ?
        if (!is_null(Profile::fetchByUsername($username))) {
            echo RestResponse::get(409, UserError::new("Username '$username' is already used"));
            exit();
        }

        // Création !
        Profile::create($username, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), new Color(255, 0, 0));
        echo RestResponse::get(201, null);
        exit();
    } else {
        echo RestResponse::get(405, UserError::new("Method " . $_SERVER['REQUEST_METHOD'] . " is not allowed"));
        exit();
    }
} catch (Throwable $throwable) {
    echo RestResponse::get(500, ServerError::new($throwable));
}
