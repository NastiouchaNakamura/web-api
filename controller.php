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
// Lecture du JSON local de la version.
$string = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/version.json");
$version_file = json_decode($string);

// Vérification du TTL, on recharge toutes les 4 heures.
$last_checked = date_create_from_format("Y-m-d\\TH:i:sp", $version_file->last_checked);
if (time() - $last_checked->getTimestamp() > 4 * 3600) {
    try {
        // Requête API GitHub.
        $githubAuthor = getenv("GITHUB_AUTHOR");
        $githubRepo = getenv("GITHUB_REPO");

        $patch_count = 0;
        $page = 1;
        do {
            // Préparation du cURL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,"https://api.github.com/repos/$githubAuthor/$githubRepo/commits?per_page=10&page=$page");
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["User-Agent: $githubAuthor"]);
            $page++;

            // Lancement du cURL
            $response  = curl_exec($curl);

            // Lecture du body
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $body = json_decode(substr($response, $header_size));

            // Compte des patchs depuis le commit de la version mineure.
            $i = 0;
            $trouve = false;
            foreach ($body as $commit) {
                if ($commit->sha == $version_file->minor_version_sha) {
                    $trouve = true;
                    break;
                } else {
                    $patch_count++;
                }
            }
        } while (!$trouve && preg_match("/link: .*rel=\"last\".*\n/", substr($response, 0, $header_size)));

        // Mise à jour du JSON local.
        $version_file->patch_count = $patch_count;
        $version_file->last_checked = date("Y-m-d\\TH:i:sp", time());
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/version.json", json_encode($version_file));

        // Construction de la version.
        define("VERSION", $version_file->major_version . "." . $version_file->minor_version . "." . $version_file->patch_count);

    } catch (Exception $e) {
        // Tant pis on a pas le patch…
        define("VERSION", $version_file->major_version . "." . $version_file->minor_version);
    }
} else {
    define("VERSION", $version_file->major_version . "." . $version_file->minor_version . "." . $version_file->patch_count);
}

########################################################################################################################
# Chargement de l'autoloader                                                                                           #
########################################################################################################################
require $_SERVER['DOCUMENT_ROOT'] . "/autoloaded/Autoloader.class.php";