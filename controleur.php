<?php
########################################################################################################################
# Vérification du protocole (les deux fonctionnent mais on veut forcer le passage par HTTPS)                           #
########################################################################################################################
if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

########################################################################################################################
# Initialisation des tableaux globaux                                                                                  #
########################################################################################################################
# Messages
$GLOBALS['messages'] = array();

# Retours d'appels de fonctions du modèle
$GLOBALS['retoursModele'] = array();

########################################################################################################################
# Initialisation du tableau formulaire                                                                                 #
########################################################################################################################
$form = array();
foreach ($_POST as $keyInput => $valInput) {
    $arrayInput = explode('_', $keyInput);
    if (isset($form['_name']) && $form['_name'] != $arrayInput[0]) {
        ajouterMessage(502, 'Attention : la convention d\'attribut "name" des inputs n\'est pas respectée.');
    } else {
        $form['_name'] = $arrayInput[0];
    }
    if (isset($arrayInput[2]) && $arrayInput[2] == 'submit') {
        $form['_submit'] = $arrayInput['1'];
    } else {
        $form[explode('_', $keyInput)[1]] = $valInput;
    }
}

if (count($form) == 0) {
    $form['_name'] = NULL;
    $form['_submit'] = NULL;
}

########################################################################################################################
# DEBUG pour pendant le développement                                                                                  #
# /!\ Tout ce qui suit doit être en commentaire dans la version finale du site /!\                                     #
########################################################################################################################
# Visualisation du formulaire POST
##ajouterMessage(0, print_r($form, true));

########################################################################################################################
# Fonctions d'ajout dans les tableaux globaux (pour la lisibilité)                                                     #
########################################################################################################################
function ajouterMessage($code, $texte) {
    $GLOBALS['messages'][] = [$code, htmlentities($texte, ENT_QUOTES, 'UTF-8')];
}

function ajouterRetourModele($cle, $resultats) {
    $GLOBALS['retoursModele'][$cle] = $resultats;
}

########################################################################################################################
# Version du site                                                                                                      #
########################################################################################################################
/**
 * Variable contenant la version actuelle du site indiquée dans le fichier ."../version.txt".
 */
define('VERSION_SITE', file_get_contents(RACINE . '/version.txt'));

########################################################################################################################
# Fonction de renvoi REST                                                                                              #
########################################################################################################################
function restReturn($httpCode, $data) {
    header("Content-Type: application/json");
    http_response_code($httpCode);
    $meta['source'] = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $meta['start'] = $_SERVER['REQUEST_TIME_FLOAT'];
    $meta['end'] = microtime(true);
    $meta['credits'] = 'Anaël BARODINE, étudiant en informatique à l\'Université d\'Orléans, au nom de l\'association étudiante Tribu-Terre.';
    echo json_encode(
        array(
            'metadata' => $meta,
            'data' => $data
        )
    );
}

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
