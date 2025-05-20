<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/constants.php";
require_once __DIR__ . "/lib/TestModule/HelloManager.php";

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', [
    '\Local\TestModule\HelloManager',
    'onBeforeElementAddUpdate'
]);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [
    '\Local\TestModule\HelloManager',
    'onBeforeElementAddUpdate'
]);