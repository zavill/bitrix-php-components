<?php
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\PriceMaths;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var array $arResult
 */
Loc::loadMessages(__FILE__);

if (!Loader::includeModule("sale"))
{
    ShowError(Loc::getMessage("SOA_MODULE_NOT_INSTALL"));

    return;
}
class SaleOrderAjax extends \CBitrixComponent
{
    protected $itemsDelivery;

    protected $basket;

    protected $deliveryPrice;

    //Получение информации о пользователе
    private function getUser()
    {
        global $USER;
        if ($USER->IsAuthorized())
        {
            $filter = Array(
                "ID" => $USER->GetID()
            );
            $rsUsers = CUser::GetList(($by = "NAME") , ($order = "desc") , $filter, array(
                'FIELDS' => array(
                    'PERSONAL_STREET',
                    'PERSONAL_PHONE',
                    'NAME',
                    'LAST_NAME',
                    'EMAIL'
                ) ,
                'SELECT' => array(
                    'UF_FLAT',
                    'UF_INDEX',
                    'UF_ENTRANCE',
                    'UF_FLOOR',
                    'UF_INTERPHONE',
                    'UF_COMMENT',
                    'UF_COORDS'
                )
            ));
            $arUser = $rsUsers->GetNext();
            foreach ($arUser as $key => $item)
            {
                $arResult[$key] = $item;
            }
            if ($arResult['NAME'] == '' || $arResult['LAST_NAME'] == '' || $arResult['EMAIL'] == '') $arResult['MAIN_INFO_CHECK_ERROR'] = 'Y';
            if ($arResult['PERSONAL_STREET'] == '') $arResult['ADDRESS_CHECK_ERROR'] = 'Y';
        }

        return $arResult;
    }

    //Получение товаров пользователя
    private function getItems()
    {
        $arResult = array();
        $products_in_cart = CSaleBasket::GetList(array() , // сортировка
            array(
                'FUSER_ID' => CSaleBasket::GetBasketUserID() ,
                'LID' => SITE_ID,
                'ORDER_ID' => NULL
            ) , false, // группировать
            false, // постраничная навигация
            array(
                'NAME',
                'PRODUCT_ID',
                'ID',
                'PRICE',
                'CURRENCY',
                'QUANTITY',
                'CATALOG_MEASURE',
                'CAN_BUY'
            ));
        foreach ($products_in_cart->arResult as $k => $product)
        {
            if ($product['CAN_BUY'] == 'N')
            {
                $this
                    ->basket
                    ->getItemById($product['ID'])->delete();
                continue;
            }
            $db_prop = CIBlockElement::GetProperty(5, $product["PRODUCT_ID"], array(
                "sort" => "asc"
            ) , Array(
                "CODE" => "CML2_LINK"
            ));
            $res = CIBlockElement::GetByID($db_prop->Fetch() ['VALUE']);
            $arMeasure = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($product["PRODUCT_ID"]);
            $product['MEASURE'] = $arMeasure[$product["PRODUCT_ID"]]['MEASURE']['SYMBOL_RUS'];
            if ($ar_res = $res->GetNext())
            {
                if ($ar_res["DETAIL_PICTURE"] != '') $product['PATH'] = CFile::GetPath($ar_res["DETAIL_PICTURE"]);
                else $product['PATH'] = "/local/components/bitrix/sale.basket.basket/templates/bootstrap_v4/images/no_photo.png";
            }
            $arResult[$product['ID']] = $product;
        }
        return $arResult;
    }

    //Установка доставки
    private function setShipment()
    {

        $this->createShipments(); // Создание отгрузок
        return $this->getShipments(); // Получение отгрузок

    }

