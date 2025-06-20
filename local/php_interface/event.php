<?

use Bitrix\Main\Localization\Loc;

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", array("MyClass", "OnBeforeIBlockElementAddHandler"));
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
}
