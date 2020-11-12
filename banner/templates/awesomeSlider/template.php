<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>
    <? $frame = $this->createFrame()->begin(""); ?>

    <div class="swiper_main_Banner">
        <div id="swiperMainBanner" data-entity="items-row">
            <swiper ref="awesomeSwiperMainBanner" :options="swiperOptionBanner" @set-translate="onSetTranslate">
                <?foreach($arResult["BANNERS"] as $k => $banner):?>
                    <swiper-slide>
                        <?=$banner?>
                    </swiper-slide>
                <?endforeach;?>
                <div id="swiper-pagination-banner" class="swiper-pagination" slot="pagination"></div>
                <div id="swiper-button-prev-banner" class="swiper-button-prev" slot="button-prev"></div>
                <div id="swiper-button-next-banner" class="swiper-button-next" slot="button-next"></div>
            </swiper>
        </div>
    </div>

    <script>
        //Слайдер популярных
        new Vue({
            el: '#items_wrap'
        });

        //Слайдер начинается
        Vue.use(VueAwesomeSwiper)

        //Слайдер просмотренных
        new Vue({
            el: '#swiperMainBanner',
            components: {
                LocalSwiper: VueAwesomeSwiper.swiper,
                LocalSlide: VueAwesomeSwiper.swiperSlide,
            },
            data: {
                swiperOptionBanner: {
                    spaceBetween: 30,
                    slidesPerView: 'auto',
                    setWrapperSize: 1140,
                    roundLengths: true,
                    loop: true,
                    slidesPerGroup: 1,
                    pagination: {
                        el: '#swiper-pagination-banner',
                        clickable: true
                    },
                    navigation: {
                        nextEl: '#swiper-button-next-banner',
                        prevEl: '#swiper-button-prev-banner'
                    }
                }
            },
            computed: {
                awesomeSwiperRecentlyViewed() {
                    return this.$refs.awesomeSwiper.swiper
                }
            },
            methods: {
                onSetTranslate() {}
            }
        })
        //Слайдер заканчивается

        Vue.use(VueAwesomeSwiper)


    </script>
    <?$frame->end();?>
<?endif;?>