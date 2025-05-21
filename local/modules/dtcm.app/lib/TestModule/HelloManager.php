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


    public static function onAfterElementUpdate(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] != 4) {
            return; 
        }

        $elementId = $arFields['ID'];
        $authorPropId = 25; 

        $propValues = $arFields['PROPERTY_VALUES'][$authorPropId] ?? [];
        $newAuthorId = !empty($propValues) ? reset($propValues)['VALUE'] : null;

        $dbRes = \CIBlockElement::GetProperty(
            $arFields['IBLOCK_ID'],
            $elementId,
            [],
            ['CODE' => 'AUTHOR']
        );

        if ($arProp = $dbRes->Fetch()) {
            $oldAuthorId = $arProp['VALUE'];

            if ($oldAuthorId != $newAuthorId) {
                \CEventLog::Add([
                    'SEVERITY' => 'INFO',
                    'AUDIT_TYPE_ID' => 'ex2_590',
                    'MODULE_ID' => 'iblock',
                    'ITEM_ID' => $elementId,
                    'DESCRIPTION' => sprintf(
                        'В рецензии [%s] изменился автор с [%s] на [%s]',
                        $elementId,
                        $oldAuthorId,
                        $newAuthorId
                    ),
                ]);
            }
        }
    }
}
