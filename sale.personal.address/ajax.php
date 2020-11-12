<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if($_POST['set_address']) { // Обновление адреса
    $user = new UpdateUser;
    echo json_encode($user->updateAddress());
    return;
}
//Класс обновления информации пользователя
class UpdateUser {
    //Обновление адреса пользователя
    public function updateAddress(){
        global $USER;
        $user = new CUser;
        $user->Update($USER->GetID(), Array(
            'PERSONAL_STREET' => $_POST['street'],
            'UF_FLAT' => $_POST['flat'],
            'UF_INDEX' => $_POST['user_index'],
            'UF_ENTRANCE' => $_POST['entrance'],
            'UF_FLOOR' => $_POST['floor'],
            'UF_INTERPHONE' => $_POST['interphone'],
            'UF_COMMENT' => $_POST['user_comment'],
            'UF_COORDS' => $_POST['coords']));
        return array(
            'RESULT' => 'Okay',
            'TEXT' => 'Address successfully changed'
        );;
    }
}
?>