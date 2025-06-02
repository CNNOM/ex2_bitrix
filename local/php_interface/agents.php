<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
//ex2-610
//IncludeModuleLangFile(__FILE__);
Loc::loadMessages(__FILE__);
function Agent_ex_610($old_timestamp = null)
{
    
    if ($old_timestamp !== null) {
        // Фильтр: активные элементы, изменённые после предыдущего запуска
        $arFilter = [
            "ACTIVE"        => "Y",
            ">TIMESTAMP_X"  => ConvertTimeStamp($old_timestamp, 'FULL'), // Преобразуем в MySQL DATETIME
            "IBLOCK_ID"     => ID_IBLOCK_REVIEWS
        ];
        Loader::includeModule("iblock");
        //CModule::IncludeModule('iblock');
        $rsElement = CIBlockElement::GetList(
            ["SORT" => "ASC"],
            $arFilter,
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );

        // Собираем все элементы для подсчёта
        $arElements = [];
        while ($arRew = $rsElement->Fetch()) {
            $arElements[] = $arRew;
        }
        $count = count($arElements);

        // Формируем сообщение для лога
        $mess = Loc::getMessage('AGENT_MESSAGE', [
            '#date#'  => FormatDate('d.m.Y H:i:s', $old_timestamp), // Дата предыдущего запуска
            '#count#' => $count
        ]);

        // Запись в лог
        CEventLog::Add([
            'AUDIT_TYPE_ID' => 'ex2_610',
            'DESCRIPTION'   => $mess
        ]);
    }
    // Возвращаем вызов агента с текущим временем для следующего запуска
    return 'Agent_ex_610(' . time() . ');';
}
