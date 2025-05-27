<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/event.php')) {
    require_once($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/event.php');
}
if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/const.php')) {
    require_once($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/const.php');
}


