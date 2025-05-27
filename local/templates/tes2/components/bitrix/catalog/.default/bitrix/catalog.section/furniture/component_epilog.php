<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$meta = $APPLICATION->GetProperty('ex2_meta_tes2');

if (str_contains($meta, '#count#')) {
    $meta = str_replace('#count#', $arResult['COUNT_PRODUCT_REVIEWS'], $meta);
}

$APPLICATION->SetPageProperty('ex2_meta_tes2', $meta);

