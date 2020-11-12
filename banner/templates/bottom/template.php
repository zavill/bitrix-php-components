<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

    <? $frame = $this->createFrame()->begin(""); ?>

    <div class="banners_row">
        <?foreach($arResult["BANNERS"] as $k => $banner):?>
                <?=$banner?>
        <?endforeach;?>
    </div>

    <?$frame->end();?>

<?endif;?>