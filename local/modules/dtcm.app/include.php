<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/constants.php";
require_once __DIR__ . "/constants.php";
require_once __DIR__ . "/agents.php";
require_once __DIR__ . "/lib/TestModule/HelloManager.php";

//ex2-590
AddEventHandler('iblock', 'OnBeforeIBlockElementAdd', [
    '\Local\TestModule\HelloManager',
    'onBeforeElementAddUpdateHandler'
]);

AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [
    '\Local\TestModule\HelloManager',
    'onBeforeElementAddUpdateHandler'
]);


AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [
    '\Local\TestModule\HelloManager',
    'OnBeforeIBlockElementHandler'
]);
AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', [
    '\Local\TestModule\HelloManager',
    'OnAfterIBlockElementHandler'
]);

//[ex2-600]
AddEventHandler('main', 'OnBeforeUserUpdate', [
    '\Local\TestModule\HelloManager',
    'OnBeforeUserUpdateHandler'
]);
AddEventHandler('main', 'OnAfterUserUpdate', [
    '\Local\TestModule\HelloManager',
    'OnAfterUserUpdateHandler'
]);



// [ex2-620]
AddEventHandler('main', 'OnBeforeEventSend', [
    '\Local\TestModule\HelloManager',
    'OnBeforeEventSendHandler'
]);


// [ex2-630]
AddEventHandler('main', 'BeforeIndex', [
    '\Local\TestModule\HelloManager',
    'BeforeIndexHandler'
]);
