(function($) {

    var salesBoosterSettings = {

        homeAdSettings: function() {
            // corner ad position inline
            $("._sale_booster_list_options").parents("li").css({"display": "inline-block", "margin-right": "10px"});
        
            /**
             * Added class into custom box
            */
            var customPopUp = $("#home_page_exit_custom_popup").parent();
            var customPopUpParent = customPopUp.parent().addClass("home_page_exit_custom_popup");

        /**
            * home custom popup execute
            */
            function checkCustomPopUp(value){
                if(value === "custom_html"){
                    customPopUpParent.show();
                } else {
                    customPopUpParent.hide();
                }
            }
        /**
            * initail check
            */
            var popUp = $("#home_page_exit_popup").val();
            checkCustomPopUp(popUp);
            /**
             * change custom popup
            */
            $("#home_page_exit_popup").change(function(e){
                value = e.target.value;
                checkCustomPopUp(value);
            })


        /**
         * Media upload on setting page
        */ 
            var mediaUploader;
            var inputValue = "";
            
            $('.upload_image').click(function(e) {

                if (e.target.id === 'upload_image_banner_below') {
                inputValue = $("#home_page_banner_below");
                } 
                
                if (e.target.id === 'upload_image_above_footer') {
                    inputValue = $("#home_page_banner_above_footer");
                } 
            
                if (e.target.id === 'upload_image_corner_ad') {
                    inputValue = $("#home_page_corner_ad");
                }
                
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Choose Image',
                    button: {
                    text: 'Choose Image'
                }, multiple: false });

                

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $(inputValue).val(attachment.url);
                });

                mediaUploader.open();
            });
        },
        init: function(){
            this.homeAdSettings();
        }
    } 
    salesBoosterSettings.init();

})( jQuery );
