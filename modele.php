<?php
########################################################################################################################
# Fonction techniques                                                                                                  #
########################################################################################################################
function MET_SQLLigneUnique($object) {
    if ($object) {
        $array = array();
        foreach ($object as $key => $val) {
            $array[$key] = is_string($val) ? htmlentities($val, ENT_QUOTES, 'UTF-8') : $val;
        }
        return $array;
    }
    return $object;
}

function MET_SQLLignesMultiples($arrayObject): array {
    $array = array();
    foreach ($arrayObject as $objectKey => $objectValue) {
        $array[$objectKey] = MET_SQLLigneUnique($objectValue);
    }
    return $array;
}

function requeteSQL($requete, $variables = array(), $nbResultats = 2, $codeMessageSucces = NULL, $texteMessageSucces = NULL) {
    try {
        $connexion = getConnect();
        $prepare = $connexion->prepare($requete);
        foreach ($variables as $variable) {
            $data_type = $variable[2] == 'INT' ? PDO::PARAM_INT : PDO::PARAM_STR;
            $prepare->bindValue($variable[0], $variable[1], $data_type);
        }
        $prepare->execute();
        switch ($nbResultats) {
            case 0:
                $retour = NULL;
                break;
            case 1:
                $retour = MET_SQLLigneUnique($prepare->fetch());
                break;
            default:
                $retour = MET_SQLLignesMultiples($prepare->fetchAll());
        }
        $prepare->closeCursor();
        if ($codeMessageSucces && $texteMessageSucces) {
            ajouterMessage($codeMessageSucces, $texteMessageSucces);
        }
        return $retour;
    } catch (Exception $e) {
        ajouterMessage(600, $e->getMessage());
        switch ($nbResultats) {
            case 0:
            case 1:
                return NULL;
            default:
                return array();
        }
    }
}

########################################################################################################################
# Log des actions                                                                                                      #
########################################################################################################################
function MdlLogApi($status, $message) {
    MdlLog('API', $status, $message);
}

function MdlLog($context, $status, $message) {
    $timestamp = time();
    $dt = new DateTime('now', new DateTimeZone('Europe/Paris'));
    $dt->setTimestamp($timestamp);
    requeteSQL(
        "
        INSERT INTO
            website_log
        VALUES
            (
                0,
                :dateLog,
                :messageLog
            )
        ",
        array(
            [':dateLog', $dt->format('Y-m-d H-i-s'), 'STR'],
            [':messageLog', '[' . $context . ']' . '[' . $status . ']' . '[' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '] ' . $message, 'STR']
        ),
        0
    );
}

########################################################################################################################
# Salles (API)                                                                                                         #
########################################################################################################################
function MdlRechercherSalle($nomSalle): void {
    ajouterRetourModele(
        'salles',
        requeteSQL(
            "
                SELECT
                    api_universite_salles.id AS id,
                    api_universite_salles.nom AS nom,
                    api_universite_groupes_salles.nom AS nomGroupe,
                    api_universite_batiments.id AS idBatiment,
                    api_universite_batiments.libelleLong AS nomBatiment,
                    api_universite_groupes_batiments.id AS codeComposante,
                    api_universite_groupes_batiments.titre AS titreComposante
                FROM
                    api_universite_salles
                        JOIN
                    api_universite_groupes_salles
                        ON
                            api_universite_groupes_salles.id = api_universite_salles.idGroupe
                        JOIN
                    api_universite_batiments
                        ON
                            api_universite_batiments.id = api_universite_groupes_salles.idBatiment
                        JOIN
                    api_universite_groupes_batiments
                        ON
                            api_universite_groupes_batiments.id = api_universite_batiments.idGroupe
                WHERE
                    LOWER(api_universite_salles.nom)
                        LIKE
                    :nomSalle
                ",
            array(
                [':nomSalle', '%' . $nomSalle . '%', 'STR']
            )
        )
    );
}

