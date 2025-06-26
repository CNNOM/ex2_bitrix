<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

// $arProductId = array_column($arResult['ITEMS'], 'ID');

// $arRevItems = CIBlockElement::GetList(
// 	['SORT' => 'ASC'],
// 	[
// 		'IBLOCK_ID' => REV_IBLOCK_ID,
// 		'ACTIVE' => 'Y',
// 		'PROPERTY_PRODUCT' => $arProductId,
// 	],
// 	false,
// 	false,
// 	['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_PRODUCT', 'PROPERTY_AUTHOR'],
// );


// $arRev = [];
// while ($item = $arRevItems->fetch()) {
// 	$arRev[] = $item;
// }

// $arUserRevId = array_unique(array_column($arRev, 'PROPERTY_AUTHOR_VALUE'));

// $ar = CUser::GetList(
// 	($by = 'id'),
// 	($order = 'asc'),
// 	[
// 		'ID' => implode('|', $arUserRevId),
// 		'UF_AUTHOR_STATUS_8' => USER_STATUS_PUBLIC
// 	],
// 	[
// 		'FIELDS' => ['ID'],
// 		'SELECT' => ['UF_AUTHOR_STATUS_8'],
// 	],
// );

// $arUsers = [];
// while ($item = $ar->fetch()) {
// 	$arUsers[] = $item['ID'];
// }

// $count = 0;
// if (is_array($arUsers)) {
// 	foreach ($arRev as $key => $item) {
// 		if (in_array($item['PROPERTY_AUTHOR_VALUE'], $arUsers)) {
// 			$arResult['REV'][$item['PROPERTY_PRODUCT_VALUE']][] = $item['NAME'];
// 			$count++;
// 		}
// 	}
// }

// $arResult['count'] = $count;
// $this->__component->SetResultCacheKeys(['count']);