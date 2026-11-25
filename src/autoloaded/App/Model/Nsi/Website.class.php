<?php

namespace App\Model\Nsi;

use DateTime;
use RuntimeException;

class Website {
    public string $year;
    public string $title;
    public string $authors;
    public string $directory;

    public static function fetch_websites(): array {
        if (!getenv("WEBSITES_ROOT")) {
            throw new RuntimeException("WEBSITES_ROOT environment variable is not set");
        }

        $root = getenv("WEBSITES_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        $websites = [];
        foreach (scandir($root) as $file_name) {
            $subpath = $root . "/" . $file_name;
            if (!str_starts_with($file_name, ".") && is_dir($subpath)) {
                $website_directory = $file_name;
                if (file_exists($root . "/" . $website_directory . "/website.json")) {
                    array_push($websites, Website::fetch_website($website_directory));
                }
            }
        }

        return $websites;
    }

    public static function website_exists(string $directory): bool {
        if (!getenv("WEBSITES_ROOT")) {
            throw new RuntimeException("WEBSITES_ROOT environment variable is not set");
        }

        $root = getenv("WEBSITES_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        return file_exists($root . "/" . $directory) && is_dir($root . "/" . $directory) && file_exists($root . "/" . $directory . "/website.json");
    }

    public static function fetch_website(string $directory): Website {
        if (!getenv("WEBSITES_ROOT")) {
            throw new RuntimeException("WEBSITES_ROOT environment variable is not set");
        }

        $root = getenv("WEBSITES_ROOT");
        $root = str_replace("\$DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"], $root);
        if (str_ends_with($root, "/")) {
            $root = substr($root, 0, -1);
        }

        if (!file_exists($root)) {
            throw new RuntimeException("File or directory '$root' does not exist");
        }

        if (!is_dir($root)) {
            throw new RuntimeException("'$root' is not a directory");
        }

        $website_dir = $root . "/" . $directory;
        $website_json_path = $website_dir . "/website.json";
        if (!file_exists($website_json_path)) {
            throw new RuntimeException("'$root' has no exam JSON file");
        }

        $website_json = json_decode(file_get_contents($website_json_path));
        $website = new Website();
        $website->directory = $directory;
        $website->year = $website_json->year;
        $website->title = $website_json->title;
        $website->authors = $website_json->authors;

        return $website;
    }
}
