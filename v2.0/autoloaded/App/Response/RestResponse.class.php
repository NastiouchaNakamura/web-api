<?php

namespace App\Response;

class RestResponse {
    public static function get(int $httpCode, array $instances): string {
        header("Content-Type: application/json");
        http_response_code($httpCode);
        $meta["source_url"] = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
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
