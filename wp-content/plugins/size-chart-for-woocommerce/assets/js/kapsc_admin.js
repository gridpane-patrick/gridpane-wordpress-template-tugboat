jQuery(function($) {

	"use strict";

	var ajaxurl = kapsc_php_vars.admin_url;
	var nonce   = kapsc_php_vars.nonce;

	jQuery('.chose_select_brand').select2({
	}); 
	
	$('#kapsc_products').select2({

		ajax: {
			url: ajaxurl, // AJAX URL is predefined in WordPress admin
			dataType: 'json',
			type: 'POST',
			delay: 250, // delay in ms while typing when to perform a AJAX search
			data: function (params) {
				return {
					q: params.term, // search query
					action: 'KA_Psc_Search_Products', // AJAX action for admin-ajax.php
					nonce: nonce // AJAX nonce for admin-ajax.php
				};
			},
			processResults: function( data ) {
				var options = [];
				if ( data ) {
   
					// data is the array of arrays, and each of them contains ID and the Label of the option
					$.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
						options.push( { id: text[0], text: text[1]  } );
					});
   
				}
				return {
					results: options
				};
			},
			cache: true
		},
		multiple: true,
		placeholder: 'Choose Products',
		minimumInputLength: 3 // the minimum of symbols to input before perform a search
		
	});

	

	$('.kapsc_country').select2();

	$('#kapsc_apply_on_all_products').change(function () {
		if (this.checked) { 
			//  ^
			$('.hide_all_pro').fadeOut('fast');
		} else {
			$('.hide_all_pro').fadeIn('fast');
		}
	});

	if ($("#kapsc_apply_on_all_products").is(':checked')) {
		$(".hide_all_pro").hide();  // checked
	} else {
		$(".hide_all_pro").show();
	}

	$(".child").on("click",function() {
		$parent = $(this).prevAll(".parent");
		if ($(this).is(":checked")) {
			$parent.prop("checked",true);
		} else {
			var len = $(this).parent().find(".child:checked").length;
			$parent.prop("checked",len>0);
		}
	});
	$(".parent").on("click",function() {
		$(this).parent().find(".child").prop("checked",this.checked);
	});

	var c_type = $("#kapsc_chart_type option:selected").val();
	if (c_type == 'chart_img') {
		$('#ka_psc_img').show();
	} else {
		$('#ka_psc_img').hide();
	}

	if (c_type == 'chart_table') {
		$('#ka_psc_table').show();
	} else {
		$('#ka_psc_table').hide();
	}

	var chart_as = $("#kapsc_chart_as option:selected").val();
	if (chart_as == 'chart_tab') {
		$('.kapsc_btn_args').hide();
	} else if (chart_as == 'chart_btn') {
		$('.kapsc_btn_args').show();
	}


	 var table         = $( '.kapsc_admin_chart_table' ),
		total_rows     = table.find( 'tr' ).length - 1,
		total_coloums  = table.find( 'th' ).length - 1,
		hide_input     = $( '#kapsc_hidden_tab_fld' ),
		add_new_row    = function () {
			var add_row = '<tr>';
			for ( var i = 0; i < total_coloums; i++ ) {
				add_row += '<td><input class="kapsc_table_input" type="text" value=""/></td>';
			}
			add_row += '<td class="kapsc_buttons_in_row"><button type="button" class="kapsc_add_row kapsc_add_row_btn kapsc_button">+</button><button type="button" class="kapsc_rem_row kapsc_rem_row_btn kapsc_button">-</button></td>';
			add_row += '</tr>';
			return add_row;
		},
		add_new_coloum = function ( cell_id ) {
			var add_minus_btn    = '<th><button type="button" class="kapsc_add_col kapsc_add_col_btn kapsc_button">+</button><button type="button" class="kapsc_rem_col kapsc_rem_col_btn kapsc_button">-</button></th>',
				add_input_in_col = '<td><input class="kapsc_table_input" type="text" /></td>';

			table.find( 'thead tr' ).find( 'th:eq(' + cell_id + ')' ).after( add_minus_btn );
			table.find( 'tbody tr' ).each( function () {
				$( this ).find( 'td:eq(' + cell_id + ')' ).after( add_input_in_col );
			} );
		},
		remove_new_col = function ( cell_id ) {
			table.find( 'thead tr' ).find( 'th:eq(' + cell_id + ')' ).remove();
			table.find( 'tbody tr' ).each( function () {
				$( this ).find( 'td:eq(' + cell_id + ')' ).remove();
			} );
		},
		table_box      = function () {
			var box_arr = [];

			table.find( 'tbody tr' ).each( function () {
				var coloums = [],
					get_td  = $( this ).find( 'td' );

				get_td.each( function () {
					if ( !$( this ).is( '.kapsc_buttons_in_row' ) ) {
						var input_value = $( this ).find( 'input' ).val();
						coloums.push( input_value );
					}
				} );

				box_arr.push( coloums );
			} );

			hide_input.val( JSON.stringify( box_arr ) );
		};


	table
		.on( 'click', '.kapsc_add_row', function () {
			var this_cell = $( this ).closest( 'td' ),
				this_row  = this_cell.closest( 'tr' );

			total_rows++;
			this_row.after( add_new_row() );
			table_box();
		} )

		.on( 'click', '.kapsc_rem_row', function () {
			if ( total_rows < 2 ) {
				return;
			}

			var this_cell = $( this ).closest( 'td' ),
				this_row  = this_cell.closest( 'tr' );

			total_rows--;
			this_row.remove();
			table_box();
		} )

		.on( 'click', '.kapsc_add_col', function () {
			var this_cell = $( this ).closest( 'th' ),
				cell_id   = this_cell.index();

			total_coloums++;
			add_new_coloum( cell_id );
			table_box();
		} )

		.on( 'click', '.kapsc_rem_col', function () {
			if ( total_coloums < 2 ) {
				return;
			}
			var this_cell = $( this ).closest( 'th' ),
				cell_id   = this_cell.index();

			total_coloums--;
			remove_new_col( cell_id );
			table_box();
		} )

		.on( 'keyup', 'input', function ( event ) {
			var current_input = $( event.target ),
				value         = current_input.val();
				
			if ( value.search( /<[^>]+>/ig ) >= 0 || value.search( '<>' ) >= 0 || value.search( '“' ) >= 0 ) {
				current_input.val( value.replace( /<[^>]+>/ig, '' ).replace( '<>', '' ).replace( '“', '"' ) );
			}

			table_box();
		} );

});

