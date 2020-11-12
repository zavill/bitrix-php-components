<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Fuser,
    Bitrix\Sale\DiscountCouponsManager;
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */
?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<?
//?><!--<pre>--><?php
//print_r($arResult);
//?><!--</pre>--><?php
if ($arResult['BASKET']['ITEMS_COUNT'] <= 0)
{
    include(Main\Application::getDocumentRoot().$templateFolder.'/empty.php');
} else {
     if ($arParams['AJAX_REQUEST'] != 'Y'): ?>
        <a href="/personal/cart/" style="margin: 30px 0 10px; display: block;" id="back_to_cart">Вернуться в корзину</a>
         <a href="#" style="margin-bottom: 10px; display: none;" id="back_to_order">Вернуться к оформлению заказа</a>
    <? endif; ?>

    <div id="steps_wrap" style="<?=($arResult['DISPLAY']['CURRENT_STEP'] == '3' ? 'display: none;' : '')?>">
        <div id="step_authorize" class="step <?=($arResult['DISPLAY']['CURRENT_STEP'] == '1' ? 'actual' : 'completed')?>">
            <div class="step_number">1</div>
            <h3 class="step_text">Авторизация</h3>
        </div>
        <div id="step_address" class="step <?=($arResult['DISPLAY']['CURRENT_STEP'] == '2' ? 'actual' : '')?> <?=($arResult['DISPLAY']['STEP_ADDRESS_ERROR'] == 'Y' ? '' : 'completed')?>">
            <div class="step_number">2</div>
            <h3 class="step_text">Доставка</h3>
        </div>
        <div id="step_checkout" class="step <?=($arResult['DISPLAY']['CURRENT_STEP'] == '3' ? 'actual' : '')?>">
            <div class="step_number">3</div>
            <h3 class="step_text">Оформление заказа</h3>
        </div>
    </div>

    <div id="checkout_block"
         style="<?= ($arResult['DISPLAY']['DISPLAY_CHECKOUT_ERROR'] == 'Y' ? 'display: none;' : '') ?>">
        <h1>Оформление заказа</h1>
        <div id="checkout_block_wrap">
            <div class="checkout_wrap" id="checkout_wrap">

                <div class="checkout_info_step">

                    <div class="checkout_step_wrap">
                        <div class="delivery_wrap">
                            <div class="delivery_header">
                                <h3>Адрес доставки</h3>
                                <div id="delivery_change_button">Изменить</div>
                            </div>
                            <div class="delivery_adress">
                                <?= $arResult['USER']['PERSONAL_STREET'] ?>
                            </div>
                            <div class="delivery_user">
                                <?= $arResult['USER']['NAME'] . " " . $arResult['USER']['LAST_NAME'] ?>, <?= $arResult['USER']['PERSONAL_PHONE']?>
                            </div>
                            <p id="delivery_comment" class="delivery_comment" placeholder="Комментарий курьеру"
                                      rows="2"><?= $arResult['USER']['UF_COMMENT'] ?></p>
                        </div>
                    </div>

                    <div class="checkout_step_wrap">
                        <h3>Способ оплаты</h3>
                        <div class="paying_wrap">
                            <span class="paying_head">Оплата на сайте</span>
                            <span class="paying_desc">Оплата банковской картой онлайн</span>
                            <svg style="margin-top: 20px;" width="232" height="18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M226.07 10.93h1.73l.21-.02a.77.77 0 00.61-.77.8.8 0 00-.6-.78l-.22-.01h-1.73v1.58z" fill="url(#paint0_linear)"/><path d="M227.6 0a3 3 0 00-3 3v3.12h4.24l.3.01c.95.05 1.66.55 1.66 1.4 0 .68-.48 1.26-1.37 1.37v.03c.97.07 1.71.61 1.71 1.45 0 .91-.82 1.5-1.9 1.5h-4.66v6.1h4.4a3 3 0 003-3V0h-4.38z" fill="url(#paint1_linear)"/><path d="M228.4 7.73a.7.7 0 00-.6-.7l-.17-.02h-1.56v1.45h1.56c.05 0 .15 0 .17-.02a.7.7 0 00.6-.7z" fill="url(#paint2_linear)"/><path d="M211.02 0a3 3 0 00-3 3v7.4c.84.41 1.71.68 2.58.68 1.04 0 1.6-.63 1.6-1.49V6.1h2.58v3.49c0 1.35-.85 2.45-3.7 2.45-1.73 0-3.08-.38-3.08-.38v6.32h4.4a3 3 0 003-3V0h-4.38z" fill="url(#paint3_linear)"/><path d="M219.3 0a3 3 0 00-3 3v3.92c.77-.64 2.08-1.05 4.21-.95 1.14.05 2.36.36 2.36.36V7.6a5.7 5.7 0 00-2.28-.66C218.98 6.82 218 7.62 218 9c0 1.4.98 2.2 2.6 2.06a5.99 5.99 0 002.27-.66v1.27s-1.2.31-2.36.36c-2.13.1-3.44-.31-4.2-.95V18h4.4a3 3 0 003-3V0h-4.4z" fill="url(#paint4_linear)"/><path fill-rule="evenodd" clip-rule="evenodd" d="M104.85 14.58v-.35h-.1l-.1.24-.1-.24h-.1v.35h.07v-.27l.1.23h.07l.1-.23v.27h.06zm-.58 0v-.3h.12v-.05h-.3v.06h.12v.29h.06z" fill="#F79410"/><path fill-rule="evenodd" clip-rule="evenodd" d="M94.5 16.08h-7.87V1.92h7.88v14.16z" fill="#FF5F00"/><path fill-rule="evenodd" clip-rule="evenodd" d="M87.13 9a8.99 8.99 0 013.44-7.08 9 9 0 100 14.15A8.98 8.98 0 0187.13 9" fill="#EB001B"/><path fill-rule="evenodd" clip-rule="evenodd" d="M105.13 9a9 9 0 01-14.56 7.08 8.98 8.98 0 000-14.16A9 9 0 01105.13 9" fill="#F79410"/><path fill-rule="evenodd" clip-rule="evenodd" d="M28.83 5.75c-.03 2.51 2.23 3.91 3.94 4.74 1.75.86 2.34 1.4 2.34 2.17-.02 1.17-1.4 1.68-2.7 1.7-2.26.04-3.58-.61-4.63-1.1l-.81 3.82c1.05.48 3 .9 5 .92 4.74 0 7.83-2.34 7.85-5.96.02-4.59-6.36-4.84-6.31-6.9.01-.62.6-1.29 1.9-1.45a8.48 8.48 0 014.45.77l.8-3.69C39.55.37 38.16 0 36.43 0c-4.46 0-7.59 2.37-7.61 5.75zM48.26.32c-.86 0-1.6.5-1.92 1.28L39.6 17.73h4.72l.94-2.6h5.78l.54 2.6h4.17L52.1.32h-3.84zm.66 4.7l1.36 6.54h-3.73l2.37-6.54zM23.1.32l-3.73 17.4h4.5L27.6.33h-4.5zm-6.67 0l-4.69 11.85-1.9-10.08A2.1 2.1 0 007.78.32H.11L0 .82c1.57.34 3.36.9 4.44 1.48.67.36.86.68 1.07 1.53l3.6 13.9h4.76L21.17.32h-4.74z" fill="#1A1F71"/><path fill-rule="evenodd" clip-rule="evenodd" d="M162.22 0s-1.63.15-2.4 1.89l-3.94 8.8h-.47V0h-5.57v18h5.26s1.72-.16 2.48-1.89l3.87-8.8h.47V18h5.57V0h-5.27zM169.96 8.17V18h5.57v-5.74h6.04c2.63 0 4.86-1.7 5.69-4.09h-17.3zM141.87 0s-2.44 0-3.1 2.37l-2.32 8.32H136s-1.72-6.12-2.32-8.33c-.65-2.37-3.1-2.36-3.1-2.36H125v18h5.57V7.31h.47L134.29 18h3.87l3.25-10.68h.46V18h5.57V0h-5.57z" fill="#4DB45E"/><path fill-rule="evenodd" clip-rule="evenodd" d="M181.57 0h-12.39a8.99 8.99 0 008.75 7.39h9.55A6.08 6.08 0 00181.57 0z" fill="url(#paint5_linear)"/><defs><linearGradient id="paint0_linear" x1="224.6" y1="10.14" x2="232" y2="10.14" gradientUnits="userSpaceOnUse"><stop stop-color="#007940"/><stop offset=".23" stop-color="#00873F"/><stop offset=".74" stop-color="#40A737"/><stop offset="1" stop-color="#5CB531"/></linearGradient><linearGradient id="paint1_linear" x1="224.6" y1="8.98" x2="232" y2="8.98" gradientUnits="userSpaceOnUse"><stop stop-color="#007940"/><stop offset=".23" stop-color="#00873F"/><stop offset=".74" stop-color="#40A737"/><stop offset="1" stop-color="#5CB531"/></linearGradient><linearGradient id="paint2_linear" x1="224.6" y1="7.73" x2="232" y2="7.73" gradientUnits="userSpaceOnUse"><stop stop-color="#007940"/><stop offset=".23" stop-color="#00873F"/><stop offset=".74" stop-color="#40A737"/><stop offset="1" stop-color="#5CB531"/></linearGradient><linearGradient id="paint3_linear" x1="208.01" y1="8.98" x2="215.53" y2="8.98" gradientUnits="userSpaceOnUse"><stop stop-color="#1F286F"/><stop offset=".48" stop-color="#004E94"/><stop offset=".83" stop-color="#0066B1"/><stop offset="1" stop-color="#006FBC"/></linearGradient><linearGradient id="paint4_linear" x1="216.26" y1="8.98" x2="223.57" y2="8.98" gradientUnits="userSpaceOnUse"><stop stop-color="#6C2C2F"/><stop offset=".17" stop-color="#882730"/><stop offset=".57" stop-color="#BE1833"/><stop offset=".86" stop-color="#DC0436"/><stop offset="1" stop-color="#E60039"/></linearGradient><linearGradient id="paint5_linear" x1="169.18" y1="3.69" x2="187.61" y2="3.69" gradientUnits="userSpaceOnUse"><stop offset=".3" stop-color="#00B4E6"/><stop offset="1" stop-color="#088CCB"/></linearGradient></defs></svg>
                        </div>
                    </div>

                </div>
                <? foreach ($arResult['SHIPMENTS']['DELIVERIES'] as $id=>$shipment): ?>
                <div class="checkout_step">
                    <div class="head_wrap">
                        <div class="order_info_wrap">
                            <h2>Состав отправления <?if ($id != 0) :?><?=$id+1?> <?endif;?></h2>
                            <div class="order_info">
                                Отправление со склада <?=$shipment['NAME']?>, <?=$shipment['ITEMS_COUNT']?> <?=$shipment['COUNT_NAME']?>
                            </div>
                        </div>
                        <div id="sortSelect">
                           <select class="delivery_dates_select" data-id="<?=$shipment['ID']?>">
                                <? foreach ($shipment['DATE'] as $date): ?>
                                    <option value="<?=$date?>"><?=$date?></option>
                               <?endforeach;?>
                           </select>
                        </div>
                    </div>
                    <div class="checkout_items_wrap">
                        <?foreach ($shipment['ITEMS'] as $item):
                        $product = $arResult['ITEMS'][$item['PRODUCT_ID']];
                        ?>
                            <div class="checkout_item">
                                <div class="item_image_wrap">
                                    <img src="<?= $product['PATH'] ?>" alt="" class="item_image">
                                </div>
                                <div class="item_name_wrap">
                                    <span class="item_name"><?= $product['NAME'] ?></span>
                                </div>
                                <span
                                        class="item_quantity"><?= Bitrix\Sale\BasketItem::formatQuantity($product['QUANTITY']) ?> <?=$product['MEASURE']?>.</span>
                                <span
                                        class="item_price"><?= CurrencyFormat($product['PRICE'], $product['CURRENCY']) ?></span>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
                <? endforeach; ?>
            </div>
            <div id="checkout_total">
                <div class="checkout_total_main">
                    <button class="button_checkout" id="small_card_button_checkout" disabled="disabled">
                        Оплатить заказ
                    </button>
                    <p class="checkout_info">Нажимая на кнопку, вы соглашаетесь с <a href="/pravovaya-informacia/" style="font-size: 14px; color: #b7b7b7;">Политикой конфиденциальности</a></p>

                    <div class="items_info">
                        <p id="items_products_count">Товары (<?= $arResult['BASKET']['ITEMS_COUNT'] ?>)</p>
                        <p class="info_value"><span id="price_value" class="info_value"><?= $arResult['BASKET']['PRICE'] ?></span></p>
                    </div>
                    <div id="discount_block" class="items_info" style="<?=($arResult['BASKET']['DISCOUNT']['PRICE'] > 0 ? '' : 'display: none;')?>">
                        <p>Скидка</p>
                        <p class="sale_value"><span id="sale_value" class="sale_value"><?= $arResult['BASKET']['DISCOUNT']['PRICE'] ?></span></p>
                    </div>
                    <div class="items_info">
                        <p>Вес</p>
                        <p class="info_value"><?= $arResult['BASKET']['WEIGHT'] ?> кг</p>
                    </div>
                    <div class="items_info">
                        <p>Доставка</p>
                        <p class="info_value <?=($arResult['SHIPMENTS']['FINAL_PRICE'] === 'Бесплатно' ? 'sales_free' : '')?>"><?=$arResult['SHIPMENTS']['FINAL_PRICE']?></p>
                    </div>
                    <div class="final_items_info">
                        <p>Итого</p>
                        <p class="final_value"><span id="final_price" class="final_value"><?= $arResult['BASKET']['PRICE_DISCOUNT'] ?></span></p>
                    </div>
                </div>
                <div class="coupon_wrap">
                    <input type="text" class="coupon_input" id="coupon_input" placeholder="Промокод" value="<?=$arResult['BASKET']['DISCOUNT']['COUPON']?>">
                    <span id="coupon_button" class="coupon_button">Применить</span>
                </div>
                <p class="coupon_info" id="coupon_info" style="<?=($arResult['BASKET']['DISCOUNT']['PRICE'] > 0 && $arResult['BASKET']['DISCOUNT']['NAME'] != '' ? '' : 'display: none;')?>">
                    <strong id="coupon_name"><?=$arResult['BASKET']['DISCOUNT']['NAME']?></strong>
                    <span id="delete_coupon_button" class="close-link">Удалить</span>
                </p>
            </div>
        </div>
    </div>
    <?php if ($arParams['AJAX_REQUEST'] != 'Y'): ?>


    <script src="https://api-maps.yandex.ru/2.1/?apikey=e7223c9e-a470-4407-b900-2477af924436&lang=ru_RU"
            type="text/javascript"></script>
    <?php endif;?>
    <div id="auth_registration_ordermake">
        <div id="phone_auth" style="<?= (!$USER->IsAuthorized() ? '' : 'display: none;') ?>">
            <div id="auth_block_checkout">
                <h2>Вход или регистрация</h2>
                <div class="form_wrap">
                    <input type="text" placeholder="Телефон" value="+7" id="phone_input_checkout"
                           class="login_input_first">
                    <button id="get_code_button_checkout" class="form_login_button">Получить код</button>
                    <div id="captcha_error" class="form_auth_error" style="display: none">Решите капчу</div>
                    <!--        Сюда капчу      -->
                    <? if ($arParams['ReCaptcha'] == 'Y'): $_SESSION['ReCaptchaAttempts'] = $arParams['ReCaptchaAttempts']; ?>
                        <input type="hidden" name="action" value="sendSMS">
                        <script src='https://www.google.com/recaptcha/api.js'></script>
                        <div data-sitekey="6LdxoPYUAAAAAIVEVdnzt-OQykRCjvfkUep1MdbY" id="g-recaptcha_checkout" class="g-recaptcha" <?=($_SESSION['SMS_COUNT_REQUEST'] >= $arParams['ReCaptchaAttempts'] ? '' : 'style="display: none;"')?>></div>
                    <? endif; ?>
                </div>
            </div>
            <div id="sms_code_block_checkout" style="display: none;">
                <h2>Введите код из СМС</h2>
                <p class="sms_info">Код отправлен на номер <span class="sms_info" id="number_checkout"></span></p>
                <div class="form_wrap">
                    <input type="text" maxlength="4" id="code_sms_checkout" class="login_input_first" autofocus>
                    <div id="152fz_checkout" style="">
                        <input type="checkbox" checked> Нажимая кнопку "Отправить" Вы даёте свое согласие на <a href="/informirovannoe-soglasie/">обработку введенной
                            персональной информации</a> в соответствии с Федеральным Законом №152-ФЗ от 27.07.2006 "О
                        персональных данных"
                    </div>
                    <p id="timer_again_checkout">Новый код можно получить через <span
                            id="seconds_timer_checkout"><?=$arParams['RELOAD_TIME']?></span> секунд</p>
                    <a id="try_again_checkout" href="#" style="display: none">Попробовать ещё раз</a>
                </div>

            </div>
        </div>
        <div id="main_data_user"
             style="<?= ($arResult['DISPLAY']['DISPLAY_MAIN_INFO_ERROR'] == 'Y' ? '' : 'display: none;') ?>">
            <h1>Ваши данные</h1>
            <p class="desc_info">Данные для оформления заказа</p>
            <div class="form_wrap">
                <input type="text" id="user_email" class="login_input_first" value="<?= $USER->GetEmail() ?>"
                       placeholder="E-mail" autofocus>
                <input type="text" id="user_name" class="login_input_first not_first" value="<?= $USER->GetFirstName() ?>"
                       placeholder="Имя">
                <input type="text" id="user_surname" class="login_input_first not_first" value="<?= $USER->GetLastName() ?>"
                       placeholder="Фамилия">
                <button id="send_user_info" class="form_login_button">Продолжить</button>
            </div>
        </div>
        <div id="user_adress"
             style="<?= ($arResult['DISPLAY']['DISPLAY_ADDRESS_ERROR'] == 'Y' ? '' : 'display: none;') ?>">
            <div id="header">
                <h1>Доставка</h1>
                <input type="text" id="suggest" class="login_input_first" value="<?=$arResult['USER']['PERSONAL_STREET'] ?>"
                       placeholder="Введите адрес" autofocus>
                <a href="#" id="suggest_map">Выбрать на карте</a>
                <div id="adress_input_line_wrap">
                    <div class="adress_input_line">
                        <input type="text" id="apartament" class="login_input_first" value="<?= $arResult['USER']['UF_FLAT'] ?>"
                               placeholder="Квартира/офис">
                        <input type="text" id="postcode" class="login_input_first" value="<?= $arResult['USER']['UF_INDEX'] ?>"
                               placeholder="Индекс">
                    </div>
                    <div class="adress_input_line">
                        <input type="text" id="entrance" class="login_input_first" value="<?= $arResult['USER']['UF_ENTRANCE'] ?>"
                               placeholder="Подъезд">
                        <input type="text" id="floor" class="login_input_first" value="<?= $arResult['USER']['UF_FLOOR'] ?>"
                               placeholder="Этаж">
                        <input type="text" id="doorphone" class="login_input_first" value="<?= $arResult['USER']['UF_INTERPHONE'] ?>"
                               placeholder="Домофон">
                    </div>
                    <input type="text" id="delivery_com" class="login_input_first" value="<?= $arResult['USER']['UF_COMMENT'] ?>"
                           placeholder="Комментарий для курьера">
                    <button type="submit" id="address_button" class="form_login_button">Продолжить</button>
                </div>
                <p id="notice"></p>
            </div>
            <div id="ya_map" style="width:100%; height:500px"></div>
            <button id="point_confirm_button" class="form_login_button" style="display: none">Выбрать</button>
        </div>
    </div>

    <script>
        new VSCheckOutComponent({
            componentPath: '<?=CUtil::JSEscape($component->getPath())?>',
            captcha: '<?=$arParams['ReCaptcha']?>',
            tryMax: '<?=$arParams['ReCaptchaAttempts']?>',
            tryCount: '<?=$_SESSION['SMS_COUNT_REQUEST']?>',
            backurl: '<?=$arParams['backurl']?>',
            Menu: '<?=$arParams['Menu']?>',
            DiscountsCount: '<?=$arResult['BASKET']['DISCOUNTS_COUNT']?>',
            AJAX: '<?=$arParams['AJAX_REQUEST']?>',
            reloadTime: '<?=$arParams['RELOAD_TIME']?>',
            coords_user: '<?=$arResult['USER']['UF_COORDS']?>',
            addressLine: '<?=$arResult['USER']['PERSONAL_STREET']?>'
        });
    </script>
    <?php
}
?>