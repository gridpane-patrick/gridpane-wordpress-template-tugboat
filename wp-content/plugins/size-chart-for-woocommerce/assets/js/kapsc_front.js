jQuery(document).ready(function($){
	$("div.kapsc_hid_OL").removeClass("kapsc-popup-overlay");
});


function kapsc_chartpopup(chartID) {

	jQuery(document).ready(function($){

		var btn = $('#kapsc_pop_btn-' + chartID);
		
		var overLayDiv = $(btn).next();
		$(overLayDiv).addClass("kapsc-popup-overlay");
		$(overLayDiv).css("background", "#000000bd");
		var getChart = $("#myPopup-" + chartID);
		
		var mag = $("woocommerce-product-gallery__trigger");

		// Get the <span> element that closes the popup

		var span = $(getChart).children(".close");
		
		console.log(span);

		$(getChart).css("display", "block");
		$(getChart).css("margin-top", "10% !important");
		// check the magnifier exits or not

		if (typeof mag !== 'undefined') {

			$(mag).css("display", "none");

		}
		

		// When the user clicks on <span> (x), close the popup
		$(span).on("click", function(){
			$(getChart).css("display", "none");
			$(overLayDiv).removeClass("kapsc-popup-overlay");
			$(overLayDiv).css("background");
			$(mag).css("display", "");
		});

		// When the user clicks anywhere outside of the popup, close it
		$(window).on("click", function(){
			$(getChart).css("display", "none");
			$(overLayDiv).removeClass("kapsc-popup-overlay");
			$(overLayDiv).css("background");
			$(mag).css("display", "");
		});

	});

}

