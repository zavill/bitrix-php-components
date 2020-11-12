<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER;
// result
$this->arResult['USER']['FIRST_NAME'] = $USER->GetFirstName();
$this->arResult['USER']['LAST_NAME'] = $USER->GetLastName();
$this->arResult['USER']['FIRST_LETTER'] = mb_substr($this->arResult['USER']['FIRST_NAME'],0,1,'UTF-8');
$this->arResult['USER']['LAST_LETTER'] = mb_substr($this->arResult['USER']['LAST_NAME'],0,1,'UTF-8');

$this->IncludeComponentTemplate();
?>