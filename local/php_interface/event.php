<?

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

            if (mb_strlen($arFields['PREVIEW_TEXT']) < 5) {
                $APPLICATION->ThrowException(Loc::GetMessage('LEN_TEXT') . mb_strlen($arFields['PREVIEW_TEXT']));
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
                $APPLICATION->ThrowException(Loc::GetMessage('LEN_TEXT') . mb_strlen($arFields['PREVIEW_TEXT']));
                return false;
            }
            $arRevItem = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'ACTIVE' => 'Y',
                    'ID' => $arFields['ID'],
                    'IBLOCK_ID' => $arFields['IBLOCK_ID'],
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR'],
            )->fetch();

            if ($arRevItem['PROPERTY_AUTHOR_VALUE']) {
                $old_class = $arRevItem['PROPERTY_AUTHOR_VALUE'];
            } else {
                $old_class = Loc::GetMessage('NOT_AUTHOR');
            }

            Event::$data['OLD_AUTHOR'][$arFields['ID']] = $old_class;
        }
    }
    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $arRevItem = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'ACTIVE' => 'Y',
                'ID' => $arFields['ID'],
                'IBLOCK_ID' => $arFields['IBLOCK_ID'],
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR'],
        )->fetch();

        if ($arRevItem['PROPERTY_AUTHOR_VALUE']) {
            $new_class = $arRevItem['PROPERTY_AUTHOR_VALUE'];
        } else {
            $new_class = Loc::GetMessage('NOT_AUTHOR');
        }

        $old_class = Event::$data['OLD_AUTHOR'][$arFields['ID']];

        if ($old_class != $new_class) {
            $mess = Loc::GetMessage('INFO_UPDATE_REV', [
                '#ID#' => $arFields['ID'],
                '#old#' => $old_class,
                '#new#' => $new_class,
            ]);

            CEventLog::Add([
                'AUDIT_TYPE_ID' => '«ex2_590»',
                'DESCRIPTION' => $mess,
            ]);
        }
    }


    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        $arUserItem = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID'],
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS_5'],
            ],
        )->fetch();

        Event::$data['OLD_CLASS'][$arFields['ID']] = $arUserItem['UF_USER_CLASS_5'];
    }
    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $new_class = $arFields['UF_USER_CLASS_5'];
        $old_class = Event::$data['OLD_CLASS'][$arFields['ID']];

        if ($new_class) {
            $arProp = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $new_class,
                    'USER_FIELD_ID' => ID_UF_USER_CLASS_5,
                ],
            )->fetch();
            $new_class = $arProp['VALUE'];
        } else {
            $new_class =  Loc::GetMessage('NOT_CLASS');
        }

        if ($old_class) {
            $arProp = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $old_class,
                    'USER_FIELD_ID' => ID_UF_USER_CLASS_5,
                ],
            )->fetch();
            $old_class = $arProp['VALUE'];
        } else {
            $old_class =  Loc::GetMessage('NOT_CLASS');
        }

        if ($old_class != $new_class) {
            $mess = [
                'OLD_USER_CLASS' => $old_class,
                'NEW_USER_CLASS' => $new_class,
            ];
            CEvent::Send(
                'EX2_AUTHOR_INFO_TEST_5',
                SITE_ID,
                $mess
            );
        }
    }
}
