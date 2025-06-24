<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
function Agent_ex_610($old_timestamp = null)
{
    Loader::includeModule("iblock");
    if ($old_timestamp != null) {
        $arRevItem = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL')
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
            'INFO_AGENT',
            [
                '#time#' => ConvertTimeStamp($old_timestamp, 'FULL'),
                '#count#' => $count,
            ]
        );
        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'Agent_ex_610',
            'DESCRIPTION' => $mess,
        ]);

    }
    return 'Agent_ex_610(' . time() . ');';
}