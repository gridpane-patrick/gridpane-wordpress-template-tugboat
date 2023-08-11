jQuery(document).ready(function ($) {
	// Your code here
	$('form#ta_product_setting_form').on('submit', function (e) {
		e.preventDefault();
		
		var product_category = jQuery("select[name='product_category'] option:selected").val();
		var product_name = jQuery("input[name='product_name']").val();
		var product_ids = jQuery("input[name='product_ids']").val();
		
		if(product_name === '' && product_ids === '' && product_category === ''){
			$('.import_response').html('من فضلك اختار القسم اولا ثم اضغط على استيراد');
		} else if(product_name !== '' && product_ids !== ''){
			$('.import_response').html('من فضلك استخدم اسم المنتج او اكود المنتج، لا يمكن استيراد المنتج باستخدام العنصرين معا');
		} else {
			var formData = $(this).serializeArray();
			$.ajax({
				method: 'POST',
				url: ta_admin.ajaxURL,
				data: formData,
				beforeSend: function () {
					$('.import_running').removeClass('import_success').addClass('loading');
					$('.btn-import_products').attr('disabled', true);
					$('.import_response').html('');
				},
				success: function (response) {
					$('.import_running').removeClass('loading').addClass('import_success');
					$('.btn-import_products').removeAttr('disabled');
					$('.import_response').html(response);
				}
			});
		}
	});

	// js validation for product price with taager product
	if ('1' == ta_admin.taager_product) {
		
		$('#_sale_price, #ta__shipping_charge').on('change', function () {

			if (!$("#ta__shipping_charge").is(":checked")) {
				
				if (parseFloat($(this).val()) < parseFloat(ta_admin.taager_price)) {
					alert('Product price should be atleast ' + ta_admin.currency + ta_admin.taager_price + '.');
					$('#publish').attr('disabled', true);
				} else {
					$('#publish').removeAttr('disabled');
				}
			} 
			else {
				if (parseFloat($('#_sale_price').val()) < parseFloat(ta_admin.ta_new_min_price)) {
					alert('Product price should be atleast ' + ta_admin.currency + ta_admin.ta_new_min_price + '.');
					$('#publish').attr('disabled', true);
				} else {
					$('#publish').removeAttr('disabled');
				}
			}
		});
		
		$('#_regular_price, #ta__shipping_charge').on('change', function () {

			if (!$("#ta__shipping_charge").is(":checked")) {
				
				if (parseFloat($(this).val()) < parseFloat(ta_admin.taager_price)) {
					alert('Product price should be atleast ' + ta_admin.currency + ta_admin.taager_price + '.');
					$('#publish').attr('disabled', true);
				} else {
					$('#publish').removeAttr('disabled');
				}
			} 
			else {
				if (parseFloat($('#_regular_price').val()) < parseFloat(ta_admin.ta_new_min_price)) {
					alert('Product price should be atleast ' + ta_admin.currency + ta_admin.ta_new_min_price + '.');
					$('#publish').attr('disabled', true);
				} else {
					$('#publish').removeAttr('disabled');
				}
			}
		});
	}
});

/* For change taager menu */
jQuery(document).on('ready', function() {
	
	jQuery("#toplevel_page_taager_account ul li").each(function() { 
		if (this?.children[0]?.text == 'Taager – منصة تاجر'){
			this.children[0].text = 'ربط المتجر';
		}
	});
	
});

jQuery(document).ready(function ($) {
	
	jQuery('form#ta_shipping_setting_form').on('submit', function (e) {
		e.preventDefault();
		
		var formData = $(this).serializeArray();
		console.log(formData);
		$.ajax({
			method: 'POST',
			url: ta_admin.ajaxURL,
			data: formData,
			beforeSend: function () {
				$('.ta_shipping_running').removeClass('shipping_success').addClass('loading');
				$('.btn-shippig_update').attr('disabled', true);
				$('.ta_shipping_response').html('');
			},
			success: function (response) {
				$('.ta_shipping_running').removeClass('loading').addClass('shipping_success');
				$('.btn-shippig_update').removeAttr('disabled');
				var shipping_update_text = 'تم التحديث بنجاح';
				$('.ta_shipping_response').html(shipping_update_text);
				$('.ta_last_updated_time').html(response);
			}
		});
	});
	
});

//make taager price textbox readonly
jQuery(document).on('ready', function() {
	jQuery('.cs_disable_taager_field').attr('readonly', 'true');	
	jQuery('.cs_disable_taager_field').css('background-color', '#EEEEEE');	
});

/* For price increased by section */
jQuery(document).on('ready', function() {
	jQuery("tr.increase_price_section").hide();
    jQuery("input[name$='enable_increase_price']").on('click',function() {
        var enable_increase_price = jQuery(this).val();
		if(enable_increase_price == 1) {
			jQuery("tr.increase_price_section").show();
		} else {	
			jQuery("tr.increase_price_section").hide();
		}
    });
});