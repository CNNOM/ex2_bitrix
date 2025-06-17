<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

$arRev = CIBlockElement::GetList(
	["SORT" => "ASC"],
	[
		'IBLOCK_ID' => REVIEWS_IBLOCK_ID,
		"ACTIVE" => "Y",
	],
	false,
	false,
	['*', 'PROPERTY_PRODUCT', 'PROPERTY_AUTHOR']
);

while ($rev = $arRev->fetch()) {
	$arRevEl[] = $rev;
}

$arAuthor = array_unique(array_column($arRevEl, 'PROPERTY_AUTHOR_VALUE'));
$arUser = CUser::GetList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $arAuthor),
		'UF_AUTHOR_STATUS_3' => 	REW_STATUS
	],
	['*', 'SELECT' => ['UF_USER_CLASS_3', 'UF_AUTHOR_STATUS_3']]
);
while ($user = $arUser->fetch()) {
	$arUsers[] = $user['ID'];
}

if (is_array($arAuthor)) {
	foreach ($arRevEl as $key => $value) {
		if (in_array($value['PROPERTY_AUTHOR_VALUE'], $arUsers)) {
			$arResult['rev'][$value['PROPERTY_PRODUCT_VALUE']][] =  $value['NAME'];
		}
	}
}


$firstKey = array_key_first($arResult['rev']);

$arResult['firstEl'] = $arResult['rev'][$firstKey][0];


$meta = $APPLICATION->GetProperty('ex2_meta');
$count = count($arRevEl);

if (str_contains($meta, '#count#')) {
	$meta = str_replace('#count#', $count, $meta);
	$APPLICATION->SetPageProperty('ex2_meta', $meta);
}
