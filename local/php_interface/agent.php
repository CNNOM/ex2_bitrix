<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
function Agent_ex_610_444($old_timestamp = null)
{
    if ($old_timestamp != null) {

        $arFilter = [
            "ACTIVE" => "Y",
            ">TIMESTAMP_X" => ConvertTimeStamp($old_timestamp, 'FULL'),
            "IBLOCK_ID" => REV_IBLOCK_ID
        ];
        Loader::includeModule('iblock');
        Loader::includeModule("iblock");

        $ar = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );
        $arRev = [];
        while ($el = $ar->fetch()) {
            $arRev[] = $el;
        }

        $count = count($arRev);

        $mess = Loc::getMessage('AGENT_MESSAGE', [
            '#date#' => FormatDate('d.m.Y H:i:s', $old_timestamp),
            '#count#' => $count
        ]);

        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'ex2_610_444',
            'DESCRIPTION' => $mess
        ]);
    }
    return 'Agent_ex_610_444(' . time() . ');';
}