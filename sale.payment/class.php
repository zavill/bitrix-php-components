<?

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var array $arResult
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Sale;

class initPayment extends \CBitrixComponent
{

    private function preparePayment()
    {
        global $USER;

        if (empty(intval($_GET['orderNumber'])))
        {
            return 'Произошла непредвиденная ошибка! Не указан номер заказа!';
        }
        else
        {
            $order = Sale\Order::load($_GET['orderNumber']);
            $orderUser = $order->getUserId();
            if ($USER->GetId() !== $orderUser) return 'Произошла непредвиденная ошибка!';
            $order = Sale\Order::loadByAccountNumber($_GET['orderNumber']); // id заказа
            if ($order->isAllowPay()) //Если разрешена оплата
            {
                //Получаем объект платежной системы
                $paymentCollection = $order->getPaymentCollection();
                $payment = $paymentCollection[0];
                $service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                $context = \Bitrix\Main\Application::getInstance()->getContext();
                $service->initiatePay($payment, $context->getRequest());//Создаем форму оплаты
            }
            else
            {
                return 'Произошла непредвиденная ошибка! Платеж не разрешен!';
            }
        }
    }

    public function executeComponent()
    {
        $this->setFrameMode(false);

        $display = $this->preparePayment();

        $this->arResult['DISPLAY'] = $display;

        $this->includeComponentTemplate();
    }
}

