<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $componentPath
 * @var array $arParams
 * @var array $arResult
 */
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\PaySystem;

if(!$arResult['FAKE_ORDER']):
?>
<div class="order_wrap">
    <h1>Ваш заказ оформлен и оплачен</h1>
    <div id="order_info">

        <div class="order_info_line">
            <p class="order_info_header">
                Номер заказа
            </p>
            <a class="order_info_item" href="/personal/orderdetails/?order=<?=$_GET['number']?>/" style="color: #ff9600;">
                <?=$_GET['number']?>
            </a>
        </div>

        <div class="order_info_line">
            <p class="order_info_header">
                Адрес доставки
            </p>
            <p class="order_info_item">
                <?=$arResult['ORDER']['ADDRESS']?>
            </p>
        </div>

        <div class="order_info_line">
            <p class="order_info_header">
                Время доставки
            </p>
            <p class="order_info_item">
                <?foreach ($arResult['ORDER']['SHIPMENT_DATES'] as $time):?>
                    <?=($c > 0 ? '<br>'  : '')?><?=$time?>
                    <? $c++; endforeach; ?>
            </p>
        </div>

        <div class="order_info_line">
            <p class="order_info_header">
                Получатель
            </p>
            <p class="order_info_item">
                <?=$arResult['ORDER']['NAME']?>,
                <?=$arResult['ORDER']['PHONE']?>,
                <?=$arResult['ORDER']['EMAIL']?>
            </p>
        </div>

        <div class="order_info_line">
            <p class="order_info_header">
                Вес заказа
            </p>
            <p class="order_info_item">
                <?=$arResult['ORDER']['WEIGHT']?> кг
            </p>
        </div>

        <div class="order_final_line">
            <p class="order_final_header">
                Заказ оплачен
            </p>
            <p class="order_final_item">
                <?=$arResult['ORDER']['PRICE']?>
            </p>
        </div>

    </div>
</div>
<?endif;?>
