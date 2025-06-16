<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}


$arElement = CIBlockElement::GetList(
	["SORT" => "ASC"],
	['IBLOCK_ID' => ID_IBLOCK_REVIEWS],
	false,
	false,
	['*', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT']
);


while ($item = $arElement->Fetch()) {
	$arRew[] = $item;
}


$authorId = array_unique(array_column($arRew, 'PROPERTY_AUTHOR_VALUE'));
$arUsers = CUser::GetList(
	($by = "id"),
	($order = "desc"),
	['ID' => implode('|', $authorId), 'UF_AUTHOR_STATUS_3' => ID_STATUS_PUBLIC],
	['SELECT' => ['UF_AUTHOR_STATUS_3']]
);
while ($user = $arUsers->Fetch()) {
	$arUse[] = $user['ID'];
}

if (is_array($arUse)) {
	foreach ($arRew as $key => $value) {
		if (in_array($value['PROPERTY_AUTHOR_VALUE'], $arUse)) {
			$arResult['reviews'][$value['PROPERTY_PRODUCT_VALUE']] = $value['NAME'];
		}
	}
}