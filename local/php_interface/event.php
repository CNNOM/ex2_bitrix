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
    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {

            if (str_contains($arFields['PREVIEW_TEXT_TYPE'], '#del#')) {
                $arFields['PREVIEW_TEXT_TYPE'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT_TYPE']);
            }


            if (mb_strlen($arFields['PREVIEW_TEXT_TYPE']) < 5) {
                $APPLICATION->ThrowException('Vfktyrmrf lkbyf fyjycf ' . mb_strlen($arFields['PREVIEW_TEXT_TYPE']));
                return false;
            }
        }
    }
    public static function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {

            if (str_contains($arFields['PREVIEW_TEXT_TYPE'], '#del#')) {
                $arFields['PREVIEW_TEXT_TYPE'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT_TYPE']);
            }

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException('Vfktyrmrf lkbyf fyjycf ' . mb_strlen($arFields['PREVIEW_TEXT']));
                return false;
            }

            $arRevEl = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR'],
            )->fetch();

            if ($arRevEl['PROPERTY_AUTHOR_VALUE']) {
                $class = $arRevEl['PROPERTY_AUTHOR_VALUE'];
            } else {
                $class = Loc::getMessage('NOT_AUTHOR');
            }

            Event::$data['OLD_CLASS'][$arFields['ID']] = $class;
        }
    }
    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        $arRevEl = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                'ID' => $arFields['ID'],
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR'],
        )->fetch();

        if ($arRevEl['PROPERTY_AUTHOR_VALUE']) {
            $new_class = $arRevEl['PROPERTY_AUTHOR_VALUE'];
        } else {
            $new_class = Loc::getMessage('NOT_AUTHOR');
        }
        $old_class =  Event::$data['OLD_CLASS'][$arFields['ID']];

        if ($old_class != $new_class) {
            $mess = Loc::getMessage('INFO', [
                '#ID#' => $arFields['ID'],
                '#new#' => $new_class,
                '#old#' => $old_class,
            ]);

            CEventLog::Add([
                'AUDIT_TYPE_ID' => '«ex2_590»',
                'DESCRIPTION' => $mess,
            ]);
        }
    }
    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $arUsers = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_AUTHOR_STATUS_7']
            ],
        )->fetch();

        Event::$data['OLD_USER_STATUS'][$arFields['ID']] = $arUsers['UF_AUTHOR_STATUS_7'];
    }
    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $old_status = Event::$data['OLD_USER_STATUS'][$arFields['ID']];
        $new_status = $arFields['UF_AUTHOR_STATUS_7'];


        if ($old_status) {
            $arRevEl = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_STATUS_ID,
                    'ACTIVE' => 'Y',
                    'PROPERTY_PRODUCT' => $old_status,
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
            )->fetch();
            $old_status = $arRevEl['NAME'];
        } else {
            $old_status = Loc::getMessage('NOT_STATUS');
        }

        if ($new_status) {
            $arRevEl = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_STATUS_ID,
                    'ACTIVE' => 'Y',
                    'PROPERTY_PRODUCT' => $new_status,
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
            )->fetch();
            $new_status = $arRevEl['NAME'];
        } else {
            $new_status = Loc::getMessage('NOT_STATUS');
        }

        if ($new_status != $old_status) {
            $mess = [
                '#OLD_UF_STATUS#' => $old_status,
                '#NEW_UF_STATUS#' => $new_status,
            ];

            CEvent::Send(
                'EX2_AUTHOR_INFO_TEST_7',
                SITE_ID,
                $mess
            );
        }
    }
}
