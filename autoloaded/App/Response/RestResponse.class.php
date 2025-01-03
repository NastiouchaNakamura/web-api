<?php
namespace App\Response;

use App\Model\GeoJson\GeoJsonGeometry;
use DateTime;

class RestResponse {
    public static function get(int $httpCode, array $data): string {

        // TODO : Limiter les accès à certains domaines.
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }

        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($httpCode);
        $meta["source_url"] = (empty($_SERVER["HTTPS"]) ? "http" : "https") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $meta["start"] = $_SERVER["REQUEST_TIME_FLOAT"];
        $meta["end"] = microtime(true);
        $meta["version"] = VERSION;
        $meta["author"] = "Anaël BARODINE";

        return json_encode(
            array(
                "metadata" => $meta,
                "data" => RestResponse::encapsulateValue($data)
            )
        );
    }

    public static function encapsulateValue($instance): bool|int|float|string|null|array {
        // Types primitifs en JSON pas besoin d'indiquer le type.
        if (is_bool($instance) || is_int($instance) || is_float($instance) || is_string($instance)  || is_null($instance))
            return $instance;

        // Types construits à format spécial.
        elseif ($instance instanceof DateTime)
            return ["type" => "dateTime", "value" => $instance->format("Y-m-d H:i:s")];
        elseif ($instance instanceof GeoJsonGeometry)
            return ["type" => "geoJson", "value" => $instance->toGeoJson()];

        // Tableaux.
        elseif (is_array($instance))
            return ["type" => "array", "size" => count($instance), "values" => array_map([RestResponse::class, "encapsulateValue"], $instance)];

        // Types construits.
        else
            return RestResponse::decomposeObject($instance);

        // Probablement un bug dû au match, ci-dessous ne marche pas.
        // Problème : is_array retourne bien True quand c'est un array mais le match sélectionne le défaut quand même.
        // TODO: Vérifier à une prochaine version de PHP si cela est corrigé.
//        return match ($instance) {
//            is_bool($instance) => ["type" => "boolean", "value" => $instance],
//            is_int($instance) => ["type" => "integer", "value" => $instance],
//            is_float($instance) => ["type" => "float", "value" => $instance],
//            is_string($instance) => ["type" => "string", "value" => $instance],
//            is_null($instance) => ["type" => "null"],
//            $instance instanceof DateTime => ["type" => "dateTime", "value" => $instance->format("Y-m-d H:i:s")],
//            $instance instanceof GeoJsonGeometry => ["type" => "geoJson", "value" => $instance->toGeoJson()],
//            is_array($instance) => RestResponse::decomposeArray($instance),
//            default => RestResponse::decomposeObject($instance),
//        };
    }

    public static function decomposeObject($instance): array {
        $className = explode("\\", get_class($instance));
        $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));

        $object = ["type" => $className];

        foreach (array_keys(get_class_vars(get_class($instance))) as $classProperty) {
            $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", $classProperty));
            $attribute = $instance->$classProperty;
            $object[$attributeName] = RestResponse::encapsulateValue($attribute);
        }

        foreach (get_class_methods($instance) as $classMethod) {
            if (str_starts_with($classMethod, "get")) {
                $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", substr($classMethod, 3)));
                $attribute = $instance->$classMethod();
                $object[$attributeName] = RestResponse::encapsulateValue($attribute);
            }
        }

        ksort($object);

        return $object;
    }
}
