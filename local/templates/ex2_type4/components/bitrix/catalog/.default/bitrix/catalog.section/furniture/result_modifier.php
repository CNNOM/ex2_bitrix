<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

define('ID_STATUS_PUBLIC', 319);
define('ID_IBLOCK_REW', 4);

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] = number_format((float)$arItem['PRICES']['PRICE']['PRINT_VALUE'], 0, '.', ' ');
	$arItem['PRICES']['PRICE']['PRINT_VALUE'] .= ' ' . $arItem['PROPERTIES']['PRICECURRENCY']['VALUE_ENUM'];

	$arResult['ITEMS'][$key] = $arItem;
}

// товоры
$productsId = array_column($arResult['ITEMS'], 'ID');

// получение рецензий
$rsElement = CIBlockElement::GetList(
	$arOrder  = array("SORT" => "ASC"),
	$arFilter = array(
		"IBLOCK_ID" => ID_IBLOCK_REW,
		"PROPERTY_PRODUCT" => $productsId,
		"ACTIVE" => "Y"
	),
	false,
	false,
	$arSelectFields = array("ID", "NAME", "IBLOCK_ID", "PROPERTY_AUTHOR", "PROPERTY_PRODUCT")
);
while ($arElement = $rsElement->fetch()) {
	$reviews[] = $arElement;
}


// полученеи пользователей которые имеютстатус публикуюца
$authorsId = array_unique(array_column($reviews, 'PROPERTY_AUTHOR_VALUE'));
$rsUsers = CUser::GetList(
	($by = "id"),
	($order = "desc"),
	['ID' => implode('|', $authorsId), 'UF_AUTHOR_STATUS' => 	ID_STATUS_PUBLIC],
	['SELECT' => ['UF_AUTHOR_STATUS']]

);
while ($arUsers = $rsUsers->fetch()) {
	$validAuthors[] = $arUsers['ID'];
}


// проверка на пользователей и вывод рецензий
if (is_array($validAuthors)) {
	foreach ($reviews as $key => $value) {
		if (in_array($value['PROPERTY_AUTHOR_VALUE'], $validAuthors)) {
			$arResult['filtredRev'][$value['PROPERTY_PRODUCT_VALUE']][] = $value['NAME'];
		}
	}
}


// подсщёт товраов с рецензиями
$count = count($arResult['filtredRev']);
$arResult['count'] = $count;
$this->__component->SetResultCacheKeys(['count']);

