<?php
########################################################################################################################
# Réponse aux requêtes OPTIONS (CORS)                                                                                  #
########################################################################################################################
header("Access-Control-Allow-Origin: *");
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Max-Age: 86400");
    http_response_code(204); // No content
    exit();
}

########################################################################################################################
# Vérification du protocole (les deux fonctionnent, mais on veut forcer le passage par HTTPS)                           #
########################################################################################################################
//if ($_SERVER["HTTPS"] != "on") {
//    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
//    exit();
//}

########################################################################################################################
# Chargement des variables d'environnement                                                                             #
########################################################################################################################
const VERSION = "dev";
const BUILD_SHA = "sourcecode";

########################################################################################################################
# Chargement de l'autoloader                                                                                           #
########################################################################################################################
spl_autoload_register(function ($class) {
    $file = $_SERVER["DOCUMENT_ROOT"] . "/autoloaded/" . str_replace('\\', DIRECTORY_SEPARATOR, $class) . ".class.php";
    if (file_exists($file)) {
        require $file;
        return true;
    } else {
        return false;
    }
});
