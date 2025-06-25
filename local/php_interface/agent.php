<?php

use Bitrix\Main\Loader;

function Agent_ex_610($old_timestamp = null)
{
    if ($old_timestamp != null) {
        Loader::includeModule("iblock");

        $arRevItems = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => 'Y',
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL'),
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME'],
        );

        $ar = [];
        while ($el = $arRevItems->fetch()) {

            $ar[] = $el;
        }

        $count = count($ar);

        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => 'Agent_ex_610',
                'DESCRIPTION' => $count,
            ]
        );
    }
    return 'Agent_ex_610(' . time() . ');';
}
