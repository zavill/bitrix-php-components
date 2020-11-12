<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $componentPath
 * @var array $arParams
 * @var array $arResult
 */
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $USER;
?>
<script src="https://api-maps.yandex.ru/2.1/?apikey=e7223c9e-a470-4407-b900-2477af924436&lang=ru_RU"
        type="text/javascript"></script>
<div id="user_adress">
    <div id="header">
        <h1>Адрес доставки</h1>
        <span id="result_change" style="display: none">Адрес доставки успешно изменен</span>
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
            <button type="submit" id="address_button" class="form_login_button">Применить</button>
        </div>
        <p id="notice" style="display: none"></p>
    </div>
    <div id="ya_map" style="width:59%; height:500px"></div>
    <button id="point_confirm_button" class="form_login_button" style="display: none">Выбрать</button>
</div>
<script>
    new VSChangeAddress({
        componentPath: '<?=CUtil::JSEscape($component->getPath())?>',
        coords_user: '<?=$arResult['USER']['UF_COORDS']?>',
        addressLine: '<?=$arResult['USER']['PERSONAL_STREET']?>'
    });
</script>