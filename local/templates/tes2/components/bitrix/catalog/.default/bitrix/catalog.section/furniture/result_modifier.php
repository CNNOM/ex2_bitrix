<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}
//------------------------------------------------------------------------------

if ($arResult['ITEMS']) {
	$productId = array_column($arResult['ITEMS'], "ID");

	$arIblockRev = CIBlockElement::GetList(
		['SORT' => "ASC"],
		[
			'IBLOCK_ID' => ID_IBLOCK_REVIEWS,
			'ACTIVE' => "Y",
			'PROPERTY_PRODUCT' => $productId,
		],
		false,
		false,
		["ID", "NAME", "PROPERTY_AUTHOR", "PROPERTY_PRODUCT"]

	);

	while ($rev = $arIblockRev->Fetch()) {
		$arRev[] = $rev;
	}


	if ($arRev && is_array($arRev)) {
		$arRevAuthor = array_column($arRev, 'PROPERTY_AUTHOR_VALUE');
		$arUsersList = CUser::GetList(
			($by = "id"),
			($order = "desc"),
			[
				"ID" => implode('|', $arRevAuthor),
				"UF_AUTHOR_STATUS_2" => ID_STATUS_PUBLIC,
			],
			[]
		);

		while ($user = $arUsersList->Fetch()) {
			$arUsers[] = $user['ID'];
		}


		if ($arUsers && is_array($arUsers)) {
			foreach ($arRev as $key => $value) {
				if (in_array($value['PROPERTY_AUTHOR_VALUE'], $arUsers)) {
					$arResult['REVIEWS'][$value['PROPERTY_PRODUCT_VALUE']][] = $value['NAME'];
				}
			}
		}
	}
}

if ($arResult['REVIEWS']) {
	$count = count($arResult['REVIEWS']);
	$arResult['COUNT_PRODUCT_REVIEWS'] = $count;
	$this->__component->SetResultCacheKeys(['COUNT_PRODUCT_REVIEWS']);
}
