<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = [
    "NAME" => 'Оплата заказа',
    "DESCRIPTION" => 'Инициализация оплаты заказа',
    "COMPLEX" => "N",
    "PATH" => [
        "ID" => 'Vostroy',
        "CHILD" => [
            'ID' => 'ORDER',
            'NAME' => "Заказ",
            'CHILD' => [
                'ID' => 'Payment',
                'NAME' => 'Оплата заказа'
            ]
        ]
    ],
];
?>