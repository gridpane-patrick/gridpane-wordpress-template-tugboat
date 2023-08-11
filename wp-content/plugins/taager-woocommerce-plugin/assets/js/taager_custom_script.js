jQuery(document).ready(function(){
	jQuery('#billing_phone').on('blur change',function(){
		var input = jQuery(this).val().length;
		var input_val = jQuery(this).val();
		var formRow = jQuery(this).parents('.form-row');
		
		let phoneNumberLength = document.getElementById('billing_phone').attributes.maxlength.nodeValue;
		if (phoneNumberLength <= 10) {
			arabicNumberOfDigits = `${phoneNumberLength} أرقام`;
		} else {
			arabicNumberOfDigits = `${phoneNumberLength} رقم`;
		}
		
		if(input<phoneNumberLength || isNaN(input_val)){
			if(jQuery('#nextractor').length == 0){
				jQuery(this).parent().append(`<span id="nextractor" class="error" style="color:red">رقم الهاتف يجب ان يحتوى على ${arabicNumberOfDigits} فقط</span>`);
			}
			formRow.addClass('woocommerce-invalid'); 
			formRow.removeClass('woocommerce-validated'); 
		}else{
			jQuery(this).parent().find('#nextractor').remove();
			formRow.addClass('woocommerce-validated');
		}
	});
	jQuery('#billing_phone2').on('blur change',function(){
		var input2 = jQuery(this).val().length;
		var input_val2 = jQuery(this).val();
		var formRow2 = jQuery(this).parents('.form-row');
		
		let phoneNumberLength = document.getElementById('billing_phone2').attributes.maxlength.nodeValue;
		if (phoneNumberLength <= 10) {
			arabicNumberOfDigits = `${phoneNumberLength} أرقام`;
		} else {
			arabicNumberOfDigits = `${phoneNumberLength} رقم`;
		}

		if(input2 != 0 ) {
			if(input2<phoneNumberLength || isNaN(input_val2)){
				if(jQuery('#nextractor2').length == 0){
					jQuery(this).parent().append(`<span id="nextractor2" class="error" style="color:red">رقم الهاتف البديل يحتوى على ${arabicNumberOfDigits} فقط</span>`);
				}
				formRow2.addClass('woocommerce-invalid'); 
				formRow2.removeClass('woocommerce-validated'); 
			}else{
				jQuery(this).parent().find('#nextractor2').remove();
				formRow2.addClass('woocommerce-validated');
			}
		} else {
			jQuery(this).parent().find('#nextractor2').remove();
		}
	});
});