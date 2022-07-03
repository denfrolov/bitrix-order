function getContentAjax(url, formData, elementClass, afterFunc) {
	$.ajax({
		url: url,
		data: formData,
		type: "GET",
		dataType: "html",
		cache: false
	}).done(function (data) {
		if (!$(data).find(`.${elementClass}`).length) {
			location.reload()
		}
		$(`.${elementClass}`).each(function (index, value) {
			$(this).html(
				$(data).find(`.${elementClass}`).eq(index).html()
			)
		});
		if (afterFunc) {
			afterFunc();
		}
	});
}

$(document).on('click', '.js-quantity-button', function (event) {
	let input = $(this).parents('.item-quantity').find('input');
	let val = parseInt(input.val());
	if ($(this).hasClass('item-quantity__minus')) {
		val = val - 1;
	} else {
		val = val + 1;
	}
	if (val < 1) {
		val = 1
	}
	input.val(val).trigger('change')
});

$(document).on('change', '.js-quantity-input', function (event) {
	let basketId = $(this).parents('.js-basket-item').data('id');
	let quantity = $(this).val();
	let data = {
		ajax_mode: 'Y',
		METHOD: 'UPDATE',
		ID: basketId,
		QUANTITY: quantity
	};
	getContentAjax('', data, 'js-ajax-block')
});

$(document).on('click', '.js-basket-item-remove', function (event) {
	let parent = $(this).parents('.js-basket-item');
	let data = {
		ajax_mode: 'Y',
		METHOD: 'DELETE',
		ID: parent.data('id'),
	};
	parent.slideUp(300);
	setTimeout(function () {
		let displayed = $('.js-basket-item').filter(function () {
			let element = $(this);
			if (element.css('display') == 'none') {
				element.remove();
				return false;
			}
			return true;
		});
		if (!displayed.length) {
			location.reload()
		}
	}, 500)
	getContentAjax('', data, 'js-ajax-block-total')
});

$(document).on('change', '.js-delivery-btn', function (event) {
	getContentAjax('', $(this).parents('form').serialize() + 'save=n', 'js-ajax-block-total')
});


$(document).on('focus', '.form-control', function (event) {
	$(this).removeClass('error').parents('.form-group').find('span.js-danger').text('')
});

$(document).on('click', '.js-submit-close', function () {
	$('.submit-error').slideUp('300');
});
