<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;


AddEventHandler(
    "iblock",
    "OnBeforeIBlockElementAdd",
    ["Event", "OnBeforeIBlockElementAddHandler"]
);
AddEventHandler(
    "iblock",
    "OnBeforeIBlockElementUpdate",
    ["Event", "OnBeforeIBlockElementUpdateHandler"]
);

AddEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    ["Event", "OnAfterIBlockElementUpdateHandler"]
);

Loc::loadMessages(__FILE__);

class Event
{
    public static $data;

    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {

            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('PREVIEW_TEXT_LEN') . ' ' . $len);
                return false;
            }
        }
    }

    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {

            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('PREVIEW_TEXT_LEN') . ' ' . $len);
                return false;
            }


            $arProp = CIBlockElement::GetProperty(
                ID_IBLOCK_REVIEWS,
                $arFields['ID'],
                [],
                ['CODE' => 'AUTHOR']
            )->fetch();

            $old_author = $arProp['VALUE'];
            if (!$old_author) {
                $old_author = Loc::getMessage('NOT_AUTHOR');
            }
            Event::$data['OLD_AUTHOR'][$arFields['ID']] = $old_author;
        }
    }


    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {

            $arProp = CIBlockElement::GetProperty(
                ID_IBLOCK_REVIEWS,
                $arFields['ID'],
                [],
                ['CODE' => 'AUTHOR']
            )->fetch();

            $new_author = $arProp['VALUE'];
            if (!$new_author) {
                $new_author = Loc::getMessage('NOT_AUTHOR');
            }

            $old_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];

            // $APPLICATION->RestartBuffer();
            // echo '<pre>';
            // print_r($new_author);
            // echo '</pre>';
            // echo '<pre>';
            // print_r($old_author);
            // echo '</pre>';
            // exit();
            $mess = Loc::getMessage(
                'NEW_AUTHOR',
                [
                    '#ID#' => $arFields['ID'],
                    '#old#' => $old_author,
                    '#new#' => $new_author,
                ]
            );

            if ($new_author != $old_author) {
                CEventLog::Add(array(
                    "AUDIT_TYPE_ID" => "ex2_590",
                    "DESCRIPTION" => $mess,
                ));
            }
        }
    }
}
