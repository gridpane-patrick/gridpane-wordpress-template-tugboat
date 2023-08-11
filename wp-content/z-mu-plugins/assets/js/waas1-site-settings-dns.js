jQuery(document).ready(function($){
	
	
	$('.deleteRecord').click(function( e ){
		e.preventDefault();
		var recordId = $(this).closest( '.div-table-row' ).attr('data-recordid');
		var url = new URL( window.location.href );
		url.searchParams.set('sub-task', 'delete-record');
		url.searchParams.set('record-id', recordId);
		window.location = url.toString();
	});
	
	
	
});