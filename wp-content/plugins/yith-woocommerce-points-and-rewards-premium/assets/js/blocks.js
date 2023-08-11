jQuery(function ($) {
	"use strict";

	/* ywpar tabs */
	function openTab(evt, obj, tab) {
		obj.find('.ywpar_tabcontent').each( function() { $(this).hide(); });
		obj.find('.ywpar_tabs_links').each( function() { $(this).removeClass('active'); });
		obj.find('#' + tab).show();
		evt.currentTarget.className += " active";
	}

	$(document).find( '.ywpar_tabs_links.active').each( function(){
		$(this).click();
	});

	$(document).on('click', '.ywpar_tabs_links', function(e){
		var selected = $(this).closest('#ywpar_tabs');
		openTab(e, selected, $(this).data('target') );
	});

	$(document).on('yith_plugin_fw_gutenberg_success_do_shortcode', function(e){
		$(document).find( '.ywpar_tabs_links.active').each( function(){
			$(this).click();
		});
	});


});
