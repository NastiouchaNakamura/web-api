<?php
require $_SERVER["DOCUMENT_ROOT"] . "/controller.php";

use App\Model\InternalError;
use App\Model\Quiz\Category;
use App\Response\RestResponse;

try {
    echo RestResponse::get(200, Category::fetchAll());
} catch (Exception $exception) {
    echo RestResponse::get(500, [InternalError::new($exception)]);
}