    private function createShipments()
    {
        $this->prepareShipmentItems();
        $shipmentCollection = $this
            ->order
            ->getShipmentCollection();
        foreach ($this->itemsDelivery as $id => $time)
        {
            $shipment = $shipmentCollection->createItem(Bitrix\Sale\Delivery\Services\Manager::getObjectById('1'));
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach ($time as $item_id)
            {
                $item = $this
                    ->basket
                    ->getItemById($item_id);
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
            $shipment->setField('COMPANY_ID', $id);
        }
    }

    //Получение вариантов доставок
    private function getShipments()
    {
        $shipmentCollection = $this
            ->order
            ->getShipmentCollection();
        $shipments = array();
        foreach ($shipmentCollection as $shipment)
        {
            if ($shipment->isSystem()) continue;
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            $shipment->setField('CURRENCY', $this
                ->order
                ->getCurrency());
            $result = array(
                'ID' => $shipment->getField('COMPANY_ID') ,
                'PRICE' => null,
                'DATE' => null,
                'ITEMS' => array()
            );
            foreach ($shipmentItemCollection as $item)
            {
                $basketItem = $item->getBasketItem();
                if (!$basketItem->canBuy()) continue;
                $arItem = array(
                    'PRODUCT_ID' => $basketItem->getId() ,
                    'NAME' => $basketItem->getField('NAME') ,
                    'PRICE' => $basketItem->getPrice() , // за единицу
                    'FINAL_PRICE' => $basketItem->getFinalPrice() , // сумма
                    'QUANTITY' => $basketItem->getQuantity() ,
                    'WEIGHT' => $basketItem->getWeight() ,
                );
                $result['ITEMS'][] = $arItem;
            }
            $deliveryRes = $this->checkDelivery($shipment->getField('COMPANY_ID') , $shipmentItemCollection->getPrice());
            $shipment->setField('BASE_PRICE_DELIVERY', $deliveryRes['PRICE']);
            $result['DATE'] = $deliveryRes['DATE'];
            $result['PRICE'] = $deliveryRes['PRICE'];
            $result['NAME'] = $deliveryRes['NAME'];
            $shipments['DELIVERIES'][] = $result;
        }
        //$shipmentCollection->calculateDelivery();
        $this->deliveryPrice = $shipmentCollection->getPriceDelivery();
        if ($this->deliveryPrice > 0) $shipments['FINAL_PRICE'] = SaleFormatCurrency($this->deliveryPrice, $this
            ->order
            ->getCurrency());
        else $shipments['FINAL_PRICE'] = 'Бесплатно';
        return $shipments;
    }

    //Проверка дат доставки
    private function checkDelivery($seller_id, $price)
    {
        $arSelect = Array(
            "PROPERTY_ATT_delivery_name",
            "PROPERTY_ATT_delivery_price",
            "PROPERTY_ATT_delivery_price_free_from",
            "PROPERTY_ATT_delivery_Fri_from",
            "PROPERTY_ATT_delivery_Fri_to",
            "PROPERTY_ATT_delivery_Mon_from",
            "PROPERTY_ATT_delivery_Mon_to",
            "PROPERTY_ATT_delivery_Tue_from",
            "PROPERTY_ATT_delivery_Tue_to",
            "PROPERTY_ATT_delivery_Wed_from",
            "PROPERTY_ATT_delivery_Wed_to",
            "PROPERTY_ATT_delivery_Thu_from",
            "PROPERTY_ATT_delivery_Thu_to",
            "PROPERTY_ATT_delivery_Sat_from",
            "PROPERTY_ATT_delivery_Sat_to",
            "PROPERTY_ATT_process_delivery_day",
        );
        $arFilter = Array(
            "IBLOCK_ID" => IntVal('8') ,
            "ACTIVE" => "Y",
            "PROPERTY_ATT_seller_id" => $seller_id
        );
        $res = CIBlockElement::GetList(Array() , $arFilter, false, Array() , $arSelect);
        $arResult = $res->GetNext();
        $date = $this->checkDeliveryDate($arResult);
        if (!empty($arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) && $price >= $arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) $price = 0;
        else $price = $arResult['PROPERTY_ATT_DELIVERY_PRICE_VALUE'];
        return array(
            'PRICE' => $price,
            'DATE' => $date,
            'NAME' => $arResult['PROPERTY_ATT_DELIVERY_NAME_VALUE']
        );
    }

    private function checkDeliveryDate($datesArray)
    {
        $dateFounded = false;
        $startDate = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime());
        $processDays = false; //Просчитаны ли дни обработки заказа
        $counter = 0; //Количество проиндексированных дат
        $foundedDates = 0; //Количество найденных дат
        $shipmentsArray = array(); // Список всех дат
        while (!$dateFounded)
        {
            if ($counter > 60 || $foundedDates >= 7) return $shipmentsArray;
            $counter++;
            if ($this->isWeekend($startDate, $datesArray))
            {
                $startDate->add('+1 day');
                continue;
            }
            if (!$processDays)
            {
                $startDate->add('+' . $datesArray['PROPERTY_ATT_PROCESS_DELIVERY_DAY_VALUE'] . ' day');
                $processDays = true;
                continue;
            }
            $weekDay = strftime("%a", strtotime($startDate));
            $weekDay = mb_strtoupper($weekDay);
            if ($datesArray['PROPERTY_ATT_DELIVERY_' . $weekDay . '_FROM_VALUE'])
            {
                $shipmentsArray[] = FormatDate("j F", $startDate->getTimestamp()) . " с " . $datesArray['PROPERTY_ATT_DELIVERY_' . $weekDay . '_FROM_VALUE'] . " до " . $datesArray['PROPERTY_ATT_DELIVERY_' . $weekDay . '_TO_VALUE'];
                $startDate->add('+1 day');
                $foundedDates++;
            }
            else
            {
                $startDate->add('+1 day');
                continue;
            }
        }
    }

    private function isWeekend($date, $array)
    {
        $weekDay = date('w', strtotime($date));
        $weekDayWord = strftime("%a", strtotime($date));
        $weekDayWord = mb_strtoupper($weekDayWord);
        $date = $date->format("Y-m-d");
        $endWorkDay = strtotime($date . " " . $array['PROPERTY_ATT_DELIVERY_' . $weekDayWord . '_TO_VALUE']);
        return ($weekDay == 0 || ($weekDay == 6 && empty($array['PROPERTY_ATT_DELIVERY_SAT_FROM_VALUE'])) || empty($array['PROPERTY_ATT_DELIVERY_' . $weekDayWord . '_FROM_VALUE']) || strtotime(date('Y-m-d H:i')) > $endWorkDay);
    }

