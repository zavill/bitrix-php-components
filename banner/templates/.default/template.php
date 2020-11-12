<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

    <? $frame = $this->createFrame()->begin(""); ?>
                <?foreach($arResult["BANNERS"] as $k => $banner):?>
                        <?=$banner?>
                <?endforeach;?>
    <?$frame->end();?>
<?endif;?>