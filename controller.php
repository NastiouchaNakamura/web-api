<?php
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
// Si la variable d'environnement FROM_FILE est absente, c'est qu'on a besoin de charger depuis le fichier.
if (getenv("FROM_FILE") === false || getenv("FROM_FILE") == 1) {
    foreach (parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../.env") as $name => $value) {
        putenv("$name=$value");
    }
} else {
    putenv("FROM_FILE=0");
}

########################################################################################################################
# Chargement de la version                                                                                             #
########################################################################################################################
define("VERSION", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/version.txt"));

########################################################################################################################
# Chargement de l'autoloader                                                                                           #
########################################################################################################################
require $_SERVER['DOCUMENT_ROOT'] . "/autoloaded/Autoloader.class.php";