<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */
foreach ($arResult['SHIPMENTS']['DELIVERIES'] as &$shipment){
    $count = count($shipment['ITEMS']) % 10;
    if ($count == '1')
    {
        $count_name = 'товар';
    }
    elseif ($count >= '2' && $count <= '4')
    {
        $count_name = 'товаров';
    }
    else
    {
        $count_name = 'товара';
    }
    $shipment['ITEMS_COUNT'] = $count;
    $shipment['COUNT_NAME'] = $count_name;
}
$component = $this->__component;