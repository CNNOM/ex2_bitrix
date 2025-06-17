<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float) $arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}


$arRevIblock = CIBlockElement::GetList(
	['SORT' => 'ASC'],
	[
		'IBLOCK_ID' => REVIEWS_IBLOCK_ID,
		'ACTIVE' => 'Y'
	],
	false,
	false,
	['*', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT']
);

while ($element = $arRevIblock->fetch()) {
	$arRev[] = $element;
}

$arAuthorId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));

$arUser = CUser::GetList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $arAuthorId),
		'UF_AUTHOR_STATUS_3' => AUTHOR_STATUS,
	],
	['SELECT' => ['UF_USER_CLASS_3', 'UF_AUTHOR_STATUS_3']]
);

while ($element = $arUser->fetch()) {
	$arUsers[] = $element['ID'];
}

if ($arUsers) {
	foreach ($arRev as $key => $value) {
		if (in_array($value['PROPERTY_AUTHOR_VALUE'], $arUsers)) {
			$arResult['REV'][$value['PROPERTY_PRODUCT_VALUE']][] = $value['NAME'];
		}
	}
}


$meta = $APPLICATION->GetProperty('ex2_meta');
$count = count($arRev);
if(str_contains($meta, '#count#')){
	$meta = str_replace('#count#', $count, $meta);
}
$APPLICATION->SetPageProperty('ex2_meta', $meta);


$arResult['FIRST_REV'] = $arRev[0]['NAME'];

// $APPLICATION->RestartBuffer();
// echo '<pre>';
// print_r($arResult['FIRST_REV']);
// echo '</pre>';
// exit();