function KA_Psc_GetUserRole(value) {

	"use strict";
	if (value == 'chart_img') {
		jQuery('#ka_psc_img').show();
	} else {
		jQuery('#ka_psc_img').hide();
	}
	if (value == 'chart_table') {
		jQuery('#ka_psc_table').show();
	} else {
		jQuery('#ka_psc_table').hide();
	}
}

function kapsc_image() { 

	"use strict";
	var image = wp.media({ 
		title: 'Upload Image',
		// mutiple: true if you want to upload multiple files at once
		multiple: false
	}).open()
	.on('select', function(){
		// This will return the selected image from the Media Uploader, the result is an object
		var uploaded_image = image.state().get('selection').first();
		// We convert uploaded_image to a JSON object to make accessing it easier
		// Output to the console uploaded_image
		var image_url = uploaded_image.toJSON().url;
		// Let's assign the url value to the input field
		jQuery('#kapsc_thumb_url').val(image_url);
		jQuery('#logodisplay').html("<img src='"+image_url+"' width='200' />");
	});

}

function kapsc_clear_image() {

	"use strict";
	jQuery('#kapsc_thumb_url').val('');
	jQuery('#logodisplay').html("");

}

function KA_Psc_GetChart_as(value) {

	"use strict";
	if (value == 'chart_tab') {
		jQuery('.kapsc_btn_args').hide();
	} else if (value == 'chart_btn') {
		jQuery('.kapsc_btn_args').show();
	}

}
