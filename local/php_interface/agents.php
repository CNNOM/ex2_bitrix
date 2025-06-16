<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vtiful\Kernel\Format;

Loc::loadMessages(__FILE__);

function Agents_test_3($old_timestamp = null)
{
    $countElement = 'test';

    if ($old_timestamp !== null) {
        $arFilter = [
            "ACTIVE"        => "Y",
            ">TIMESTAMP_X"  => ConvertTimeStamp($old_timestamp, 'FULL'),
            "IBLOCK_ID"     => ID_IBLOCK_REVIEWS
        ];
        Loader::includeModule("iblock");

        $rsElement = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            $arFilter,
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );

        $arElements = [];
        while ($arRew = $rsElement->Fetch()) {
            $arElements[] = $arRew;
        }
        $count = count($arElements);

        $mess = Loc::getMessage(
            'AGENT_MESSAGE',
            [
                '#date#' => FormatDate('d.m.Y H:i:s', $old_timestamp),
                '#count#' => $count,
            ]
        );

        CEventLog::Add(
            [
                'AUDIT_TYPE_ID' => 'ex2_610',
                'DESCRIPTION'   => $mess
            ]
        );
    }

    return 'Agents_test_3(' . time() . ');';
}

function Agent_good($old_timestamp = null)
{
    if ($old_timestamp !== null) {
        // Loader::includeModule("iblock");

        // $arElement = CIBlockElement::GetList(
        //     ['SORT' => 'ASC'],
        //     [
        //         'ACTIVE' => 'Y',
        //         '>TIMESTAMP_X' => ConvertTimeStamp($old_timestamp, 'FULL'),
        //         'IBLOCK_ID' => ID_IBLOCK_REVIEWS,
        //     ],
        //     false,
        //     false,
        //     ["ID", "IBLOCK_ID"]
        // );

        $arFilter = [
            "ACTIVE"        => "Y",
            ">TIMESTAMP_X"  => ConvertTimeStamp($old_timestamp, 'FULL'),
            "IBLOCK_ID"     => ID_IBLOCK_REVIEWS
        ];
        Loader::includeModule("iblock");

        $rsElement = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            $arFilter,
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );
        $arElements = [];
        while ($arRew = $rsElement->Fetch()) {
            $arElements[] = $arRew;
        }
        $count = count($arElements);

        $mess = Loc::getMessage(
            'AGENT_MESSAGE',
            [
                '#date#' => FormatDate('d.m.Y H:i:s', $old_timestamp),
                '#count#' => $count
            ]
        );

        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'ex2_610________332',
            'DESCRIPTION' => $mess,
        ]);
    }

    return 'Agent_good(' . time() . ');';
}
