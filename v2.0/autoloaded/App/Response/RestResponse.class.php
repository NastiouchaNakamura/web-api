<?php

namespace App\Response;

class RestResponse {
    public static function get(int $httpCode, array $instances): string {

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
        $meta["source_url"] = "https://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}";
        $meta["start"] = $_SERVER["REQUEST_TIME_FLOAT"];
        $meta["end"] = microtime(true);
        $meta["version"] = file_get_contents(ROOT . "version.txt");
        $meta["author"] = "Anaël BARODINE, CS student at University of Orléans, as member of Tribu-Terre";

        $objects = array();
        foreach ($instances as $instance) {
            if (is_object($instance)) {
                $objects[] = RestResponse::toObject($instance);
            } else {
                $objects[] = $instance;
            }
        }

        return json_encode(
            array(
                "metadata" => $meta,
                "data" => [
                    "object_count" => count($objects),
                    "objects" => $objects
                ]
            )
        );
    }

    public static function toObject($instance): array {
        $className = explode("\\", get_class($instance));
        $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($className)));

        $object = [
            "type" => $className
        ];

        foreach (get_class_methods($instance) as $classMethod) {
            if (str_starts_with($classMethod, "get")) {
                $attributeName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", substr($classMethod, 3)));
                $attribute = $instance->$classMethod();
                $object[$attributeName] = $attribute;
            }
        }

        return $object;
    }
}
