<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 * @var array $arResult
 */
class changeAddress extends \CBitrixComponent
{
    public function getUser()
    {
        global $USER;
        
        if ($USER->IsAuthorized())
        {
            $filter = array(
                "ID" => $USER->GetID()
            );
            $rsUsers = CUser::GetList(($by = "NAME") , ($order = "desc") , $filter, array(
                'FIELDS' => array(
                    'PERSONAL_STREET',
                    'PERSONAL_PHONE',
                    'NAME',
                    'LAST_NAME',
                    'EMAIL'
                ) ,
                'SELECT' => array(
                    'UF_FLAT',
                    'UF_INDEX',
                    'UF_ENTRANCE',
                    'UF_FLOOR',
                    'UF_INTERPHONE',
                    'UF_COMMENT',
                    'UF_COORDS'
                )
            ));
            $arUser = $rsUsers->GetNext();
            foreach ($arUser as $key => $item)
            {
                $arResult[$key] = $item;
            }
            if ($arResult['NAME'] == '' || $arResult['LAST_NAME'] == '' || $arResult['EMAIL'] == '') $arResult['MAIN_INFO_CHECK_ERROR'] = 'Y';
            if ($arResult['PERSONAL_STREET'] == '') $arResult['ADDRESS_CHECK_ERROR'] = 'Y';
        }

        return $arResult;
    }
    public function executeComponent()
    {
        $this->setFrameMode(false);

        //USER info
        $arUser = $this->getUser();
        $arResult['USER'] = $arUser;

        $this->arResult = $arResult;
        //is included in all cases for old template
        $this->IncludeComponentTemplate();

    }
}
?>
