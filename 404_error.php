<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\Error;
use App\Response\RestResponse;

if ($_SERVER["REQUEST_URI"] == "/") {
    echo RestResponse::get(400, [Error::new("No tool specified")]);
} else {
    $explodedRequest = explode("/", $_SERVER["REQUEST_URI"], 3);
    $tool = $explodedRequest[1];
    $endpoint = $explodedRequest[2];

    if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/$tool")) {
        echo RestResponse::get(404, [Error::new("Tool '$tool' not found")]);
    } elseif ($endpoint != "") {
        echo RestResponse::get(404, [Error::new("Endpoint '$endpoint' of tool '$tool' not found")]);
    } else {
        echo RestResponse::get(400, [Error::new("No endpoint specified")]);
    }
}