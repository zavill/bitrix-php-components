var componentPath, floor, interphone, flat, entrance, user_index, user_comment, address, boolError, coords;
window.VSCheckOutComponent = function (params) {

	componentPath = params['componentPath'];

	this.componentPath = params['componentPath'];

	this.captchaActive = params['captcha'];

	this.menu = params['Menu'];

	this.backURL = params['backurl'];

	if(this.captchaActive == 'Y') {
		this.tryAttemptsMax = params['tryMax'];

		this.tryAttempsCount = params['tryCount'];
	}

	this.authBlock = BX('auth_block_checkout');

	this.captchaError = BX('captcha_error');

	this.smsCodeBlock = BX('sms_code_block_checkout');

	this.sendSmsButton = BX('get_code_button_checkout');

	this.sendSMSInput = BX('phone_input_checkout');

	this.codeInput = BX('code_sms_checkout');

	this.tryAgainButton = BX('try_again_checkout');

	this.captchaBlock = BX('g-recaptcha_checkout');

	this.phoneText = BX('number_checkout');

	this.fz152 = BX('152fz_checkout');

	this.timerTime = null;

	this.timeInterval = null;

	this.remainingTimeDOM = BX('timer_again_checkout');

	this.secondsTimer = BX('seconds_timer_checkout');

	this.timerOut = false;

	this.mainInfoButton = BX('send_user_info');

	this.mainInfoEMail = BX('user_email');

	this.mainInfoName = BX('user_name');

	this.mainInfoSurname = BX('user_surname');

	this.checkoutSmallCard = BX('small_card_button_checkout');

	this.changeAddressButton = BX('delivery_change_button');

	this.submitCoupon = BX('coupon_button');

	this.inputCoupon = BX('coupon_input');

	this.discountBlock = BX('discount_block');

	this.discountPrice = BX('sale_value');

	this.totalPrice = BX('final_price');

	this.couponBlock = BX('coupon_info');

	this.couponName = BX('coupon_name');

	this.couponDeleteButton = BX('delete_coupon_button');

	this.priceBeforeSale = BX('price_value');

	this.deliveryCommentInput = BX('delivery_comment');

	this.discountsCount = params['DiscountsCount'];

	this.AJAXrequest = params['AJAX'];

	this.reloadTime = params['reloadTime'] * 1000;

	this.edit_address = false;

	this.back_to_cart = BX('back_to_cart');

	this.back_to_order = BX('back_to_order');

	this.confirm_address_button = BX('address_button');

	if(params['addressLine'])
		address = params['addressLine'];

	coords = params['coords_user'];

	BX.ready(BX.delegate(this.init,this));

};

