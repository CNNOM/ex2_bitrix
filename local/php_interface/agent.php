<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

function Agent_ex_610_test_6($old_timestamp = null)
{
    if ($old_timestamp != null) {
        Loader::includeModule('iblock');

        $arRewList = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => REV_IBLOCK_ID,
                'ACTIVE' => "Y",
                '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL')
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID'],
        );

        $rev = [];
        while ($el = $arRewList->fetch()) {
            $rev[] = $el;
        }

        $count = count($rev);


        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'Agent_ex_610_test_6',
            'DESCRIPTION' => $count,
        ]);
    }

    return  'Agent_ex_610_test_6(' . time() . ');';
}
