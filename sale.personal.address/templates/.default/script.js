var componentPath, floor, interphone, flat, entrance, user_index, user_comment, address, boolError, coords;
window.VSChangeAddress = function (params){

    componentPath = params['componentPath'];

    if(params['addressLine'])
        address = params['addressLine'];

    coords = params['coords_user'];

    BX.ready(BX.delegate(this.init,this));
};
window.VSChangeAddress.prototype = {

    init : function (){
        this.mapActivated = false;

        this.addressLine = BX('adress_input_line_wrap');

        this.map = BX('ya_map');

        this.showOnTheMapButton = BX('suggest_map');

        this.confirmPointButton = BX('point_confirm_button');

        BX.bind(this.showOnTheMapButton, 'click', BX.proxy(this.showMapMobile, this));

       ymaps.ready(this.initmap);
    },

    initmap : function() {


        // Подключаем поисковые подсказки к полю ввода.
        var suggestView = new ymaps.SuggestView('suggest'),
            map,
            placemark;
        suggestView.events.add('select', function (event) {
            geocode();
        });


        var location = ymaps.geolocation;
        location.get({
            mapStateAutoApply: true
        }).then(function (result) {
            var coordinates = result.geoObjects.get(0).geometry.getCoordinates();
            map = new ymaps.Map("ya_map", {
                    center: coordinates,
                    zoom: 11,
                    controls: ['zoomControl']
                },
                {
                    suppressMapOpenBlock: true
                },
                {
                    preset: 'islands#redDotIconWithCaption'
                });


            // Добавление метки (Placemark) на карту
            placemark = new ymaps.Placemark(
                coordinates, { },
                {
                    draggable: true,
                    'preset': 'islands#redDotIcon',
                    iconLayout: 'default#image',
                    iconImageHref: componentPath + '/images/Group_12.svg',
                    iconImageSize: [76, 112],
                    iconImageOffset: [-38, -112]
                }
            );

            map.geoObjects.add(placemark);

            map.events.add('click', function (e) {
                var coords = e.get('coords');
                placemark.geometry.setCoordinates(coords);
                getAddress(coords);
            });

            placemark.events.add('dragend', function (e) {
                getAddress(placemark.geometry.getCoordinates());
            });

            // Ставим метку если адрес уже введён
            if($('#suggest').val() != '') {
                if (coords != '') {
                    coords = coords.split(', ');
                    coords[0] = parseFloat(coords[0]);
                    coords[1] = parseFloat(coords[1]);
                    placemark.geometry.setCoordinates(coords);
                    getAddress(coords);
                } else {
                    geocode();
                }
            }

            else
                getAddress(placemark.geometry.getCoordinates());


            var geolocationControl = new ymaps.control.GeolocationControl({
                options: {noPlacemark: true}
            });

            geolocationControl.events.add('locationchange', function (event) {
                var position = event.get('position');
                placemark.geometry.setCoordinates(position);
                // Установим новый центр карты в текущее местоположение пользователя.
                map.panTo(position);
                getAddress(position);
            });

            map.controls.add(geolocationControl);
        });
        location.get({
            provider: 'browser',
            mapStateAutoApply: true
        }).then(function (result) {
            if($('#suggest').val() == '') {
                placemark.geometry.setCoordinates(result['geoObjects']['position']);
                getAddress(placemark.geometry.getCoordinates());
                map.panTo(placemark.geometry.getCoordinates());
            }
        });
        // При клике по кнопке запускаем верификацию введёных данных.
        $('#address_button').bind('click', function (e) {
            goToCheckoutAddress();
        });
        $('#point_confirm_button').bind('click', function (e) {
            goToCheckoutAddress();
        });
        // Определяем адрес по координатам (обратное геокодирование).
        function getAddress(_coords) {
            ymaps.geocode(_coords).then(function (res) {
                var obj = res.geoObjects.get(0);
                $('#suggest').removeClass('input_error');
                $('#notice').css('display', 'none');
                $('#suggest').val(obj.getAddressLine());
                address = obj.getAddressLine();
                coords = _coords[0] + ", " + _coords[1];
                boolError = false;
            });
        }

        function geocode() {
            // Забираем запрос из поля ввода.
            var request = $('#suggest').val();
            // Геокодируем введённые данные.
            ymaps.geocode(request).then(function (res) {
                var obj = res.geoObjects.get(0),
                    error, hint;
                boolError = true;
                if (obj) {
                    // Об оценке точности ответа геокодера можно прочитать тут: https://tech.yandex.ru/maps/doc/geocoder/desc/reference/precision-docpage/
                    switch (obj.properties.get('metaDataProperty.GeocoderMetaData.precision')) {
                        case 'exact':
                            break;
                        case 'number':
                        case 'near':
                        case 'range':
                            error = 'Неточный адрес, требуется уточнение';
                            hint = 'Уточните номер дома';
                            break;
                        case 'street':
                            error = 'Неполный адрес, требуется уточнение';
                            hint = 'Уточните номер дома';
                            break;
                        case 'other':
                        default:
                            error = 'Неточный адрес, требуется уточнение';
                            hint = 'Уточните адрес';
                    }
                } else {
                    error = 'Адрес не найден';
                    hint = 'Уточните адрес';
                }

                // Если геокодер возвращает пустой массив или неточный результат, то показываем ошибку.
                if (error) {
                    showError(error);
                    showMessage(hint);
                } else {
                    showResult(obj);
                    boolError = false;
                }
            }, function (e) {
                console.log(e)
            })

        }

        function showResult(obj) {
            // Удаляем сообщение об ошибке, если найденный адрес совпадает с поисковым запросом.
            $('#suggest').removeClass('input_error');
            $('#notice').css('display', 'none');

            var mapContainer = $('#ya_map'),
                bounds = obj.properties.get('boundedBy');
            // Рассчитываем видимую область для текущего положения пользователя.
            mapState = ymaps.util.bounds.getCenterAndZoom(
                bounds,
                [mapContainer.width(), mapContainer.height()]
            ),
                // Сохраняем полный адрес для сообщения под картой.
                address = obj.getAddressLine();
            // Убираем контролы с карты.
            mapState.controls = [];
            // Создаём карту.
            createMap(mapState);
        }

        function showError(message) {
            $('#notice').text(message);
            $('#suggest').addClass('input_error');
            $('#notice').css('display', 'block');
        }

        function createMap(state) {
            //map.setCenter(state.center, state.zoom);
            map.panTo(state.center);
            placemark.geometry.setCoordinates(state.center);
            coords = '';
        }

        function goToCheckoutAddress() {
            $('#result_change').css('display', 'none');
            if (boolError)
                return;
            floor = $('#floor').val();
            user_index = $('#postcode').val();
            entrance = $('#entrance').val();
            flat = $('#apartament').val();
            interphone = $('#doorphone').val();
            user_comment = $('#delivery_com').val();
            //Запрос к аяксу
            BX.ajax({
                url: componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
                method: 'POST',
                dataType: 'json',
                timeout: 60,
                data: {
                    set_address: 1,
                    street: address,
                    floor: floor,
                    user_index: user_index,
                    entrance: entrance,
                    flat: flat,
                    interphone: interphone,
                    user_comment: user_comment,
                    coords: coords
                },
                onsuccess: BX.delegate(function(result){
                    if(result['RESULT'] == 'Okay') {
                        $('#result_change').css('display', '');
                    }
                        //Адресс успешно сохранен
                }, this)
            });
        }
    },
    /* Яндекс Карты конец */
    showMapMobile : function () {
        if(!this.mapActivated){
            this.mapActivated = true;
            BX.hide(this.addressLine);
            BX.show(this.confirmPointButton);
            BX.show(this.map);
            this.showOnTheMapButton.innerText = 'Закрыть карту';
        } else {
            this.mapActivated = false;
            BX.show(this.addressLine);
            BX.hide(this.confirmPointButton);
            BX.hide(this.map);
            this.showOnTheMapButton.innerText = 'Выбрать на карте';
        }
    }
};