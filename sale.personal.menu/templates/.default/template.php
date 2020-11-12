<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $componentPath
 * @var array $arParams
 * @var array $arResult
 */
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $USER;
?>
<div class="menu_container">
    <div class="head_avatar">
        <?=$arResult['USER']['FIRST_LETTER']?><?=$arResult['USER']['LAST_LETTER']?>
    </div>
    <a href="/personal/" class="head_wrap">
        <h3 class="head"><?=$USER->GetFirstName()?></h3>
        <h3 class="head"><?=$USER->GetLastName()?></h3>
    </a>
    <ul class="menu_list">
        <li><a href="/personal/private/">Личные данные</a></li>
        <li><a href="/personal/address/">Адрес доставки</a></li>
        <li><a href="/personal/orders/?filter_history=Y">История заказов</a></li>
    </ul>
</div>