function MdlApiGetBatiments(): void {
    $prepare = getConnect()->prepare(
        "
        SELECT
            api_universite_batiments.id AS id,
            legende,
            titre,
            couleurR,
            couleurG,
            couleurB,
            libelleCourt,
            libelleLong,
            idGroupe
        FROM
            api_universite_groupes_batiments
                JOIN
            api_universite_batiments
                ON
                    api_universite_groupes_batiments.id = api_universite_batiments.idGroupe;
        "
    );
    $prepare->execute();
    $batiments = $prepare->fetchAll();
    $prepare->closeCursor();

    $batimentsJson = [];
    foreach ($batiments as $batiment) {
        if (!isset($batimentsJson[$batiment['idGroupe']])) {
            $batimentsJson[$batiment['idGroupe']] = [
                'legende' => html_entity_decode($batiment['legende'], ENT_QUOTES),
                'titre' => html_entity_decode($batiment['titre'], ENT_QUOTES),
                'couleur' => '#' . str_pad(
                    dechex(
                        $batiment['couleurR'] * 256 * 256 +
                        $batiment['couleurG'] * 256 +
                        $batiment['couleurB']
                    ),
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'batiments' => []
            ];
        }
        $batimentsJson[$batiment['idGroupe']]['batiments'][] = [
            'id' => $batiment['id'],
            'libelle_court' => html_entity_decode($batiment['libelleCourt'], ENT_QUOTES),
            'libelle_long' => html_entity_decode($batiment['libelleLong'], ENT_QUOTES)
        ];
    }

    ajouterRetourModele(
        'batiments',
        $batimentsJson
    );
}

function MdlApiGetSalles($idBatiment): void {
    $prepare = getConnect()->prepare(
        "
        SELECT
            api_universite_groupes_salles.nom AS nomGroupe,
            idGroupe, api_universite_salles.nom AS nom
        FROM
            api_universite_groupes_salles
                JOIN
            api_universite_salles
                ON
                    api_universite_groupes_salles.id = api_universite_salles.idGroupe
        WHERE idBatiment=:idBatiment;
        "
    );
    $prepare->bindValue(':idBatiment', $idBatiment, PDO::PARAM_INT);
    $prepare->execute();
    $salles = $prepare->fetchAll();
    $prepare->closeCursor();

    $groupes = [];
    foreach ($salles as $salle) {
        if (!isset($groupes[$salle['idGroupe']])) {
            $groupes[$salle['idGroupe']] = [
                'nom' => html_entity_decode($salle['nomGroupe'], ENT_QUOTES),
                'salles' => []
            ];
        }
        $groupes[$salle['idGroupe']]['salles'][] = html_entity_decode($salle['nom'], ENT_QUOTES);
    }

    $groupesJson = [];

    foreach ($groupes as $groupe) {
        $groupesJson[] = $groupe;
    }

    ajouterRetourModele(
        'salles',
        $groupesJson
    );
}

function MdlApiGetGeoJson($idBatiment): void {
    $prepare = getConnect()->prepare(
        "
        SELECT
            carved,
            idBatiment,
            idPolygon,
            c1,
            c2
        FROM
            api_universite_polygons
                JOIN
            api_universite_coordonnees
                ON
                    api_universite_polygons.id = api_universite_coordonnees.idPolygon
        WHERE idBatiment=:idBatiment;
        "
    );
    $prepare->bindValue(':idBatiment', $idBatiment, PDO::PARAM_INT);
    $prepare->execute();
    $coordinates = $prepare->fetchAll();
    $prepare->closeCursor();

    $polygons = [];
    foreach ($coordinates as $coordinate) {
        if (!isset($polygons[$coordinate['idPolygon']])) {
            $polygons[$coordinate['idPolygon']] = [
                'carved' => $coordinate['carved'],
                'coords' => []
            ];
        }
        $polygons[$coordinate['idPolygon']]['coords'][] = [
            floatval($coordinate['c1']),
            floatval($coordinate['c2'])
        ];
    }

    $geoJson = [
        'type' => 'MultiPolygon',
        'coordinates' => [[],[]]
    ];

    foreach ($polygons as $polygon) {
        $geoJson['coordinates'][$polygon['carved']][] = $polygon['coords'];
    }

    ajouterRetourModele(
        'geoJson',
        $geoJson
    );
}
