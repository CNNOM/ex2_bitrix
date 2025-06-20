<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
function Agent_ex_610($old_timestamp = null)
{

    if ($old_timestamp != null) {

        Loader::includeModule('iblock');

        $arRevItem = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => REV_IBLOCK_ID,
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL'),
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID'],
        );

        $arRev = [];
        while ($item = $arRevItem->fetch()) {
            $arRev[] = $item;
        }
        $count = count($arRev);

        $mess = Loc::getMessage(
            'INFO_UPDATE_REV_ELEMENT',
            [
                '#time#' => ConvertTimeStamp($old_timestamp, 'FULL'),
                '#count#' => $count,
            ]
        );

        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => '«ex2_610»',
                'DESCRIPTION' => $mess,
            ]
        );
    }
    return 'Agent_ex_610(' . time() . ');';
}
