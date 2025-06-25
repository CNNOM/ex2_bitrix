<?php

use Bitrix\Main\Localization\Loc;

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", array("Event", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Event", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("Event", "OnAfterIBlockElementUpdateHandler"));

AddEventHandler("main", "OnBeforeUserUpdate", array("Event", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnAfterUserUpdate", array("Event", "OnAfterUserUpdateHandler"));


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

    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $ar = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_AUTHOR_STATUS_8'],
            ],
        )->fetch();
        Event::$data['OLD_AUTHOR'][$arFields['ID']] = $ar['UF_AUTHOR_STATUS_8'];
    }
    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        $old_status = Event::$data['OLD_AUTHOR'][$arFields['ID']];
        $new_status = $arFields['UF_AUTHOR_STATUS_8'];

        if ($old_status) {
            $arRevItems = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => SATUS_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $old_status,
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME'],
            )->fetch();
            $old_status = $arRevItems['NAME'];
        } else {
            $old_status = Loc::getMessage('NOT_STATUS');
        }

        if ($new_status) {
            $arRevItems = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => SATUS_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'ID' => $new_status,
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME'],
            )->fetch();
            $new_status = $arRevItems['NAME'];
        } else {
            $new_status = Loc::getMessage('NOT_STATUS');
        }

        if ($old_status != $new_status) {

            $mess = [
                'OLD_UF_STATUS' => $old_status,
                'NEW_UF_STATUS' => $new_status,
            ];
            CEvent::Send(
                'EX2_AUTHOR_INFO',
                's1',
                $mess,
            );
        }
    }
}
