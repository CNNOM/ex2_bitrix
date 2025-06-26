<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", array("Event", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Event", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("Event", "OnAfterIBlockElementUpdateHandler"));

AddEventHandler("main", "OnBeforeUserUpdate", array("Event", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnAfterUserUpdate", array("Event", "OnAfterUserUpdateHandler"));

class Event
{
    public static $data;
    public static function  OnBeforeIBlockElementAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {

            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage(
                    'ERROR_SMALL_LEN_PREVIEW_TEXT',
                    [
                        '#len#' => $len
                    ]
                ));
                return false;
            }
        }
    }
    public static function  OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {

            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            $len = mb_strlen($arFields['PREVIEW_TEXT']);
            if ($len < 5) {
                $APPLICATION->ThrowException(Loc::getMessage(
                    'ERROR_SMALL_LEN_PREVIEW_TEXT',
                    [
                        '#len#' => $len
                    ]
                ));
                return false;
            }

            $res = CIBlockElement::GetList(
                ['SORT' => 'asc'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
            )->fetch();

            if ($res['PROPERTY_AUTHOR_VALUE']) {
                $old_author = $res['PROPERTY_AUTHOR_VALUE'];
            } else {
                $old_author = Loc::getMessage('NOT_AUTHOR');
            }

            Event::$data['OLD_AUTHOR'][$arFields['ID']] = $old_author;
        }
    }
    public static function  OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $old_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];

        $res = CIBlockElement::GetList(
            ['SORT' => 'asc'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                'ID' => $arFields['ID'],
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
        )->fetch();

        if ($res['PROPERTY_AUTHOR_VALUE']) {
            $new_author = $res['PROPERTY_AUTHOR_VALUE'];
        } else {
            $new_author = Loc::getMessage('NOT_AUTHOR');
        }

        if ($new_author != $old_author) {
            $mess = Loc::getMessage(
                'UPDATE_AUTHOR_PRODUCT',
                [
                    '#ID#' => $arFields['ID'],
                    '#old#' => $old_author,
                    '#new#' => $new_author,
                ]
            );

            CEventLog::Add([
                'AUDIT_TYPE_ID' => '«ex2_590»',
                'DESCRIPTION' => $mess
            ]);
        }
    }

    public static function  OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;


        $res = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS']
            ],
        )->fetch();

        Event::$data['OLD_STATUS'][$arFields['ID']] = $res['UF_USER_CLASS'];
    }

    public static function  OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $old_status = Event::$data['OLD_STATUS'][$arFields['ID']];
        $new_status = $arFields['UF_USER_CLASS'];


        if ($old_status) {
            $res = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $old_status,
                    'USER_FIELD_ID' => ID_UF_USER_CLASS,
                ],
            )->fetch();
            $old_status = $res['VALUE'];
        } else {
            $old_status = Loc::getMessage('NOT_STATUS');
        }


        if ($new_status) {
            $res = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $new_status,
                    'USER_FIELD_ID' => ID_UF_USER_CLASS,
                ],
            )->fetch();
            $new_status = $res['VALUE'];
        } else {
            $new_status = Loc::getMessage('NOT_STATUS');
        }

        if ($old_status != $new_status) {

            $mess = [
                'OLD_USER_CLASS' => $old_status,
                'NEW_USER_CLASS' => $new_status,
            ];
            CEvent::Send(
                "EX2_AUTHOR_INFO",
                's1',
                $mess
            );
        }
    }
}
