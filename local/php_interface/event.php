<?php

use Bitrix\Main\Localization\Loc;

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", array("Event", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Event", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("Event", "OnAfterIBlockElementUpdateHandler"));


Loc::loadMessages(__FILE__);
class Event
{
    public static $data;
    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('INFO_ERROR_PREVIEW_TEXT', ['#len#' => $len]));
                return false;
            }
        }
    }
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage('INFO_ERROR_PREVIEW_TEXT', ['#len#' => $len]));
                return false;
            }


            $rs = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_PRODUCT', 'PROPERTY_AUTHOR'],
            )->fetch();

            if ($rs['PROPERTY_AUTHOR_VALUE']) {
                $old_author = $rs['PROPERTY_AUTHOR_VALUE'];
            } else {
                $old_author = Loc::getMessage('NOT_AUTHOR');
            }

            Event::$data['OLD_AUTHOR'][$arFields['ID']] = $old_author;
        }
    }
    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {
            $old_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];

            $rs = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_PRODUCT', 'PROPERTY_AUTHOR'],
            )->fetch();

            if ($rs['PROPERTY_AUTHOR_VALUE']) {
                $new_author = $rs['PROPERTY_AUTHOR_VALUE'];
            } else {
                $new_author = Loc::getMessage('NOT_AUTHOR');
            }

            if ($old_author != $new_author) {
                $mess = Loc::getMessage(
                    'INFO_UPDATE_AUTHOR_REV',
                    [
                        '#ID#' => $arFields['ID'],
                        '#old#' => $old_author,
                        '#new#' => $new_author,

                    ]
                );
                CEventLog::Add(
                    [
                        'AUDIT_TYPE_ID' => '«ex2_590»',
                        'DESCRIPTION' => $mess,
                    ]
                );
            }
        }
    }
}
