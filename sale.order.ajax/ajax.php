<?php
use Bitrix\Main\Application;
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);
global $APPLICATION;
$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId) , 0, 2);
if (!empty($siteId) && is_string($siteId))
{
    define('SITE_ID', $siteId);
}
require ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\PriceMaths;
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/local/library/smsc_api.php');

if (isset($_POST['updateUser']))
{ //Обновление основной информации
    $user = new UpdateUser;
    echo json_encode($user->updateInfo());
    return;
}
else if (isset($_POST['CHECK_SMS']))
{ // Авторизация/регистрация
    $authorize = new Authorization;
    echo json_encode($authorize->initAuthorization());
    return;
}
else if ($_POST['set_address'])
{ // Обновление адреса
    $user = new UpdateUser;
    echo json_encode($user->updateAddress());
    return;
}
else if ($_POST['create_order'])
{ // Создание заказа
    $order = new CreateOrderClass;
    echo json_encode($order->createOrder());
    return;
}
else if ($_POST['discount_update'])
{
    $updateDiscount = new UpdateDiscount;
    echo json_encode($updateDiscount->initUpdate());
    return;
}
else if ($_POST['updateCheckout'])
{
    global $USER;
    ?>
    <div id="checkout_ajax_container">
        <div class="login-link" id="login-link">
            <a href="/personal/" id="login" class="login"><? ($USER->GetFullName() != '' ? print_r($USER->GetFullName()) : print_r($USER->GetLogin())) ?></a>
        </div>
        <? $APPLICATION->IncludeComponent("bitrix:sale.order.ajax", "bootstrap_v4", Array(
            "AJAX_REQUEST" => 'Y'
        )); ?>
    </div>
    <?
}
$request = Bitrix\Main\Application::getInstance()->getContext()
    ->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!Bitrix\Main\Loader::includeModule('sale')) return;

Bitrix\Main\Localization\Loc::loadMessages(dirname(__FILE__) . '/class.php');

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
    $signedParamsString = $request->get('signedParamsString') ? : '';
    $params = $signer->unsign($signedParamsString, 'sale.order.ajax');
    $params = unserialize(base64_decode($params));
}
catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
    die();
}

$action = $request->get($params['ACTION_VARIABLE']);
if (empty($action)) return;

// Класс авторизации по номеру телефона
class Authorization
{

    protected $numberSMS; //отформатированный СМС номер
    protected $smsText; //Текст СМС
    protected $secret = '6LdxoPYUAAAAALD2MEmrOBi06NawwV7JcoSlWTWd';
    protected $pass; //Код в СМС
    //Отправка СМС
    public function sendSMS()
    {

        self::prepareComponent();

        $resultCaptcha = $this->checkRecaptcha();
        if ($resultCaptcha['RESULT'] == 'ERROR') return $resultCaptcha;

        $_SESSION['SMS_COUNT_REQUEST']++;

        $userResult = self::checkUser();
        if ($userResult['TEXT'] == 'Authorized') return $userResult;

        send_sms($this->numberSMS, $this->smsText);
        return $userResult;
    }

    //Подготовка переменных
    public function prepareComponent()
    { //подготовка данных
        if (!isset($_POST['phone'])) return;
        $this->numberSMS = preg_replace('![^0-9]+!', '', $_POST['phone']); //Приводим номер к нужному формату
        // Генерируем пароль для смс
        for ($i = 0;$i < 4;$i++)
        {
            // Вычисляем произвольный индекс из массива
            $index = rand(1, 9);
            $this->pass .= $index;
        }

        $this->smsText = 'Код ' . $this->pass . '. Никому не сообщайте. Только для www.vostroy.com';
    }

