<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $componentPath
 * @var array $arParams
 * @var array $arResult
 */
if($arResult['SEND_MAIL']['RESULT'] === 'ERROR')
{
    ?><div class="error_text"><?=$arResult['SEND_MAIL']['TEXT']?></div><?
}
if($arResult['SEND_MAIL']['RESULT'] === 'SUCCESS')
{
	?><div class="mf-ok-text"><?=$arResult['SEND_MAIL']['TEXT']?></div><?
}
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="change_profile_form">

    <span class="profile_head">Имя</span>
    <input class="login_input_first personal_input" type="text" name="user_name" maxlength="50" value="<?=$arResult['USER']["USER_NAME"]?>">

    <span class="profile_head">Почта</span>
    <input class="login_input_first personal_input" type="text" name="user_email" maxlength="50" value="<?=$arResult['USER']["USER_EMAIL"]?>">

    <span class="profile_head">Сообщение</span>
    <textarea name="MESSAGE" rows="5" cols="40" class="login_input_first personal_text"></textarea>

    <?if($arParams["USE_CAPTCHA"] == "Y"):?>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <div class="g-recaptcha" data-sitekey="<?=$arParams['ReCaptchaCode']?>" style="margin-top: 20px;"></div>
    <?endif;?>
    <input type="submit" name="submit" value="Отправить" class="button" style="margin-top: 20px;" id="send_button">
</form>
