<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

//-----

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
//-----


AddEventHandler(
    "main",
    "OnBeforeUserUpdate",
    ["Event", "OnBeforeUserUpdateHandler"]
);
AddEventHandler(
    "main",
    "OnAfterUserUpdate",
    ["Event", "OnAfterUserUpdateHandler"]
);
//-----
AddEventHandler(
    'main',
    'OnBeforeEventSend',
    ["Event", "OnBeforeEventSendHandler"]
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

    //-----

    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $arUser = CUser::GetList(
            ($by = "id"),
            ($order = "desc"),
            ['ID' => $arFields['ID']],
            ['FIELD' => ['ID'], 'SELECT' => ['UF_USER_CLASS_3']]
        )->fetch();

        $old_class = $arUser['UF_USER_CLASS_3'];
        // if (!$old_class) {
        //     $old_class = Loc::getMessage('NOT_CLASSs');
        // }

        Event::$data['OLD_CLASS'][$arFields['ID']] = $old_class;
    }

    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $old_class = Event::$data['OLD_CLASS'][$arFields['ID']];
        $new_class = $arFields['UF_USER_CLASS_3'];

        if ($old_class) {
            $arClass = CUserFieldEnum::GetList(
                [],
                ['ID' => $old_class]
            )->fetch();
            $old_class = $arClass['VALUE'];
        } else {
            $old_class = Loc::getMessage('NOT_CLASS');
        }


        if ($new_class) {
            $arClass = CUserFieldEnum::GetList(
                [],
                ['ID' => $new_class]
            )->fetch();
            $new_class = $arClass['VALUE'];
        } else {
            $new_class = Loc::getMessage('NOT_CLASS');
        }

        if ($old_class != $new_class) {

            $mess = [
                'OLD_USER_CLASS' => $old_class,
                'NEW_USER_CLASS' => $new_class,
            ];

            CEvent::Send('EX2_AUTHOR_INFO', SITE_ID, $mess);
        }
    }

    //-----

    public static function OnBeforeEventSendHandler(&$arFields, &$arTemplate)
    {
        global $APPLICATION;
        if ($arTemplate['EVENT_NAME'] === 'USER_INFO') {

            $arUser = CUser::GetList(
                ($by = "id"),
                ($order = "desc"),
                ['ID' => $arFields['USER_ID']],
                ['FIELD' => ['ID'], 'SELECT' => ['UF_USER_CLASS_3']]
            )->fetch();

            if ($arUser['UF_USER_CLASS_3']) {
                $arElement = CUserFieldEnum::GetList(
                    [],
                    ['ID' => $arUser['UF_USER_CLASS_3'], 'USER_FIELD_ID' => UF_USER_CLASS_3]
                )->fetch();

                $arFields['CLASS'] = $arElement['VALUE'];
            } else {
                $arFields['CLASS'] = Loc::getMessage('NO_CLASS');
            }
            CEventLog::Add(
                [
                    'AUDIT_TYPE_ID' => 'OnBeforeEventSendHandlers',
                    'DESCRIPTION'   => $arFields['CLASS']
                ]
            );
        }
    }
}
