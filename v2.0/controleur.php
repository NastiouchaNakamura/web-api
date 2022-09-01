<?php
########################################################################################################################
# Vérification du protocole (les deux fonctionnent, mais on veut forcer le passage par HTTPS)                           #
########################################################################################################################
if ($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

########################################################################################################################
# Chargement des variables d'environnement                                                                             #
########################################################################################################################
require ROOT . "../../secrets.php";

########################################################################################################################
# Chargement de l'autoloader                                                                                           #
########################################################################################################################
require ROOT . "autoloaded/Autoloader.class.php";

########################################################################################################################
# API - Université                                                                                                     #
########################################################################################################################
function CtlApiUniversite() {
    try {
        MdlApiGetBatiments();
        restReturn(
            200,
            $GLOBALS['retoursModele']['batiments']
        );
        return;
    } catch (Exception $e) {
        MdlLogApi('ERROR', 'Erreur interne survenue lors de la requête des bâtiments : ' . '(' . $e->getCode() . ')' . $e->getMessage());
        restReturn(
            500,
            'Erreur interne survenue lors de la requête des bâtiments.'
        );
        return;
    }
}

########################################################################################################################
# API - Université - Salles                                                                                            #
########################################################################################################################
function CtlApiUniversiteSalles($idBatiment) {
    try {
        if (!empty($idBatiment) && ctype_digit($idBatiment)) {
            MdlApiGetSalles($idBatiment);
            restReturn(
                200,
                $GLOBALS['retoursModele']['salles']
            );
        } else {
            restReturn(
                400,
                'Veuillez saisir un ID de bâtiment (nombre entier) comme paramètre HTTP \'id\'.'
            );
        }
    } catch (Exception $e) {
        MdlLogApi('ERROR', 'Erreur interne survenue lors de la requête des salles : ' . '(' . $e->getCode() . ')' . $e->getMessage());
        restReturn(
            500,
            'Erreur interne survenue lors de la requête des salles.'
        );
        return;
    }
}

########################################################################################################################
# API - Université - GeoJSON                                                                                           #
########################################################################################################################
function CtlApiUniversiteGeoJson($idBatiment) {
    try {
        if (!empty($idBatiment) && ctype_digit($idBatiment)) {
            MdlApiGetGeoJson($idBatiment);
            restReturn(
                200,
                $GLOBALS['retoursModele']['geoJson']
            );
        } else {
            restReturn(
                400,
                'Veuillez saisir un ID de bâtiment (nombre entier) comme paramètre HTTP \'id\'.'
            );
        }
    } catch (Exception $e) {
        MdlLogApi('ERROR', 'Erreur interne survenue lors de la requête du GeoJSON : ' . '(' . $e->getCode() . ')' . $e->getMessage());
        restReturn(
            500,
            'Erreur interne survenue lors de la requête du GeoJSON.'
        );
        return;
    }
}
