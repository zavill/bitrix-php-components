<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use Bitrix\Sale;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $APPLICATION CMain
 * @var array $arParams
 * @var array $arResult
 */

class feedbackForm extends \CBitrixComponent
{

    //Получаем информацию о пользователе
    private function getUser()
    {
        global $USER;

        return array(
            'USER_NAME' => $USER->GetFullName() ,
            'USER_EMAIL' => $USER->GetEmail() ,
        );
    }

    //Отправляем E-Mail
    private function sendMail()
    {

        $captcha = $this->checkRecaptcha();

        if ($captcha['RESULT'] === 'ERROR') return $captcha;

        $checkUser = $this->checkUser();

        if ($checkUser['RESULT'] === 'ERROR') return $checkUser;

        \Bitrix\Main\Mail\Event::sendImmediate(array(

            "EVENT_NAME" => "FEEDBACK_FORM",

            'MESSAGE_ID' => 85,

            "LID" => "s1",

            "C_FIELDS" => array(

                "EMAIL_TO" => 'mail@vostroy.com',

                "DEFAULT_EMAIL_FROM" => 'mail@vostroy.com',

                "TEXT" => $_POST["MESSAGE"],

                "AUTHOR" => $_POST["user_name"],

                "AUTHOR_EMAIL" => $_POST["user_email"],

            ) ,
        ));

        return array(
            'RESULT' => 'SUCCESS',
            'TEXT' => 'Ваше сообщение успешно отправлено!'
        );

    }

    //Проверка рекапчи
    private function checkRecaptcha()
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret' => '6LdxoPYUAAAAALD2MEmrOBi06NawwV7JcoSlWTWd', 'response' => $_POST["g-recaptcha-response"]];
        $options = ['http' => ['method' => 'POST', 'content' => http_build_query($data) ]];
        $context = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captcha_success = json_decode($verify);
        if ($captcha_success->success === false) return array(
            'RESULT' => 'ERROR',
            'TEXT' => 'Решите капчу'
        );
    }

    //Проверка заполнения полей
    private function checkUser()
    {
        if (empty($_POST['user_email']) || empty($_POST['user_name']) || empty($_POST['MESSAGE'])) return array(
            'RESULT' => 'ERROR',
            'TEXT' => 'Заполните все поля формы обратной связи'
        );
    }

    public function executeComponent()
    {
        $this->setFrameMode(true);

        $user = $this->getUser();

        $this->arResult['USER'] = $user;

        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] <> '') $res = $this->sendMail();

        $this->arResult['SEND_MAIL'] = $res;

        $this->includeComponentTemplate();
    }
}

?>