    //Проверка капчи
    public function checkRecaptcha()
    {
        if ($_SESSION['SMS_COUNT_REQUEST'] >= $_SESSION['ReCaptchaAttempts'])
        {
            require_once ($_SERVER['DOCUMENT_ROOT'] . '/local/library/recaptchalib.php');
            if (isset($_POST['recaptcha']))
            {
                $response = $_POST["recaptcha"];
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $data = ['secret' => $this->secret, 'response' => $response];
                $options = ['http' => ['method' => 'POST', 'content' => http_build_query($data) ]];
                $context = stream_context_create($options);
                $verify = file_get_contents($url, false, $context);
                $captcha_success = json_decode($verify);
                if ($captcha_success->success == false)
                {
                    $arResult = array(
                        'RESULT' => 'ERROR',
                        'TEXT' => 'Invalid Captcha',
                        'Captcha' => $response
                    );
                    echo json_encode($arResult);
                    return $arResult;
                }
            }
            else
            {
                $arResult = array(
                    'RESULT' => 'ERROR',
                    'TEXT' => 'Captcha Unkown'
                );
                echo json_encode($arResult);
                return $arResult;
            }
        }
    }

    //Проверка информации о пользователе
    public function checkUser()
    {
        $user = new CUser;
        $filter = Array(
            "PERSONAL_PHONE" => $_POST['phone']
        );
        $rsUsers = CUser::GetList(($by = "NAME") , ($order = "desc") , $filter, array(
            'FIELDS' => array(
                'ID',
                'PERSONAL_PHONE',
                'NAME',
                'LAST_NAME',
                'EMAIL',
                'PERSONAL_STREET'
            ) ,
            'SELECT' => array(
                'UF_LAST_IP',
                'UF_FLAT',
                'UF_INDEX',
                'UF_ENTRANCE',
                'UF_FLOOR',
                'UF_INTERPHONE',
                'UF_COMMENT'
            )
        ));
        if ($arUser = $rsUsers->Fetch())
        { //Если пользователь найден
            if ($arUser['UF_LAST_IP'] == \Bitrix\Main\Service\GeoIP\Manager::getRealIp())
            {
                $user->Update($arUser['ID'], Array(
                    'UF_LAST_IP' => \Bitrix\Main\Service\GeoIp\Manager::getRealIp()
                ));
                return $this->successLogin($arUser);
            }
            $user->Update($arUser['ID'], Array(
                'UF_SMS_CODE' => $this->pass
            ));
            $arResult = Array(
                'RESULT' => 'Okay',
                'TEXT' => 'Authorization',
                'UF_LAST_IP' => $arUser['UF_LAST_IP'],
                'IP' => \Bitrix\Main\Service\GeoIp\Manager::getRealIp()
            );
        }
        else
        { //Если пользователя не существует
            $arFields = Array(
                'PERSONAL_PHONE' => $_POST['phone'],
                "LOGIN" => $_POST['phone'],
                'PASSWORD' => 'LWjdANzO',
                "LID" => "ru",
                "ACTIVE" => "N",
                "GROUP_ID" => array(
                    6
                ) ,
                'UF_SMS_CODE' => $this->pass
            );
            $user->Add($arFields);
            $arResult = Array(
                'RESULT' => 'Okay',
                'TEXT' => 'Registration'
            );
        }
        return $arResult;
    }

    //Авторизация пользователя
    public function successLogin($arUser)
    {
        global $USER;
        $arResult = Array(
            'RESULT' => 'Okay',
            'TEXT' => 'Authorized',
            'NAME' => $arUser['NAME'],
            'LAST_NAME' => $arUser['LAST_NAME'],
            'EMAIL' => $arUser['EMAIL'],
            'PERSONAL_STREET' => $arUser['PERSONAL_STREET'],
            'UF_FLAT' => $arUser['UF_FLAT'],
            'UF_INDEX' => $arUser['UF_INDEX'],
            'UF_ENTRANCE' => $arUser['UF_ENTRANCE'],
            'UF_FLOOR' => $arUser['UF_FLOOR'],
            'UF_INTERPHONE' => $arUser['UF_INTERPHONE'],
            'UF_COMMENT' => $arUser['UF_COMMENT']
        );
        $_SESSION['SMS_COUNT_REQUEST'] = 0;
        $USER->Authorize($arUser['ID']); // авторизуем
        return $arResult;
    }