    //Группировка товаров по продавцу
    private function prepareShipmentItems()
    {
        foreach ($this->basket as $basketItem)
        {
            $created_by = $basketItem->getPropertyCollection()
                ->getPropertyValues() ['CREATED_BY']['VALUE'];
            $this->itemsDelivery[$created_by][] = $basketItem->getId();
        }
    }

    //Отображение экранов пользователю
    private function checkDisplay($array)
    {

        global $USER;

        if (!$USER->IsAuthorized() || $array['MAIN_INFO_CHECK_ERROR'] == 'Y' || $array['ADDRESS_CHECK_ERROR'] == 'Y') $arResult['DISPLAY_CHECKOUT_ERROR'] = 'Y';

        if ($array['MAIN_INFO_CHECK_ERROR'] == 'Y') $arResult['DISPLAY_MAIN_INFO_ERROR'] = 'Y';

        else if ($array['ADDRESS_CHECK_ERROR'] == 'Y') $arResult['DISPLAY_ADDRESS_ERROR'] = 'Y';

        if ($array['MAIN_INFO_CHECK_ERROR'] == 'Y' || !$USER->IsAuthorized())
        {
            $arResult['CURRENT_STEP'] = 1;
            if ($array['ADDRESS_CHECK_ERROR'] == 'Y' || !$USER->IsAuthorized()) $arResult['STEP_ADDRESS_ERROR'] = 'Y';
        }
        else if ($array['ADDRESS_CHECK_ERROR'] == 'Y')
        {
            $arResult['CURRENT_STEP'] = 2;
            $arResult['STEP_ADDRESS_ERROR'] = 'Y';
        }
        else
        {
            $arResult['CURRENT_STEP'] = 3;
        }

        return $arResult;
    }

    //Получение корзины
    private function getBasket()
    {

        $this->order = Bitrix\Sale\Order::create(SITE_ID, 1);
        $this
            ->order
            ->setPersonTypeId(1);
        $this
            ->order
            ->setBasket($this->basket);
        $discounts = $this
            ->order
            ->getDiscount();
        $discounts->getApplyResult();
        $coupons = $discounts->getApplyResult();
        foreach ($coupons['COUPON_LIST'] as $cupo_n => $ar_r)
        {
            $arResult['DISCOUNT']['COUPON'] = $ar_r['COUPON'];
            $arResult['DISCOUNT']['NAME'] = $coupons['DISCOUNT_LIST'][$ar_r['ORDER_DISCOUNT_ID']]['NAME'];
        }
        $arResult['DISCOUNTS_COUNT'] = count($coupons['DISCOUNT_LIST']);
        echo $this->deliveryPrice;
        $arResult['PRICE'] = CurrencyFormat($this
            ->basket
            ->getBasePrice() , $this
            ->order
            ->getCurrency());
        $showPrices = $discounts->getShowPrices();
        if (!empty($showPrices['BASKET']))
        {
            foreach ($showPrices['BASKET'] as $basketCode => $data)
            {
                $basketItem = $this
                    ->basket
                    ->getItemByBasketCode($basketCode);
                if ($basketItem instanceof Sale\BasketItemBase)
                {
                    $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                    $basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
                    $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                }
            }
        }
        $basketPrice = $this
            ->basket
            ->getPrice();
        $basketBasePrice = $this
            ->basket
            ->getBasePrice();
        $arResult['DISCOUNT']['PRICE'] = CurrencyFormat(PriceMaths::roundPrecision($basketBasePrice - $basketPrice) , $this
            ->order
            ->getCurrency());
        $arResult['WEIGHT'] = roundEx($this
                ->basket
                ->getWeight() / 1000);
        $arResult['ITEMS_COUNT'] = count($this
            ->basket
            ->getQuantityList());
        return $arResult;
    }

    private function prepareFinalPrice()
    {

        return CurrencyFormat($this
            ->order
            ->getPrice() , $this
            ->order
            ->getCurrency());

    }

    private function init()
    {
        $this->basket = \Bitrix\Sale\Basket::loadItemsForFUser(

            \Bitrix\Sale\Fuser::getId() ,

            \Bitrix\Main\Context::getCurrent()
                ->getSite()
        );
    }

    public function executeComponent()
    {
        $this->setFrameMode(false);

        $this->init();

        //USER info
        $arUser = $this->getUser();
        $arResult['USER'] = $arUser;

        //Display styles
        $display = $this->checkDisplay($arResult['USER']);
        $arResult['DISPLAY'] = $display;

        //Get products in basket
        $items = $this->getItems();
        $arResult['ITEMS'] = $items;

        //Get basket
        $basket = $this->getBasket();
        $arResult['BASKET'] = $basket;

        $shipments = $this->setShipment();
        $arResult['SHIPMENTS'] = $shipments;

        $finalPrice = $this->prepareFinalPrice();
        $arResult['BASKET']['PRICE_DISCOUNT'] = $finalPrice;

        $this->arResult = $arResult;
        //is included in all cases for old template
        $this->includeComponentTemplate();

    }
}

