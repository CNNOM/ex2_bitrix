<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

//ex2-610
Loc::loadMessages(__FILE__);
function Agent_ex_610($old_timestamp = null)
{
    if ($old_timestamp !== null) {
        $rsElement = CIBlockElement::GetList(
            $arOrder = array("SORT" => "ASC"),
            $arFilter = array(
                "ACTIVE" => "Y",
                ">TIMESTAMP_X" => ConvertTimeStamp($old_timestamp, 'FULL'),
                "IBLOCK_ID" => ID_IBLOCK_REW
            ),
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );
        $arElements = [];
        while ($arElement = $rsElement->fetch()) {
            $arElements[] = $arElement;
        }
        $count = count($arElements);

        $mess = Loc::getMessage('AGENT_MESSAGE', [
            '#date#' => FormatDate('d.m.Y H:i:s', $old_timestamp),
            '#count#' => $count
        ]);

        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'ex2_610',
            'DESCRIPTION' => $mess
        ]);
    }
    return 'Agent_ex_610(' . time() . ');';

}