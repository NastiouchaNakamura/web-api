<?php
namespace App\Response;

use App\Model\GeoJson\GeoJsonGeometry;
use Closure;

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
                "data" => RestResponse::decomposeObject($data)
            )
        );
    }

    public static function decomposeObject($instance): array | bool | int | float | string | null {
        if (is_bool($instance) || is_int($instance) || is_float($instance) || is_string($instance) || is_null($instance)) {
            return $instance;

        } elseif (is_array($instance)) {
            $object = [
                "type" => "array"
            ];

            $array = array();
            foreach ($instance as $element) {
                $array[] = RestResponse::decomposeObject($element);
            }

            $object["size"] = count($array);
            $object["data"] = $array;

            return $object;

        } elseif ($instance instanceof GeoJsonGeometry) {
            return $instance->toGeoJson();

        } else {
            $className = explode("\\", get_class($instance));
            $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));

            $object = ["type" => $className];

            foreach (array_keys(get_class_vars(get_class($instance))) as $classProperty) {
                $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", $classProperty));
                $attribute = $instance->$classProperty;
                $object[$attributeName] = RestResponse::decomposeObject($attribute);
            }

            foreach (get_class_methods($instance) as $classMethod) {
                if (str_starts_with($classMethod, "get")) {
                    $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", substr($classMethod, 3)));
                    $attribute = $instance->$classMethod();
                    $object[$attributeName] = RestResponse::decomposeObject($attribute);
                }
            }

            ksort($object);

            return $object;
        }
    }
}
