<?php
namespace App\Response;

use App\Model\GeoJson\GeoJsonGeometry;
use DateTime;

/**
 * Class used to format proper HTTP response according to REST principles.
 * 
 * Usage: use static method `set` to set all necessary headers and respond with
 * given response code and given object (that will be JSONified).
 */
class RestResponse {
    /**
     * @deprecated Use newer method `set` that 
     */
    public static function get(int $httpCode, mixed $data): string {
        $meta["source_url"] = (empty($_SERVER["HTTPS"]) ? "http" : "https") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $meta["author"] = "Anaël BARODINE";
        $meta["start"] = $_SERVER["REQUEST_TIME_FLOAT"];
        $meta["end"] = microtime(true);
        $meta["version"] = VERSION;
        $meta["build"] = BUILD_SHA;

        $content = json_encode(
            array(
                "metadata" => $meta,
                "data" => RestResponse::encapsulate_value($data)
            )
        );

        http_response_code($httpCode);
        header("Content-Type: application/json; charset=UTF-8");
        header("Content-Length: " . strlen($content));

        return $content;
    }

    /**
     * Sets all necessary HTTP headers, sets response metadata, formats given
     * data as JSON and sends the response before terminating (`exit`) PHP
     * process.
     * 
     * @param int $httpCode HTTP code to use in response
     * @param mixed $data Data to use as response data ('payload'), that will
     * be formated to JSON.
     * @return never Never returns as `exit()` function is used to terminate
     * execution.
     */
    public static function set(int $httpCode, mixed $data): never {
        $meta["source_url"] = (empty($_SERVER["HTTPS"]) ? "http" : "https") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $meta["author"] = "Anaël BARODINE";
        $meta["start"] = $_SERVER["REQUEST_TIME_FLOAT"];
        $meta["end"] = microtime(true);
        $meta["version"] = VERSION;
        $meta["build"] = BUILD_SHA;

        $content = json_encode(
            array(
                "metadata" => $meta,
                "data" => RestResponse::encapsulate_value($data)
            ), JSON_THROW_ON_ERROR
        );

        http_response_code($httpCode);
        header("Content-Type: application/json; charset=UTF-8");
        header("Content-Length: " . strlen($content));

        echo $content;
        exit();
    }

    private static function encapsulate_value(mixed $instance): bool|int|float|string|null|array {
        // Types primitifs en JSON (pas d'encapsulation).
        if (is_bool($instance) || is_int($instance) || is_float($instance) || is_string($instance) || is_null($instance))
            return $instance;

        // Types construits à format spécial.
        elseif ($instance instanceof DateTime)
            return ["type" => "date_time", "value" => $instance->format("Y-m-d H:i:s")];
        elseif ($instance instanceof GeoJsonGeometry)
            return ["type" => "geo_json", "value" => $instance->toGeoJson()];

        // Tableaux.
        elseif (is_array($instance))
            return ["type" => "array", "size" => count($instance), "values" => array_map([RestResponse::class, "encapsulate_value"], $instance)];

        // Types construits.
        else
            return RestResponse::decompose_object($instance);
    }

    private static function decompose_object(mixed $instance): array {
        $className = explode("\\", get_class($instance));
        $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));

        $object = ["type" => $className];

        foreach (array_keys(get_class_vars(get_class($instance))) as $classProperty) {
            $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", $classProperty));
            $attribute = $instance->$classProperty;
            $object[$attributeName] = RestResponse::encapsulate_value($attribute);
        }

        foreach (get_class_methods($instance) as $classMethod) {
            if (str_starts_with($classMethod, "get")) {
                $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", substr($classMethod, 3)));
                $attribute = $instance->$classMethod();
                $object[$attributeName] = RestResponse::encapsulate_value($attribute);
            }
        }

        return $object;
    }
}
