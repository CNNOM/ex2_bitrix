<?php

namespace Local\TestModule;

class HelloManager
{
    public static function onBeforeElementAddUpdate(&$arFields)
    {

        if ($arFields['IBLOCK_ID'] != 4) {
            return true;
        }

        // Проверка длины анонса
        $previewText = trim($arFields['PREVIEW_TEXT']);
        if (mb_strlen($previewText, 'UTF-8') < 5) {
            $GLOBALS['APPLICATION']->ThrowException(
                'Текст анонса слишком короткий: ' . mb_strlen($previewText, 'UTF-8') . ', а должен быть не меньше 5'
            );
            return false;
        }

        // Удаление плейсхолдера #del#
        if (strpos($previewText, '#del#') !== false) {
            $arFields['PREVIEW_TEXT'] = str_replace('#del#', '', $previewText);
        }

        return true;
    }
}
