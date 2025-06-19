<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float) $arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

$arItem = array_column($arResult['ITEMS'], 'ID');

$arRevItem = CIBlockElement::GetList(
	['PROPERTY_AUTHOR' => 'DESC'],
	[
		'IBLOCK__ID' => 9,
		'ACTIVE' => 'Y',
		'PROPERTY_PRODUCT' => $arItem,
	],
	false,
	false,
	['ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
);

while ($el = $arRevItem->fetch()) {
	$arRev[] = $el;
}

$arUserId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));

$arUsers = CUser::GetList(
	($by = "ID"),
	($order = "desc"),
	[
		'ID' => implode('|', $arUserId),
		'UF_AUTHOR_STATUS_3' => 342,
	],
	['ID', 'SELECT' => ['UF_AUTHOR_STATUS_3']]
);
while ($el = $arUsers->fetch()) {
	$users[] = $el['ID'];
}

if (is_array($users)) {
	foreach ($arRev as $key => $value) {
		if (in_array($value['PROPERTY_AUTHOR_VALUE'], $users)) {
			$arResult['REV'][$value['PROPERTY_PRODUCT_VALUE']][] = $value['NAME'];
		}
	}
}

$meta = $APPLICATION->GetProperty('ex2_meta');
$len = count($arRev);
if (str_contains($meta, '#count#')) {
	$meta = str_replace('#count#', $len, $meta);
}
$APPLICATION->SetPageProperty('ex2_meta', $meta);

if ($arRev) {
	$arResult['FirestRev'] = $arRev[0]['NAME'];
}

