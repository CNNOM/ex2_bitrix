<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

$arItemsId = array_column($arResult['ITEMS'], 'ID');

$arRevEl = CIBlockElement::GetList(
	['SORT' => 'ASC'],
	[
		'IBLOCK_ID' => REV_IBLOCK_ID,
		'ACTIVE' => 'Y',
		'PROPERTY_PRODUCT' => $arItemsId,
	],
	false,
	false,
	['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
);

$arRev = [];
while ($el = $arRevEl->fetch()) {
	$arRev[] = $el;
}

$userRevsId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));
$arUsers = CUser::GetList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $userRevsId),
		'UF_AUTHOR_STATUS_7' => GROUP_PUBLIC_USER,
	],
	[
		'FIELDS' => ['ID'],
		'SELECT' => ['UF_AUTHOR_STATUS_7']
	],
);

$users = [];
while ($el = $arUsers->fetch()) {
	$users[] = $el['ID'];
}

$count = 0;
if (is_array($users)) {
	foreach ($arRev as $key => $item) {
		if (in_array($item['PROPERTY_AUTHOR_VALUE'], $users)) {
			$arResult['REV'][$item['PROPERTY_PRODUCT_VALUE']][] = $item['NAME'];
			$count++;
		}
	}
}

$arResult['count'] = $count;
$this->__component->SetResultCacheKeys(['count']);

