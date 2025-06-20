<?
AddEventHandler(
    "iblock",
    "OnBeforeIBlockElementAdd",
    array("Event", "OnBeforeIBlockElementAddHandler")
);
AddEventHandler(
    "iblock",
    "OnBeforeIBlockElementUpdate",
    array("Event", "OnBeforeIBlockElementUpdateHandler")
);
AddEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    array("Event", "OnAfterIBlockElementUpdateHandler")
);
AddEventHandler("main", "OnBeforeUserUpdate", array("Event", "OnBeforeUserUpdateHandler"));
AddEventHandler("main", "OnAfterUserUpdate", array("Event", "OnAfterUserUpdateHandler"));

AddEventHandler('main', 'OnBeforeEventSend', array("Event", "OnBeforeEventSendHandler"));


use Bitrix\Main\Localization\Loc;

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
                $APPLICATION->ThrowException('Имя входа должно быть заполнено.');
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
                $APPLICATION->ThrowException('Имя входа должно быть заполнено.');
                return false;
            }

            $arRevList = CIBlockElement::GetList(
                ['SORT' => 'ACS'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT']
            )->fetch();

            if ($arRevList['PROPERTY_AUTHOR_VALUE']) {

                $old_author = $arRevList['PROPERTY_AUTHOR_VALUE'];
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
            $arRevList = CIBlockElement::GetList(
                ['SORT' => 'ACS'],
                [
                    'IBLOCK_ID' => REV_IBLOCK_ID,
                    'ID' => $arFields['ID'],
                ],
                false,
                false,
                ['ID', 'IBOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT']
            )->fetch();

            if ($arRevList['PROPERTY_AUTHOR_VALUE']) {
                $new_author = $arRevList['PROPERTY_AUTHOR_VALUE'];
            } else {
                $new_author = Loc::getMessage('NOT_AUTHOR');
            }

            $old_author = Event::$data['OLD_AUTHOR'][$arFields['ID']];

            if ($new_author != $old_author) {
                $mess = Loc::getMessage('CEventLog', [
                    '[ID]' => $arFields['ID'],
                    '[был ID автора]' => $old_author,
                    '[стал ID автора]' => $new_author,
                ]);

                CEventLog::Add([
                    'AUDIT_TYPE_ID' => '«ex2_590»',
                    'DESCRIPTION' => $mess,
                ]);
            }
        }
    }
    public static function OnBeforeUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $arUser = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID']
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS_3']
            ],
        )->fetch();

        Event::$data['OLD_CLASS'][$arFields['ID']] = $arUser['UF_USER_CLASS_3'];
    }
    public static function OnAfterUserUpdateHandler(&$arFields)
    {
        global $APPLICATION;
        $old_class = Event::$data['OLD_CLASS'][$arFields['ID']];
        $new_class = $arFields['UF_USER_CLASS_3'];


        if ($old_class) {
            $rrvd = CUserFieldEnum::GetList(
                [],
                ['ID' => $old_class]
            )->fetch();
            $old_class = $rrvd['VALUE'];
        } else {
            $old_class = Loc::getMessage('NOT_CLASSS');
        }

        if ($new_class) {
            $rrvd = CUserFieldEnum::GetList(
                [],
                ['ID' => $new_class]
            )->fetch();
            $new_class = $rrvd['VALUE'];
        } else {
            $new_class = Loc::getMessage('NOT_CLASSS');
        }

        if ($new_class != $old_class) {
            $mess = [
                'OLD_USER_CLASS' => $old_class,
                'NEW_USER_CLASS' => $new_class,
            ];

            CEvent::Send(
                'EX2_AUTHOR_INFO_4',
                SITE_ID,
                $mess
            );
        }
    }

    public static function OnBeforeEventSendHandler(&$arFields, &$arTemplate)
    {
        global $APPLICATION;

        $arUser = CUser::GetList(
            ($by = 'id'),
            ($order = 'asc'),
            [
                'ID' => $arFields['ID']
            ],
            [
                'FIELDS' => ['ID'],
                'SELECT' => ['UF_USER_CLASS_3'],
            ],
        )->fetch();

        if ($arUser['UF_USER_CLASS_3']) {
            $ar = CUserFieldEnum::GetList(
                [],
                [
                    'ID' => $arUser['UF_USER_CLASS_3'],
                    'USER_FIELD_ID' => UF_USER_CLASS_3_ID,
                ]
            )->fetch();
            $class = $ar['VALUE'];
        } else {
            $class = Loc::getMessage('NOT_CLASS');
        }
        $arFields['CLASS'] = $class;
    }
}
