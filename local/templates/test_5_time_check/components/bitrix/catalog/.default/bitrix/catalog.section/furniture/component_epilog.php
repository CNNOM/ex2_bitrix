<?

$mess = $APPLICATION->GetProperty('ex2_meta_test_5');
$count = $arResult['COUNT_REV'];
if (str_contains($mess, '#count#')) {
    $mess = str_replace('#count#', $count, $mess);
}
$APPLICATION->SetPageProperty('ex2_meta_test_5', $mess);
