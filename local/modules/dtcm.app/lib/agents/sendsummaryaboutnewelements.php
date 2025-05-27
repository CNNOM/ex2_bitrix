<?php
namespace Dtcm\App\Agents;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Type\DateTime as BitrixDateTime;

Loc::loadMessages(__FILE__);

class SendSummaryAboutNewElements
{
	public static function run($lastTimeExec = "")
	{
		if (Loader::includeModule("iblock")) {

			$result = static::prepareResult($lastTimeExec);

			if ($result) {
				$elementTable = static::prepareHtml($result);

				// Event::sendImmediate([
				// 	"EVENT_NAME" => "ELEMENTS_SUMMARY",
				// 	"LID" => MY_SITE_ID,
				// 	"C_FIELDS" => [
				// 		"ELEMENTS_COUNT" => count($result),
				// 		"ELEMENTS_TABLE" => $elementTable,
				// 	],
				// ]);
			}
		}

		return "\\" . __METHOD__ . "(\"" . (new BitrixDateTime())->toString() . "\");";
	}

	protected static function prepareResult($lastTimeExec = "")
	{
		$query = new Query(ElementTable::getEntity());

		$query->setSelect([
			"ID",
			"NAME",
			"IBLOCK_ID",
			"IBLOCK_NAME" => "IBLOCK.NAME"
		]);
		$query->setFilter([
			">DATE_CREATE" => $lastTimeExec ?: (new BitrixDateTime())->add("-1 day"),
		]);
		$query->setOrder([
			"IBLOCK_ID" => "ASC",
		]);
		$query->registerRuntimeField("IBLOCK", [
			"data_type" => IblockTable::class,
			"reference" => [
				"=this.IBLOCK_ID" => "ref.ID"
			]
		]);

		return $query->exec()->fetchAll();
	}

	protected static function prepareHtml($data)
	{
		$iblockIds = array_unique(array_column($data, "IBLOCK_ID"));

		$elementTable = "<table>";

		foreach ($iblockIds as $iblockId) {
			$iblockElements = array_filter($data, function ($el) use ($iblockId) {
				return $el["IBLOCK_ID"] == $iblockId;
			});

			$elementTable .= "<tr><th colspan='2'>" . Loc::getMessage("ACADEMY_IBLOCK_NAME") . ": " .
				$iblockElements[0]["IBLOCK_NAME"] . "(" . $iblockId . ")"
				. "</th></tr>";
			$elementTable .= "<tr><th colspan='2'>" . Loc::getMessage("ACADEMY_NEW_ELEMENTS_COUNT") . ":" .
				count($iblockElements) . "</th></tr>";
			$elementTable .= "<tr><th>" . Loc::getMessage("ACADEMY_ELEMENT_ID") . "</th><th>" .
				Loc::getMessage("ACADEMY_ELEMENT_NAME") . "</th></tr>";

			foreach ($iblockElements as $element) {
				$elementTable .= "<tr><td>" . $element["ID"] . "</td><td>" . $element["NAME"] . "</td></tr>";
			}
		}

		$elementTable .= "</table>";

		return $elementTable;
	}
}