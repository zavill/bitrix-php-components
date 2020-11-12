<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 */

CModule::IncludeModule('iblock');

//Если требуется определить устройство пользователя
if ($arParams['DETECT_MOBILE'] === "Y")
{
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/local/library/Mobile_Detect/Mobile_Detect.php'); // Подключаем скрипт
    $detect = new Mobile_Detect; // Создаём экземпляр класса

}

if ($arParams['DETECT_MOBILE'] === "Y" && $detect->isMobile())
{ //Если устройство - мобильный телефон и включена проверка на телефон
    $banner = "PROPERTY_BANNER_MOBILE";
}
else
{
    // Здесь код, который выводится, если устройство не мобильное
    $banner = "PROPERTY_BANNER_DESKTOP";
}

$arSelect = Array(
    "ID",
    'CODE',
    $banner
);
$arFilter = Array(
    "IBLOCK_ID" => 7,
    "ACTIVE_DATE" => "Y",
    "ACTIVE" => "Y",
    "PROPERTY_Banner_type_VALUE" => $arParams['BANNER_TYPE']
);
$res = CIBlockElement::GetList(Array() , $arFilter, false, Array() , $arSelect);

$arResult['BANNERS'] = [];

while ($ob = $res->GetNextElement())
{
    $fields = $ob->GetFields();
    if (empty($fields[$banner . "_VALUE"])) continue;
    $arResult['BANNERS'][$fields['ID']] = '<a href="/sales/' . $fields['CODE'] . '" title="" target="" class="carousel-item">
            <img src="' . CFile::GetPath($fields[$banner . "_VALUE"]) . '" alt="" title="" class="banner_image">
        </a>';
}
if (count($arResult['BANNERS']) == 0) return;
$this->IncludeComponentTemplate();

?>
