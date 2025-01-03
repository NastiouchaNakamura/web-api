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
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/../secrets.php")) {
    require $_SERVER['DOCUMENT_ROOT'] . "/../secrets.php";
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . "/secrets.php")) {
    require $_SERVER['DOCUMENT_ROOT'] . "/secrets.php";
}

########################################################################################################################
# Chargement de la version                                                                                             #
########################################################################################################################
define("VERSION", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/version.txt"));

########################################################################################################################
# Chargement de l'autoloader                                                                                           #
########################################################################################################################
require $_SERVER['DOCUMENT_ROOT'] . "/autoloaded/Autoloader.class.php";