<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", array("Event", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", array("Event", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("Event", "OnAfterIBlockElementUpdateHandler"));

AddEventHandler("main", "OnBeforeUserUpdate", array("Event", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnAfterUserUpdate", array("Event", "OnAfterUserUpdateHandler"));

AddEventHandler('main', 'OnBeforeEventSend', array("Event", "OnBeforeEventSendHandler"));


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

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException('долбоёб');
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

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException('долбоёб');
                return false;
            }

            $arRewList = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => "Y",
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_AUTHOR'],
            )->fetch();


            if ($arRewList['PROPERTY_AUTHOR_VALUE']) {
                $author = $arRewList['PROPERTY_AUTHOR_VALUE'];
            } else {
                $author = Loc::getMessage('NOT_AUTHOR');
            }

            Event::$data['OLD_CLASS'][$arFields['ID']] = $author;
        }
    }
    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['IBLOCK_ID'] == REV_IBLOCK_ID) {

            $arRewList = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ACTIVE' => "Y",
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_AUTHOR'],
            )->fetch();


            if ($arRewList['PROPERTY_AUTHOR_VALUE']) {
                $new_AUTHOR = $arRewList['PROPERTY_AUTHOR_VALUE'];
            } else {
                $new_AUTHOR = Loc::getMessage('NOT_AUTHOR');
            }

            $old_AUTHOR = Event::$data['OLD_CLASS'][$arFields['ID']];

            if ($old_AUTHOR != $new_AUTHOR) {
                $mess = Loc::getMessage(
                    'INFO_UPDATE_EL',
                    [
                        '#ID#' => $arFields['ID'],
                        '#new#' => $new_AUTHOR,
                        '#old#' => $old_AUTHOR,
                    ]
                );
                CEventLog::Add([
                    'AUDIT_TYPE_ID' => '«ex2_590 test 6',
                    'DESCRIPTION' => $mess,
                ]);
            }
        }
    }

    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $arUserList = CUser::getList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS_6']
            ],
        )->fetch();
        Event::$data['OLD_CLASS_AUTHOR'][$arFields['ID']] = $arUserList['UF_USER_CLASS_6'];
    }
    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $new_class = $arFields['UF_USER_CLASS_6'];
        $old_class = Event::$data['OLD_CLASS_AUTHOR'][$arFields['ID']];

        if ($new_class) {
            $arEnum = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $new_class,
                    'USER_FIELD_ID' => UF_USER_CLASS_6_ID
                ]
            )->fetch();
            $new_class = $arEnum['VALUE'];
        } else {
            $new_class = Loc::getMessage('NOT_CLASS');
        }

        if ($old_class) {
            $arEnum = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $old_class,
                    'USER_FIELD_ID' => UF_USER_CLASS_6_ID
                ]
            )->fetch();
            $old_class = $arEnum['VALUE'];
        } else {
            $old_class = Loc::getMessage('NOT_CLASS');
        }

        if ($old_class !=  $new_class) {
            $mess = [
                'OLD_USER_CLASS' => $old_class,
                'NEW_USER_CLASS' => $new_class,
            ];
            CEvent::Send(
                'EX2_AUTHOR_INFO_6',
                SITE_ID,
                $mess
            );
        }
    }

    public static function OnBeforeEventSendHandler(&$arFields, &$arTemplate)
    {
        global $APPLICATION;

        $arUserList = CUser::getList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['USER_ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS_6']
            ],
        )->fetch();

        if ($arUserList['UF_USER_CLASS_6']) {
            $arEnum = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $arUserList['UF_USER_CLASS_6'],
                    'USER_FIELD_ID' => UF_USER_CLASS_6_ID
                ]
            )->fetch();
            $class = $arEnum['VALUE'];
        } else {
            $class = Loc::getMessage('NOT_CLASS');
        }

        $arFields['CLASS'] = $class;
    }
}
