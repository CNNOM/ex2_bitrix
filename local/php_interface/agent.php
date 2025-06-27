<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

function Agent_ex_610($old_timestamp = null)
{

    if ($old_timestamp != null) {
        Loader::includeModule("iblock");

        $res = CIBlockElement::GetList(
            ['SORT' => 'asc'],
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
        while ($item = $res->fetch()) {
            $arRev[] = $item;
        }

        $count = count($arRev);

        $mess = Loc::getMessage(
            'AGENT',
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
