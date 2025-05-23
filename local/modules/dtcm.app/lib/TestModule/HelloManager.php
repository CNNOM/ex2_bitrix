<?php

namespace Local\TestModule;

use Bitrix\Main\Localization\Loc;
use CIBlockElement;
use CEventLog;
use CEvent;
use CUser;
use CUserFieldEnum;

Loc::loadMessages(__FILE__);

class HelloManager
{
    private static $data;

    //ex2-590
    public static function onBeforeElementAddUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REW) {

            if (str_contains($arFields['PREVIEW_TEXT'], '#del#')) {
                $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $arFields['PREVIEW_TEXT']);
            }

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $GLOBALS['APPLICATION']->ThrowException(
                    Loc::getMessage('PREVIEW_TEXT')
                );
                return false;
            }
        }
    }

    public static function OnBeforeIBlockElementHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REW) {
            $arProp = CIBlockElement::GetProperty(
                ID_IBLOCK_REW,
                $arFields['ID'],
                [],
                ['CODE' => 'AUTHOR']
            );
            while ($prop = $arProp->fetch()) {
                $old_author = $prop['VALUE'];
            }
            if ($old_author) {
                HelloManager::$data['old_author'][$arFields['ID']] = $old_author;
            } else {
                HelloManager::$data['old_author'][$arFields['ID']] = Loc::getMessage('NO_AUTHOR');
            }
        }
    }

    public static function OnAfterIBlockElementHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == ID_IBLOCK_REW) {
            $arProp = CIBlockElement::GetProperty(
                ID_IBLOCK_REW,
                $arFields['ID'],
                [],
                ['CODE' => 'AUTHOR']
            );
            while ($prop = $arProp->fetch()) {
                $new_author = $prop['VALUE'];
            }
            if (!$new_author) {
                $new_author = Loc::getMessage('NO_AUTHOR');
            }
            $old_author = HelloManager::$data['old_author'][$arFields['ID']];

            if ($new_author != $old_author) {
                $mess = Loc::getMessage(
                    'NEW_AUTHOR',
                    [
                        "#ID#" => $arFields['ID'],
                        "#old#" => $old_author,
                        "#new#" => $new_author,
                    ]
                );

                CEventLog::Add(
                    [
                        'AUDIT_TYPE_ID' => 'ex2_590',
                        'DESCRIPTION' => $mess,
                    ]
                );
            }
        }
    }

    //[ex2-600]
    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $rsUsers = CUser::GetList(
            ($by = "id"),
            ($order = "desc"),
            ['ID' => $arFields['ID']],
            ['FIELDS' => ['ID'], 'SELECT' => ['UF_USER_CLASS']]

        )->fetch();

        HelloManager::$data['OLD_CLASS'][$arFields['ID']] = $rsUsers['UF_USER_CLASS'];
    }

    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $OLD_USER_CLASS = HelloManager::$data['OLD_CLASS'][$arFields['ID']];

        if ($OLD_USER_CLASS) {
            $arElement = CUserFieldEnum::GetList(
                [],
                ['ID' => $OLD_USER_CLASS],
            )->fetch();
            $OLD_USER_CLASS = $arElement['VALUE'];
        } else {
            $OLD_USER_CLASS = Loc::getMessage('NO_CLASS');
        }

        if ($arFields['UF_USER_CLASS']) {
            $arElement = CUserFieldEnum::GetList(
                [],
                ['ID' => $arFields['UF_USER_CLASS']]
            )->fetch();
            $NEW_USER_CLASS = $arElement['VALUE'];
        } else {
            $NEW_USER_CLASS = Loc::getMessage('NO_CLASS');
        }

        if ($OLD_USER_CLASS != $NEW_USER_CLASS) {
            $arEventFields = [
                'OLD_USER_CLASS' => $OLD_USER_CLASS,
                'NEW_USER_CLASS' => $NEW_USER_CLASS,
            ];

            CEventLog::Add(
                [
                    'AUDIT_TYPE_ID' => 'ex2_590',
                    'DESCRIPTION' => 'OLD_USER_CLASS: ' . $OLD_USER_CLASS . ' NEW_USER_CLASS: ' . $NEW_USER_CLASS,
                ]
            );

            CEvent::Send(
                'EX2_AUTHOR_STATUS',
                's1',
                $arEventFields
            );
        }
    }


    // [ex2-620] 
    public static function OnBeforeEventSendHandler(&$arFields, &$arTemplate)
    {
        global $APPLICATION;
        $arFilter = array(
            "ACTIVE" => "Y",
            "ID" => "usrid"
        );
        $rsUsers = CUser::GetList(
            ($by = "personal_country"),
            ($order = "desc"),
            ['ID' => $arFields['USER_ID']],
            ['FIELDS' => ['ID'], 'SELECT' => ['UF_USER_CLASS']]

        )->fetch();

        if ($rsUsers) {
            // $arFields['CLASS']
            // $arTemplate["MESSAGE"] = str_replace('#CLASS#', $rsUsers['UF_USER_CLASS'], $arTemplate["MESSAGE"]);
            $arFields['CLASS'] = $rsUsers['UF_USER_CLASS'];
        } else {
            $arFields['CLASS'] = Loc::getMessage('NO_CLASS');

            // $arTemplate["MESSAGE"] = str_replace('#CLASS#', Loc::getMessage('NO_CLASS'), $arTemplate["MESSAGE"]);
        }
        // $APPLICATION->RestartBuffer();
        // echo '<pre>';
        // print_r($arTemplate);
        // echo '</pre>';
        // exit();
        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => 'ex2_590',
                'DESCRIPTION' => 'UF_USER_CLASS: ' . $rsUsers['UF_USER_CLASS'],
            ]
        );
        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => 'ex2_590',
                'DESCRIPTION' => 'UF_USER_CLASS: ' . $arTemplate["MESSAGE"],
            ]
        );
    }

    // ex2-630
    public static function BeforeIndexHandler(&$arFields)
    {
        if ($arFields["MODULE_ID"] == "iblock" && $arFields["PARAM2"] == ID_IBLOCK_REW) {
            $property = CIBlockElement::GetProperty(
                ID_IBLOCK_REW,
                $arFields['ITEM_ID'],
                [],
                ['CODE' => 'AUTHOR']
            )->Fetch();


            if ($property && !empty($property['VALUE'])) {
                // Получаем данные пользователя
                $user = CUser::GetList(
                    ($by = "id"),
                    ($order = "desc"),
                    ['ID' => $arFields['ID']],
                    ['FIELDS' => ['LOGIN']]
                )->fetch();

                // Добавляем логин к заголовку, если пользователь существует
                if ($user) {
                    $arFields['TITLE'] .= ' ' . $user['LOGIN'];
                }
            }
        }

        return $arFields;
    }
}
