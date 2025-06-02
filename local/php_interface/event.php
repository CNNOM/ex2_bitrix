<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", ["Event", "OnBeforeIBlockAddHandler"]);
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", ["Event", "OnBeforeIBlockElementUpdateHandler"]);
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", ["Event", "OnAfterIBlockElementUpdateHandler"]);

AddEventHandler("main", "OnBeforeUserUpdate", ["Event", "OnBeforeUserUpdateHandler"]);
AddEventHandler("main", "OnAfterUserUpdate", ["Event", "OnAfterUserUpdateHandler"]);


class Event
{
    public static $data;

    public static function OnBeforeIBlockAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {

                $arFields['PREVIEW_TEXT'] = str_replace('#del#', "", $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {

                $APPLICATION->ThrowException(Loc::getMessage('LEN_TEXT') . ' ' . $len);
                return false;
            }
        }
    }

    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {
            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {

                $arFields['PREVIEW_TEXT'] = str_replace('#del#', "", $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {

                $APPLICATION->ThrowException(Loc::getMessage('LEN_TEXT') . ' ' . $len);
                return false;
            }
        }

        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REVIEWS) {

            $arProp = CIBlockElement::GetProperty(
                ID_IBLOCK_REVIEWS,
                $arFields['ID'],
                [],
                ['CODE' => 'AUTHOR']
            );
            while ($prop = $arProp->Fetch()) {
                $arAuthor = $prop['VALUE'];
            }
            if ($arAuthor) {
                Event::$data['OLD_AUTHOR'][$arFields['ID']] = $arAuthor;
            } else {
                Event::$data['OLD_AUTHOR'][$arFields['ID']] = Loc::getMessage('NO_AUTHOR');
            }
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
            );
            while ($prop = $arProp->Fetch()) {
                $new_author = $prop['VALUE'];
            }

            if (!$new_author) {
                $new_author = Loc::getMessage('NO_AUTHOR');
            }

            $ol_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];

            if ($ol_author != $new_author) {
                $mess = Loc::getMessage(
                    'UPDATE_REV',
                    [
                        '#ID#' => $arFields['ID'],
                        '#old#' => $ol_author,
                        '#new#' => $new_author,
                    ]
                );

                CEventLog::Add([
                    'AUDIT_TYPE_ID' => 'ex2_590',
                    "DESCRIPTION" => $mess,
                ]);
            }
        }
    }


    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        $arUser = CUser::GetList(
            ($by = 'id'),
            ($order = 'desc'),
            ['ID' => $arFields['ID']],
            ['SELECT' => ['UF_USER_CLASS_2']]
        )->Fetch();

        Event::$data['OLD_USER_CLASS'][$arFields['ID']] = $arUser['UF_USER_CLASS_2'];
    }

    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $OLD_USER_CLASS = Event::$data['OLD_USER_CLASS'][$arFields['ID']];

        if ($OLD_USER_CLASS) {
            $arEnum = CUserFieldEnum::GetList(
                [],
                ["ID" => $OLD_USER_CLASS]
            )->Fetch();

            $OLD_USER_CLASS = $arEnum['VALUE'];
        } else {
            $OLD_USER_CLASS = Loc::getMessage('NO_CLASS');
        }

        if ($arFields['UF_USER_CLASS_2']) {
            $arEnum = CUserFieldEnum::GetList(
                [],
                ["ID" => $arFields['UF_USER_CLASS_2']]
            )->Fetch();

            $NEW_USER_CLASS = $arEnum['VALUE'];
        } else {
            $NEW_USER_CLASS = Loc::getMessage('NO_CLASS');
        }

        if ($OLD_USER_CLASS != $NEW_USER_CLASS) {

            $data = [
                'OLD_USER_CLASS' => $OLD_USER_CLASS,
                'NEW_USER_CLASS' => $NEW_USER_CLASS,
            ];

            CEvent::send(

                "EX2_AUTHOR_INFO",
                's1',
                $data
            );
        }
    }
}
