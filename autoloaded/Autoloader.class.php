<?php

class Autoloader {
    public static function register(): void {
        spl_autoload_register(function ($class) {
            $file = $_SERVER["DOCUMENT_ROOT"] . "/autoloaded/" . str_replace('\\', DIRECTORY_SEPARATOR, $class) . ".class.php";
            if (file_exists($file)) {
                require $file;
                return true;
            }

            echo "Class not found: $class at $file";
            return false;
        });
    }
}
Autoloader::register();