window.VSCheckOutComponent.prototype = {
	init: function() {

		BX.showWait();

		BX.bind(this.sendSmsButton, 'click', BX.proxy(this.sendSmsCode, this));// Бинд функции отправки СМС на кнопку

		BX.bind(this.codeInput, 'input', BX.proxy(this.checkSMSCode, this));// Бинд функции проверки кода

		BX.bind(this.tryAgainButton, 'click', BX.proxy(this.showAuth, this));// Бинд функции попробовать ещё раз на кнопку

		BX.bind(this.mainInfoButton, 'click', BX.proxy(this.sendMainInfo, this));// Бинд функции сохранить основные данные пользователя

		BX.bind(this.mainInfoEMail, 'input', BX.proxy(this.checkEmail, this));

		BX.bind(this.submitCoupon, 'click', BX.proxy(this.CouponSet, this));// Бинд функции применения промокода

		BX.bind(this.couponDeleteButton, 'click', BX.proxy(this.CouponDelete, this));// Бинд функции удаления промокода

		BX.bind(this.changeAddressButton, 'click', BX.proxy(this.editAddress, this)); // Бинд функции изменении адреса

		BX.bind(this.confirm_address_button, 'click', BX.proxy(function(){this.changeBackButtons(false)}, this)); // Бинд функции применении адреса

		BX.bind(this.confirmPointButton, 'click', BX.proxy(function(){this.changeBackButtons(false)}, this)); // Бинд функции применении адреса

		BX.bind(this.back_to_order, 'click', BX.proxy(function(){this.nextStep('checkout_block', 'user_adress'); this.changeBackButtons(false);}, this)); // Бинд функции применении адреса

		$('#phone_input_checkout').mask("+7 (999) 999 99 99", {autoclear: false});// Маска телефона

		$('#phone_input_checkout').focus();

		/* Начало Яндекс Карты */
		if(this.AJAXrequest != 'Y')
			ymaps.ready(this.initmap);

		this.mapActivated = false;

		this.addressLine = BX('adress_input_line_wrap');

		this.map = BX('ya_map');

		this.showOnTheMapButton = BX('suggest_map');

		this.confirmPointButton = BX('point_confirm_button');

		this.datesArray = {};

		BX.bind(this.showOnTheMapButton, 'click', BX.proxy(this.showMapMobile, this));

		$('.delivery_dates_select').select2({
			minimumResultsForSearch: -1
		});

		BX.bind(this.checkoutSmallCard, 'click', BX.proxy(this.checkOut, this));

		$('#small_card_button_checkout').prop('disabled', false);

		BX.closeWait();

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
				}
				geocode();
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
			console.log(address);
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
					if(result['RESULT'] == 'Okay')
						window.VSCheckOutComponent.prototype.goToCheckout();
				}, this)
			});
		}
	},
	/* Яндекс Карты конец */

	goToCheckout : function () {
		if(this.edit_address)
			this.edit_address = false;
		this.updateCheckout();
	},

	CouponSet : function () {
		if(this.inputCoupon.value == '')
		{
			BX.addClass(this.inputCoupon, 'error_input');
			return 1;
		} else {
			BX.removeClass(this.inputCoupon, 'error_input');
		}
		BX.ajax({
			url: componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				discount_update: 1,
				coupon: this.inputCoupon.value
			},
			onsuccess: BX.delegate(function(result){
				console.log(result);
				if(result['SUCCESS'] == true && result['SALE_PRICE'] > 0) {
					this.recalculateTotal(result);
					this.discountsCount++;
				} else
					BX.addClass(this.inputCoupon, 'error_input');
			}, this)
		});
	},

	CouponDelete : function () {
		if(this.discountsCount <= 1)
			this.hideCoupons();
		else
			BX.showWait();
		BX.ajax({
			url: componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				discount_update: 1,
				coupon: "destroy"
			},
			onsuccess: BX.delegate(function(result){
				console.log(result);
				if(result['SUCCESS'] == true) {
					this.totalPrice.innerHTML = result['PRICE'];
					this.discountsCount--;
					BX.closeWait();
				}
			}, this)
		});
	},

	recalculateTotal : function(coupon) {
		this.discountPrice.innerHTML = coupon['SALE'];
		this.totalPrice.innerHTML = coupon['PRICE'];
		BX.style(this.discountBlock, 'display', '');//Активация блока со скидками
		if(coupon['NAME']) {
			this.couponName.innerText = coupon['NAME'];
			BX.style(this.couponBlock, 'display', '');//Активация блока с купоном
		} else
			BX.style(this.couponBlock, 'display', 'none');//Деактивация блока с купоном
		BX.closeWait();
	},

	hideCoupons : function(){
		this.inputCoupon.value = "";
		BX.style(this.discountBlock, 'display', 'none');//Деактивация блока со скидками
		BX.style(this.couponBlock, 'display', 'none');//Деактивация блока с купоном
	},

	updateCheckout : function () {
		BX.showWait();
		//Запрос к аяксу для обновления страницы
		BX.ajax.post(
			componentPath + '/ajax.php',
			{
				updateCheckout: 1
			},
			function (data) {
				$('#checkout_block').html($(data).find('#checkout_block').html());
				$(document).unbind('click.fb-start');
				$('#login-link').html($(data).find('#login-link').html());
				window.VSCheckOutComponent.prototype.nextStep('checkout_block', 'phone_auth');
				window.VSCheckOutComponent.prototype.nextStep('checkout_block', 'user_adress');
				BX.closeWait();
			}
		);
	},

	editAddress : function(){
		this.edit_address = true;
		this.nextStep('user_adress', 'checkout_block');
		this.changeBackButtons(true);
	},

	changeBackButtons : function (back_to_order){
		if(back_to_order) {
			BX.hide(this.back_to_cart);
			BX.show(this.back_to_order);
		} else {
			BX.show(this.back_to_cart);
			BX.hide(this.back_to_order);
		}
	},



	sendSmsCode: function () {
		//Проверка на введённый номер
		if ( (this.sendSMSInput.value.indexOf("_") != -1) || this.sendSMSInput.value == '' ) {
			BX.addClass(this.sendSMSInput, 'error_input');
			return 1;
		} else {
			BX.removeClass(this.sendSMSInput, 'error_input');
		}
		//Проверка на капчу
		this.captcha = grecaptcha.getResponse();
		if(this.tryAttempsCount >= this.tryAttemptsMax && this.captchaActive == 'Y')
		{
			if (!this.captcha.length) {
				// Выводим сообщение об ошибке
				BX.style(this.captchaError, 'display', '');
				return;
			}
			BX.style(this.captchaError, 'display', 'none;');
		}


		//Запрос к аяксу
		BX.ajax({
			url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				phone: this.sendSMSInput.value,
				CHECK_SMS: 0,
				recaptcha: this.captcha
			},
			onsuccess: BX.delegate(function(result){
				console.log(result);
				BX.style(this.fz152, 'display', '');
				if(result && result['TEXT'] == 'Authorized'){
					this.authorizeCheck(result);
					return;
				} else if (result && result['TEXT'] == 'Authorization'){
					BX.style(this.fz152, 'display', 'none');
				}
				// активация второго блока с СМС кодом
				this.showSmsCode();
			}, this)
		});
		this.startTimer();
		this.tryAttempsCount++;
		grecaptcha.reset();
	},

	getDeliveryDates : function () {
		this.datesArray = {};
		let $t = this;
		$('.delivery_dates_select').each(function () {
			console.log($t.datesArray);
			$t.datesArray[$(this).data('id')] = $(this).val();
		});
	},

	checkOut : function() {
		if(address == "")
			return;
		floor = $('#floor').val();
		user_index = $('#postcode').val();
		entrance = $('#entrance').val();
		flat = $('#apartament').val();
		interphone = $('#doorphone').val();
		user_comment = $('#delivery_comment').val();
		if(floor != '')
			floor = 'этаж ' + floor;
		if(entrance != '')
			entrance = "подъезд " + entrance;
		if(flat != '')
			flat = 'кв./оф. ' + flat;
		if(interphone != '')
			interphone = 'домофон ' + interphone;
		this.getDeliveryDates();
		BX.showWait();
		BX.ajax({
			url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				create_order: 1,
				address: address,
				floor: floor,
				user_index: user_index,
				entrance: entrance,
				flat: flat,
				interphone: interphone,
				user_comment: user_comment,
				coords: coords,
				delivery: 2,
				paysystem: 4,
				datesArray: JSON.stringify(this.datesArray)
			},
			onsuccess: BX.delegate(function(result){
				if(result['ID'] > 0) {
					console.log(result);
					if(result['URL_TO_PAY'] !== '') {
						location.href = result['URL_TO_PAY'];
					} else {
						location.href = "/order_done/?number=" + orderId;
					}
					ym(49157746,'reachGoal','ORDER');
					BX.closeWait();
				} else {
					console.log(result);
				}
			}, this)
		});
	},

	startTimer : function(){
		this.secondsTimer.innerHTML = this.reloadTime / 1000; // пишем что осталось this.reloadTime секунд
		this.timerTime = new Date(Date.parse(new Date()) + this.reloadTime);//таймер на this.reloadTime секунд
		this.timerOut = true;// таймер запущен
		BX.style(this.remainingTimeDOM, 'display', '');//включаем таймер
		BX.style(this.tryAgainButton, 'display', 'none');//отключаем повтор отправки
		this.timeInterval = setInterval(this.timerSMS, 1000, this.secondsTimer, this.timerTime);
		setTimeout(() => {
			clearInterval(this.timeInterval);
			BX.style(this.remainingTimeDOM, 'display', 'none');//отключаем таймер
			BX.style(this.tryAgainButton, 'display', '');//включаем повтор отправки
			this.timerOut = false;
		}, this.reloadTime);

	},

	checkSMSCode: function () {
		//Проверка на введённый код
		if (this.codeInput.value.length != 4) {
			return 1;
		}
		//Запрос к аяксу
		BX.ajax({
			url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				phone: this.sendSMSInput.value,
				code: this.codeInput.value,
				CHECK_SMS: 1
			},
			onsuccess: BX.delegate(function(result){
				console.log(result);
				if(result && result['TEXT'] == 'INVALID CODE') {
					this.createError(this.codeInput);
				} else if (result && result['TEXT'] == 'Authorized') {
					this.authorizeCheck(result);
				}
			}, this)
		});
	},


	timerSMS : function (timerSec, timerTime) {
		this.t = Date.parse(timerTime) - Date.parse(new Date());
		this.seconds = Math.floor((this.t / 1000) % 60);
		timerSec.innerHTML = this.seconds;
	},

	showSmsCode : function () {
		this.phoneText.innerText = this.sendSMSInput.value;//Меняем номер телефона в блоке
		BX.style(this.authBlock, 'display', 'none');//Деактивация блока авторизации
		BX.style(this.smsCodeBlock, 'display', '');//Активация блока с СМС кодом
		$('#code_sms_checkout').focus();
	},

	authorizeCheck : function (result) {

		console.log(result);
		this.user_adress = result['PERSONAL_STREET'];
		this.user_name = result['NAME'];
		this.user_last_name = result['LAST_NAME'];
		this.user_email = result['EMAIL'];

		$('#suggest').val(this.user_adress)
		$('#user_email').val(this.user_email);
		$('#user_name').val(this.user_name);
		$('#user_surname').val(this.user_last_name);

		if(result['NAME'] == null || result['LAST_NAME'] == null || result['EMAIL'] == null) {
			this.nextStep('main_data_user', 'phone_auth');
		} else if (result['PERSONAL_STREET'] == null || result['PERSONAL_STREET'] == '') {
			this.nextStep('user_adress', 'phone_auth');
		} else {
			this.updateCheckout();
		}
	},

	showAuth : function () {
		if(this.timerOut == true)
			return;
		if(this.tryAttempsCount >= this.tryAttemptsMax && this.captchaActive == 'Y'){
			BX.style(this.captchaBlock, 'display', '');//Актвация блока с капчей
		}
		console.log(this.tryAttempsCount + ' : ' + this.tryAttemptsMax + ' : ' + this.captchaActive);
		BX.style(this.authBlock, 'display', '');//Активация блока авторизации
		BX.style(this.smsCodeBlock, 'display', 'none');//Деактивация блока с СМС кодом
	},
	createError : function(element){
		BX.addClass(element, 'error_input');
		BX.bind(element, 'input', BX.proxy(this.codeError.bind(this, element), this));
	},

	codeError : function (element) {
		BX.removeClass(element, 'error_input');
	},
	sendMainInfo : function() {
		//Проверка введеных полей
		this.checkOutMainInfo();
		if(this.inputErrors)
			return;
		if($('#suggest').val() != '')
			this.nextStep('checkout_block', 'main_data_user');
		else
			this.nextStep('user_adress', 'main_data_user');
		//Запрос к аяксу
		BX.ajax({
			url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {
				email: this.mainInfoEMail.value,
				name: this.mainInfoName.value,
				surname: this.mainInfoSurname.value,
				updateUser: 1
			},
			onsuccess: BX.delegate(function(result){

			}, this)
		});
	},
	emailValidation : function (value) {
		let txt = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		return txt.test(value);
	},

	checkEmail : function () {

		if (this.emailValidation(this.mainInfoEMail.value)) {
			BX.removeClass(this.mainInfoEMail, 'error_input');
			this.inputErrors = false;
		} else {
			BX.addClass(this.mainInfoEMail, 'error_input');
			this.inputErrors = true;
		}
	},

	checkOutMainInfo : function() {
		this.inputErrors = false;
		this.checkEmail();
		if (this.mainInfoEMail.value == '') {
			this.inputErrors = true;
		}
		if (this.mainInfoName.value == '') {
			this.createError(this.mainInfoName);
			this.inputErrors = true;
		}
		if (this.mainInfoSurname.value == '') {
			this.createError(this.mainInfoSurname);
			this.inputErrors = true;
		}
	},
	nextStep : function (step_name, prev_step_name){
		if(step_name == 'checkout_block' || step_name == 'user_adress')
			BX.style(BX(step_name), 'display', 'flex');
		else
			BX.show(BX(step_name));
		BX.hide(BX(prev_step_name));

		switch (step_name) {
			case 'user_adress':
				if(!this.edit_address) {
					BX.addClass(BX('step_address'), 'actual');
					BX.removeClass(BX('step_authorize'), 'actual');
					BX.addClass(BX('step_authorize'), 'completed');
				}
				break;
			case 'checkout_block':
				BX.hide(BX('steps_wrap'));
				break;
		}
		window.scrollTo(0,0);
	},
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
