<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

$arProductId = array_column($arResult['ITEMS'], 'ID');

$res = CIBlockElement::GetList(
	['SORT' => 'asc'],
	[
		'IBLOCK_ID' => REV_IBLOCK_ID,
		'ACTIVE' => 'Y',
		'PROPERTY_PRODUCT' => $arProductId,
	],
	false,
	false,
	['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT', 'ACTIVE'],
);

$arRev = [];
while ($item = $res->fetch()) {
	$arRev[] = $item;
}
$arUserRevId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));

$res = CUser::GetList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $arUserRevId),
		'UF_AUTHOR_STATUS' => UF_AUTHOR_STATUS_ID
	],
	[
		'FIELDS' => ['ID'],
		'SELECT' => ['UF_USER_CLASS', 'UF_AUTHOR_STATUS']
	],
);

$arValidUser = [];
while ($item = $res->fetch()) {
	$arValidUser[] = $item['ID'];
}

$count = 0;

if (is_array($arValidUser)) {
	foreach ($arRev as $key => $item) {
		if (in_array($item['PROPERTY_AUTHOR_VALUE'], $arValidUser)) {
			$arResult['REV'][$item['PROPERTY_PRODUCT_VALUE']][] = $item['NAME'];
			$count++;
			if (!isset($arResult['FIRST_REV'])) {
				$arResult['FIRST_REV'] = $item['NAME'];
			}
		}
	}
}

$arResult['count'] = $count;
$this->__component->SetResultCacheKeys(['count']);