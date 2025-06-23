<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}


$arElId = array_column($arResult['ITEMS'], 'ID');



$arRewList = CIBlockElement::GetList(
	['SORT' => 'ASC'],
	[
		'IBLOCK_ID' => REV_IBLOCK_ID,
		'ACTIVE' => "Y",
		'PROPERTY_PRODUCT' => $arElId,
	],
	false,
	false,
	['ID', 'NAME', 'IBLOCK_ID', 'PROPERTY_AUTHOR', 'PROPERTY_PRODUCT'],
);

$arRew = [];
while ($el = $arRewList->fetch()) {
	$arRew[] = $el;
}

$arUserId = array_unique(array_column($arRew, 'PROPERTY_AUTHOR_VALUE'));
$arUserList = CUser::getList(
	($by = 'id'),
	($order = 'asc'),
	[
		'ID' => implode('|', $arUserId),
		'UF_AUTHOR_STATUS_6' => STATUS_PUBLICK
	],
	[
		'FIELDS' => ['ID'],
		'SELECT' => ['UF_AUTHOR_STATUS_6']
	],
);

$arUser = [];
while ($user = $arUserList->fetch()) {
	$arUser[] = $user['ID'];
}

$count = 0;
if (is_array($arUser)) {
	foreach ($arRew as $key => $item) {
		if (in_array($item['PROPERTY_AUTHOR_VALUE'], $arUser)) {
			$arResult['REV'][$item['PROPERTY_PRODUCT_VALUE']][] = $item['NAME'];
			$count++;

			if (!isset($arResult['FIRST_EL_REV'])) {
				$arResult['FIRST_EL_REV'] = $item['NAME'];
			}
		}
	}
}

$arResult['count'] = $count;
$this->__component->SetResultCacheKeys(['count']);