    //Проверка кода СМС
    public function checkSMS()
    {
        $user = new CUser;
        $filter = Array(
            "PERSONAL_PHONE" => $_POST['phone'],
            'UF_SMS_CODE' => $_POST['code']
        );
        $rsUsers = CUser::GetList(($by = "NAME") , ($order = "desc") , $filter, array(
            'FIELDS' => array(
                'ID',
                'NAME',
                'LAST_NAME',
                'EMAIL',
                'PERSONAL_STREET'
            ) ,
            'SELECT' => array(
                'UF_FLAT',
                'UF_INDEX',
                'UF_ENTRANCE',
                'UF_FLOOR',
                'UF_INTERPHONE',
                'UF_COMMENT'
            )
        ));

        if ($arUser = $rsUsers->Fetch())
        {
            $user->Update($arUser['ID'], Array(
                'ACTIVE' => 'Y',
                'UF_LAST_IP' => \Bitrix\Main\Service\GeoIp\Manager::getRealIp()
            ));
            return $this->successLogin($arUser);
        }
        else
        {
            $arResult = Array(
                'RESULT' => 'Error',
                'TEXT' => 'INVALID CODE'
            );
        }
        return $arResult;
    }

    //Инициализация класса
    public function initAuthorization()
    {
        if ($_POST['CHECK_SMS'] == '0') // если это не проверка кода СМС
            return $this->sendSMS();
        else
            //Если же это проверка кода
            return $this->checkSMS();
    }
}
//Класс обновления информации пользователя
class UpdateUser
{

    //Обновление основной информации о пользователе
    public function updateInfo()
    {
        if (!isset($_POST['email']) || !isset($_POST['name']) || !isset($_POST['surname'])) return;
        global $USER;
        $user = new CUser;
        $fields = array(
            'NAME' => $_POST['name'],
            'LAST_NAME' => $_POST['surname'],
            'EMAIL' => $_POST['email']
        );
        $user->Update($USER->GetID() , $fields);
        return array(
            'RESULT' => 'Okay',
            'TEXT' => 'Info successfully changed'
        );;
    }

    //Обновление адреса пользователя
    public function updateAddress()
    {
        global $USER;
        $user = new CUser;
        $user->Update($USER->GetID() , Array(
            'PERSONAL_STREET' => $_POST['street'],
            'UF_FLAT' => $_POST['flat'],
            'UF_INDEX' => $_POST['user_index'],
            'UF_ENTRANCE' => $_POST['entrance'],
            'UF_FLOOR' => $_POST['floor'],
            'UF_INTERPHONE' => $_POST['interphone'],
            'UF_COMMENT' => $_POST['user_comment'],
            'UF_COORDS' => $_POST['coords']
        ));
        return array(
            'RESULT' => 'Okay',
            'TEXT' => 'Address successfully changed'
        );;
    }
}
//Класс обновления скидок
class UpdateDiscount
{

    protected $order; // Текущий заказ
    protected $basket; // Текущая корзина
    protected $itemsDelivery;
    protected $shipmentCollection;
    protected $deliveryPrice;

    //Подготовка корзины
    public function prepareBasket()
    {
        $this->basket = \Bitrix\Sale\Basket::loadItemsForFUser(

            \Bitrix\Sale\Fuser::getId() ,

            \Bitrix\Main\Context::getCurrent()
                ->getSite()
        );
        $products_in_cart = CSaleBasket::GetList(array() , // сортировка
            array(
                'FUSER_ID' => CSaleBasket::GetBasketUserID() ,
                'LID' => SITE_ID,
                'ORDER_ID' => NULL
            ) , false, // группировать
            false, // постраничная навигация
            array(
                'ID',
                'CAN_BUY'
            ));
        foreach ($products_in_cart->arResult as $k => $product)
        {
            if ($product['CAN_BUY'] == 'N') $this
                ->basket
                ->getItemById($product['ID'])->delete();
        }
        $this->order = Bitrix\Sale\Order::create(SITE_ID, 1);
        Sale\DiscountCouponsManager::init(); //очищаем список купонов для данного хита
        Sale\DiscountCouponsManager::clear(true); //инициализируем корзину пользователя
        $this
            ->order
            ->setPersonTypeId(1);
    }

