<?
use Bitrix\Main\Loader;

function testAgent($old_timestamp = null)
{
    if ($old_timestamp != null) {
        Loader::includeModule('iblock');

        $ar = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL'),
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID'],
        );

        $arRev = [];
        while ($el = $ar->fetch()) {
            $arRev[] = $el;
        }

        $count = count($arRev);

        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => 'testAgent',
                'DESCRIPTION' => $count,

            ]
        );
    }
    return 'testAgent(' . time() . ');';
}