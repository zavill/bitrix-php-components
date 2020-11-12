<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
    "GROUPS" => array(
        'CAPTCHA' => array(
            'NAME' => 'Настройки капчи'
        )
    ),
    "PARAMETERS" => array(
        "EMAIL_TO" => Array(
            "NAME" => 'E-Mail на который будут приходить письма',
            "TYPE" => "STRING",
            "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
            "PARENT" => "BASE",
        ),
        "USE_CAPTCHA" => array(
            "PARENT" => "CAPTCHA",
            "NAME" => "Включить капчу",
            "TYPE" => "CHECKBOX",
            "MULTIPLE" => "N",
            "DEFAULT" => "N",
        ),
        "ReCaptchaCode" => array(
            "PARENT" => "CAPTCHA",
            "NAME" => "RECaptcha ключ",
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => "",
        ),
    ),
);
?>