<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$meta = $APPLICATION->GetProperty('ex2_meta_test_8');
if (str_contains($meta, '#count#')) {
    $meta = str_replace('#count#', $arResult['count'], $meta);
}
$APPLICATION->SetPageProperty('ex2_meta_test_8', $meta);