    //Проверка существования промокода
    public function checkPromoCode()
    {
        $check = DiscountCouponsManager::isExist($_POST['coupon']);
        if ($check["ID"] > 0) return 'SUCCESS';
        else return 'FAIL';
    }
    //Установка доставки
    public function setShipment()
    {
        $this->prepareShipmentItems();
        $this->shipmentCollection = $this
            ->order
            ->getShipmentCollection();
        foreach ($this->itemsDelivery as $id => $time)
        {
            $shipment = $this
                ->shipmentCollection
                ->createItem(Bitrix\Sale\Delivery\Services\Manager::getObjectById(2));
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach ($time as $item_id)
            {
                $item = $this
                    ->basket
                    ->getItemById($item_id);
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
            $deliveryRes = $this->checkDelivery($id, $shipmentItemCollection->getPrice());
            $shipment->setField('BASE_PRICE_DELIVERY', $deliveryRes);
        }
        $this->deliveryPrice = $this
            ->shipmentCollection
            ->getPriceDelivery();
    }

    //Группировка товаров по продавцу
    private function prepareShipmentItems()
    {
        foreach ($this->basket as $basketItem)
        {
            if (!$basketItem->canBuy()) continue;
            $created_by = $basketItem->getPropertyCollection()
                ->getPropertyValues() ['CREATED_BY']['VALUE'];
            $this->itemsDelivery[$created_by][] = $basketItem->getId();
        }
    }

    private function checkDelivery($seller_id, $price)
    {
        $arSelect = Array(
            "PROPERTY_ATT_delivery_price",
            "PROPERTY_ATT_delivery_price_free_from",
        );
        $arFilter = Array(
            "IBLOCK_ID" => IntVal('8') ,
            "ACTIVE" => "Y",
            "PROPERTY_ATT_seller_id" => $seller_id
        );
        $res = CIBlockElement::GetList(Array() , $arFilter, false, Array() , $arSelect);
        $arResult = $res->GetNext();
        if (!empty($arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) && $price >= $arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) $price = 0;
        else $price = $arResult['PROPERTY_ATT_DELIVERY_PRICE_VALUE'];
        return $price;
    }

    //Применение промокода
    public function applyDiscount()
    {
        $this
            ->order
            ->setBasket($this->basket);
        $result = Sale\DiscountCouponsManager::add($_POST['coupon']);
        $discounts = $this
            ->order
            ->getDiscount();
        $discounts->calculate();
        $discounts->getApplyResult();
        $coupons = $discounts->getApplyResult();
        $this->setShipment();
        foreach ($coupons['COUPON_LIST'] as $ar_r)
        {
            $coupon_name = $coupons['DISCOUNT_LIST'][$ar_r['ORDER_DISCOUNT_ID']]['NAME'];
        }
        $basketPrice = $this
            ->basket
            ->getPrice();
        $basketBasePrice = $this
            ->basket
            ->getBasePrice();

        return array(
            'PRICE' => CurrencyFormat($basketPrice + $this->deliveryPrice, $this
                ->order
                ->getCurrency()) ,
            'SUCCESS' => $result,
            'SALE_PRICE' => PriceMaths::roundPrecision($basketBasePrice - $basketPrice) ,
            'SALE' => CurrencyFormat(PriceMaths::roundPrecision($basketBasePrice - $basketPrice) , $this
                ->order
                ->getCurrency()) ,
            'NAME' => $coupon_name
        );
    }

    //Получение цены со скидкой
    public function getPriceWithDiscount()
    {
        $check = $this->checkPromoCode();
        if ($check == 'SUCCESS')
        {
            $this->prepareBasket();
            return $this->applyDiscount();
        }
        else return array(
            'SUCCESS' => false,
            'PRICE' => 0
        );
    }

    //Удаление скидки
    public function destroyDiscount()
    {
        $this->prepareBasket();
        $this
            ->order
            ->setBasket($this->basket);
        $this->setShipment();
        $basketPrice = $this
            ->basket
            ->getPrice();
        $basketBasePrice = $this
            ->basket
            ->getBasePrice();
        return array(
            'PRICE' => CurrencyFormat($basketPrice + $this->deliveryPrice, $this
                ->order
                ->getCurrency()) ,
            'SUCCESS' => true,
            'SALE' => PriceMaths::roundPrecision($basketBasePrice - $basketPrice)
        );
    }

    //Инициализация класса
    public function initUpdate()
    {
        if ($_POST['coupon'] == "destroy") return $this->destroyDiscount();
        else return $this->getPriceWithDiscount();
    }
}
//Класс создания заказа
class CreateOrderClass
{

