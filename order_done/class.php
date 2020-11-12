<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use Bitrix\Sale;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var array $arResult
 */

class Order_Done extends \CBitrixComponent {

    protected $order;

    protected $fakeorder = true;

    public function prepareOrder(){
        global $USER;
        if(!empty($_GET['number'])) {
            /* ID заказа */
            $this->order = Sale\Order::load($_GET['number']);
            $orderUser = $this->order->getUserId();
            if ($USER->GetId() == $orderUser)
                $this->fakeorder = false;
        }
    }

    public function getProperties(){
        if(!$this->fakeorder) {
            $propertyCollection = $this->order->getPropertyCollection();
            $propertyCodeToId = [];

            foreach($propertyCollection as $propertyValue) {
                if($propertyValue->getField('CODE') === 'DELIVERY_DATES_JSON'){
                    $arResult['SHIPMENT_DATES'] = json_decode($propertyCollection->getItemByOrderPropertyId($propertyValue->getField('ORDER_PROPS_ID'))->getValue(), true);
                    break;
                }
            }

            $arResult['EMAIL'] = $propertyCollection->getUserEmail()->getValue();
            $arResult['ADDRESS'] = $propertyCollection->getAddress()->getValue();
            $arResult['PHONE'] = $propertyCollection->getPhone()->getValue();
            $arResult['NAME'] = $propertyCollection->getPayerName()->getValue();
            $arResult['WEIGHT'] = $this->order->getBasket()->getWeight() / 1000;
            $arResult['PRICE'] = CurrencyFormat($this->order->getField('PRICE'), $this->order->getCurrency());
            return $arResult;
        }
    }

    public function executeComponent() {
        $this->setFrameMode(false);

        $this->prepareOrder();

        if(!$this->fakeorder)
            $props = $this->getProperties();

        $this->arResult['FAKE_ORDER'] = $this->fakeorder;

        $this->arResult['ORDER'] = $props;

        $this->includeComponentTemplate();
    }
}

?>