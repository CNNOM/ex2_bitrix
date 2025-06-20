<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

$arItemId = array_column($arResult['ITEMS'], 'ID');

$arRevItem = CIBlockElement::GetList(
	['SORT' => 'ASC'],
	[
		'ACTIVE' => 'Y',
		'PROPERTY_PRODUCT' => $arItemId,
		'IBLOCK_ID' => REV_IBLOCK_ID,
	],
	false,
	false,
	['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_PRODUCT', 'PROPERTY_AUTHOR'],
);

$arRev = [];
while ($el = $arRevItem->fetch()) {
	$arRev[] = $el;
}

$arUserId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));
$arUserItem = CUser::GetList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $arUserId),
		'UF_AUTHOR_STATUS_5' => USER_PUBLIC_GROUP_ID

	],
	[
		'FIELDS' => ['ID'],
		'SELECT' => ['UF_AUTHOR_STATUS_5', 'UF_USER_CLASS_5'],
	],
);

$arUser = [];
while ($el = $arUserItem->fetch()) {
	$arUser[] = $el['ID'];
}

$count = 0;
if (is_array($arUser)) {
	foreach ($arRev as $key => $item) {
		if (in_array($item['PROPERTY_AUTHOR_VALUE'], $arUser)) {
			$count++;
			$arResult['REV'][$item['PROPERTY_PRODUCT_VALUE']][] = $item['NAME'];
			if (!isset($arResult['FIRST_REV'])) {
				$arResult['FIRST_REV']= $item['NAME'];
			}
		}
	}
}

$arResult['COUNT_REV'] = $count;
$this->__component->SetResultCacheKeys(['COUNT_REV']);