    protected $order; // Текущий заказ
    protected $basket; // Текущая корзина
    protected $user; //Текущий пользователь
    protected $itemsDelivery = array();
    protected $shipmentCollection;
    protected $shipmentDatesJSON;
    protected $datesArray = array();

    //Подготовка корзины
    public function prepareBasket()
    {
        global $USER;
        $filter = Array(
            "ID" => $USER->GetID()
        );
        $rsUsers = CUser::GetList(($by = "NAME") , ($order = "desc") , $filter, array(
            'FIELDS' => array(
                'PERSONAL_PHONE'
            )
        ));
        $this->user = $rsUsers->GetNext();
        $this->basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId() , SITE_ID);
        $products_in_cart = CSaleBasket::GetList(array() , // сортировка
            array(
                'FUSER_ID' => CSaleBasket::GetBasketUserID() ,
                'LID' => SITE_ID,
                'ORDER_ID' => NULL
            ) , false, // группировать
            false, // постраничная навигация
            array(
                'ID',
                'CAN_BUY'
            ));
        foreach ($products_in_cart->arResult as $k => $product)
        {
            if ($product['CAN_BUY'] == 'N') $this
                ->basket
                ->getItemById($product['ID'])->delete();
        }
        $this->order = Bitrix\Sale\Order::create(SITE_ID, $USER->GetId());
        $this
            ->order
            ->setPersonTypeId(1);
        $this
            ->order
            ->setBasket($this->basket);
    }

    //Установка доставки
    public function setShipment()
    {
        $shipmentDates = array();
        $this->prepareShipmentItems();
        $this->shipmentCollection = $this
            ->order
            ->getShipmentCollection();
        foreach ($this->itemsDelivery as $id => $time)
        {
            $shipment = $this
                ->shipmentCollection
                ->createItem(Bitrix\Sale\Delivery\Services\Manager::getObjectById($_POST['delivery']));
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach ($time as $item_id)
            {
                $item = $this
                    ->basket
                    ->getItemById($item_id);
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
            $deliveryRes = $this->checkDelivery($id, $shipmentItemCollection->getPrice() , $this->datesArray[$id]);
            if (empty($deliveryRes['DATE'])) return array(
                'RESULT' => 'ERROR',
                'TEXT' => 'Ошибка установки даты доставки! Обновите страницу или обратитесь в техническую поддержку'
            );
            $shipment->setField('BASE_PRICE_DELIVERY', $deliveryRes['PRICE']);
            $shipment->setField('COMMENTS', $deliveryRes['DATE']);
            $shipmentDates[$id] = $deliveryRes['DATE'];
        }
        $this->shipmentDatesJSON = json_encode($shipmentDates, JSON_UNESCAPED_UNICODE);
    }

    //Группировка товаров по продавцу
    private function prepareShipmentItems()
    {
        $this->datesArray = json_decode($_POST['datesArray'], true);
        foreach ($this->basket as $basketItem)
        {
            if (!$basketItem->canBuy()) continue;
            $created_by = $basketItem->getPropertyCollection()
                ->getPropertyValues() ['CREATED_BY']['VALUE'];
            $this->itemsDelivery[$created_by][] = $basketItem->getId();
        }
    }

    private function checkDelivery($seller_id, $price, $selectedDate)
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
        $date = $this->checkDeliveryDate($arResult, $selectedDate);
        if (!empty($arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) && $price >= $arResult['PROPERTY_ATT_DELIVERY_PRICE_FREE_FROM_VALUE']) $price = 0;
        else $price = $arResult['PROPERTY_ATT_DELIVERY_PRICE_VALUE'];
        return array(
            'PRICE' => $price,
            'DATE' => $date,
        );
    }

