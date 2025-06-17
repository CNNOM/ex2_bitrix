<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
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

class Event
{
    public static $data;
    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REVIEWS_IBLOCK_ID) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('LEN_REW_TEXT') .  mb_strlen($arFields["PREVIEW_TEXT"]));
                return false;
            }
        }
    }
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REVIEWS_IBLOCK_ID) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('LEN_REW_TEXT') .  mb_strlen($arFields["PREVIEW_TEXT"]));
                return false;
            }

            $arRev = CIBlockElement::GetList(
                ["SORT" => "ASC"],
                [
                    'IBLOCK_ID' => REVIEWS_IBLOCK_ID,
                    "ACTIVE" => "Y",
                    'ID' => $arFields['ID']
                ],
                false,
                false,
                ['ID', 'PROPERTY_AUTHOR']
            )->fetch();

            if ($arRev['PROPERTY_AUTHOR_VALUE']) {
                $old_author = $arRev['PROPERTY_AUTHOR_VALUE'];
            } else {
                $old_author = Loc::getMessage('NO_AUTHOR');
            }
            Event::$data['OLD_AUTHOR'][$arFields['ID']] = $old_author;

            // $APPLICATION->RestartBuffer();
            // echo '<pre>';
            // print_r(Event::$data);
            // echo '</pre>';
            // exit();
        }
    }

    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $arRev = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            [
                'IBLOCK_ID' => REVIEWS_IBLOCK_ID,
                "ACTIVE" => "Y",
                'ID' => $arFields['ID']
            ],
            false,
            false,
            ['ID', 'PROPERTY_AUTHOR']
        )->fetch();

        if ($arRev['PROPERTY_AUTHOR_VALUE']) {
            $new_author = $arRev['PROPERTY_AUTHOR_VALUE'];
        } else {
            $new_author = Loc::getMessage('NO_AUTHOR');
        }

        $old_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];


        if ($old_author != $new_author) {
            $mess = Loc::getMessage('REV_LOG', [
                '#ID#' => $arFields['ID'],
                '#old#' => $old_author,
                '#new#' => $new_author,
            ]);

            CEventLog::Add(
                [
                    'AUDIT_TYPE_ID' => 'ex2_590',
                    'DESCRIPTION' => $mess,
                ]
            );
        }
    }
}
