<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
function Agent2($old_timeStamp = null)
{
    Loader::includeModule('iblock');
    if ($old_timeStamp != null) {
        $arRez = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timeStamp, 'FULL'),
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID'],
        );

        $arRev = [];
        while ($el = $arRez->fetch()) {
            $arRev[] = $el;
        }

        $count = count($arRev);
        
        $mess = Loc::getMessage('INFO_REV', [
            '#old_timestamp#' => FormatDate('d.m.Y H:i:s', $old_timeStamp),
            '#count#' => ConvertTimeStamp($old_timeStamp, 'FULL'),
        ]);
        CEventLog::Add([
            'DESCRIPTION' => $mess
        ]);
    }

    return 'Agent2(' . time() . ');';
}
