<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = [
    "NAME" => 'Меню в персональном разделе',
    "DESCRIPTION" => 'Боковое меню в персональном разделе пользователя',
    "COMPLEX" => "N",
    "PATH" => [
        "ID" => 'Vostroy',
        "CHILD" => [
            'ID' => 'Personal',
            'NAME' => "Персональный раздел"
        ]
    ],
];
?>