    private function checkDeliveryDate($datesArray, $selectedDate)
    {
        $dateFounded = false;
        $startDate = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime());
        $processDays = false; //Просчитаны ли дни обработки заказа
        $counter = 0; //Количество проиндексированных дат
        $foundedDates = 0; //Количество найденных дат
        $shipmentsArray = array(); // Список всех дат
        while (!$dateFounded)
        {
            if ($counter > 60) return $shipmentsArray;
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
            if (FormatDate("j F", $startDate->getTimestamp()) . " с " . $datesArray['PROPERTY_ATT_DELIVERY_' . $weekDay . '_FROM_VALUE'] . " до " . $datesArray['PROPERTY_ATT_DELIVERY_' . $weekDay . '_TO_VALUE'] == $selectedDate)
            {
                return $selectedDate;
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

    //Установка цены
    public function setPrice()
    {
        $paymentCollection = $this
            ->order
            ->getPaymentCollection();
        $payment = $paymentCollection->createItem(Bitrix\Sale\PaySystem\Manager::getObjectById($_POST['paysystem']));
        $discounts = $this
            ->order
            ->getDiscount();
        $discounts->getApplyResult();
        $payment->setField("SUM", $this
            ->order
            ->getPrice());
        $payment->setField("CURRENCY", $this
            ->order
            ->getCurrency());
    }

    //Установка свойств
    public function setProperties()
    {
        global $USER;

        $propertyCollection = $this
            ->order
            ->getPropertyCollection();

        $propertyCodeToId = array();

        foreach ($propertyCollection as $propertyValue) $propertyCodeToId[$propertyValue->getField('CODE') ] = $propertyValue->getField('ORDER_PROPS_ID');

        $propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyCodeToId['FIO']);
        $propertyValue->setValue($USER->GetFullName());

        $propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyCodeToId['PHONE']);
        $propertyValue->setValue($this->user['PERSONAL_PHONE']);

        $propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyCodeToId['EMAIL']);
        $propertyValue->setValue($USER->GetEmail());

        $propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyCodeToId['DELIVERY_DATES_JSON']);
        $propertyValue->setValue($this->shipmentDatesJSON);

        $address = array(
            $_POST['address'],
            $_POST['flat'],
            $_POST['floor'],
            $_POST['interphone'],
            $_POST['entrance'],
            $_POST['user_index']
        );
        $comma_address = implode(", ", array_filter($address));
        $propertyValue = $propertyCollection->getItemByOrderPropertyId($propertyCodeToId['ADDRESS']);
        $propertyValue->setValue($comma_address);

        $this
            ->order
            ->setField('USER_DESCRIPTION', $_POST['user_comment']);
    }

    //Создание заказа
    public function createOrder()
    {

        $this->prepareBasket();

        $this->setShipment();

        $this->setPrice();

        $this->setProperties();

        $this
            ->order
            ->doFinalAction(true);
        $this
            ->order
            ->save();

        $paymentCollection = $this
            ->order
            ->getPaymentCollection();
        $onePayment = $paymentCollection[0];
        if ($onePayment->getField('IS_CASH') !== 'Y') $urlToPay = '/personal/orderPay/?orderNumber=' . $this
                ->order
                ->getId();

        return array(
            'ID' => $this
                ->order
                ->getId() ,
            'URL_TO_PAY' => $urlToPay
        );
    }
}

