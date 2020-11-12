<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$banners_type = array(
    "Верхний слайдер" => "Верхний слайдер",
    "Баннеры после рекоммендаций" => "Баннеры после рекоммендаций"
);

//формирование массива параметров
$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "BANNER_TYPE" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Тип баннера",
            "TYPE" => "LIST",
            "VALUES" => $banners_type
        ),
        "DETECT_MOBILE" => array(
            "PARENT" => "VISUAL",
            "NAME" => "Выводить отдельные баннеры для мобильных устройств",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y"
        ),
    ),
);