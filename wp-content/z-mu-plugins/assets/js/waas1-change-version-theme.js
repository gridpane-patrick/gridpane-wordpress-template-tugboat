jQuery( document ).ready(function($){

	
	$( _wpThemeSettings.themes ).each(function( index ) {
		
		if( _wpThemeSettings.themes[index].waas1_repo ){
			
			var themeIdInRepo = _wpThemeSettings.themes[index].id;
			var themeNameInRepo = _wpThemeSettings.themes[index].name;
			var themeAppendHtml = _wpThemeSettings.themes[index].waas1_change_version;
			
			$(".themes .theme[data-slug='"+themeIdInRepo+"']").append( themeAppendHtml );
		}
	
	});
	

	$('.change-repo-version a').click(function(e){
		e.stopPropagation();
	});
	
});