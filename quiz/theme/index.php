<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\InternalError;
use App\Model\Quiz\Theme;
use App\Response\RestResponse;

try {
    echo RestResponse::get(200, Theme::fetchAll());
} catch (Exception $exception) {
    echo RestResponse::get(500, [InternalError::new($exception)]);
}
