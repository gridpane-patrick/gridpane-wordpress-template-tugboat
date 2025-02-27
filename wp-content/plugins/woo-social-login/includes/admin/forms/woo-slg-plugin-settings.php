<?php
/**
 * Settings Page
 *
 * The code for the plugins main settings page
 * 
 * @package WooCommerce - Social Login
 * @since 1.6.4
 */

// call globals to use them in this page
global $woo_slg_model, $woo_slg_options;
?>

<div class="wrap">

	<!-- wpweb logo -->
	<img src="<?php echo esc_url(WOO_SLG_IMG_URL) . '/wpweb-logo.png'; ?>" class="woo-slg-wpweb-logo" alt="<?php esc_html_e( 'WP Web Logo', 'wooslg' );?>" />
	
	<!-- plugin name -->
	<h2><?php esc_html_e( 'Social Login - Settings', 'wooslg' ); ?></h2>
	
	<!-- settings reset -->
	<?php
		if( isset( $_POST['woo_slg_reset_settings'] ) && $_POST['woo_slg_reset_settings'] == esc_html__( 'Reset All Settings', 'wooslg' ) ) {
			
			
			update_option( 'woo_slg_delete_options', 'yes' ); // update enable delete option
			woo_slg_uninstall(); // Call uninstall function for remove all option
			woo_slg_install(); // Call install function for set default option
			
			//Global Options
			$woo_slg_options	= woo_slg_global_settings();

			//reseting facebook fan page posting setting session
			echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'All Settings Reset Successfully.', 'wooslg') . '</strong></p></div>'; 
			
		} else if( isset( $_POST['woo-slg-set-submit'] ) && !empty( $_POST['woo-slg-set-submit'] ) ) { //check settings updated or not
			
			//reseting facebook fan page posting setting session
			echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Changes Saved.', 'wooslg') . '</strong></p></div>'; 
		}
	?>
	
	<!--plugin reset settings button-->		
	<?php 
	
	extract( $woo_slg_options );

	$woo_social_settings_page_url = add_query_arg( array( 'page' => 'woo-social-settings'), admin_url( 'admin.php' ) );

		echo apply_filters ( 'woo_slg_settings_reset_form', '<form action="'.esc_url($woo_social_settings_page_url).'" method="POST" id="woo-slg-reset-settings-form">
			<div class="woo-slg-reset-wrapper">
				<input type="submit" class="button-primary" name="woo_slg_reset_settings" id="woo_slg_reset_settings" value="' . esc_html__( 'Reset All Settings', 'wooslg' ) . '" />
			</div>
		</form>' );
	?>
	<br />
	<form action="<?php echo $woo_social_settings_page_url; ?>" method="post">
		
		<?php 

		$general_tab = $facebook_tab = $google_plus_tab = $linkedin_tab = $twitter_tab = $yahoo_tab = $foursquare_tab = $windows_live_tab = $vk_tab = $amazon_tab = $paypal_tab = $misc_tab = $email_login_tab = $apple_login_tab = $line_tab = '';
		$general_content = $facebook_content = $google_plus_content = $linkedin_content = $twitter_content = $yahoo_content = $foursquare_content = $windows_live_content = $vk_content = $amazon_content = $paypal_content = $line_content = $misc_content = $apple_login_content = $email_login_content = '';
		$selected_tab = 'general';

		if( isset($_POST['woo_slg_options_selected_tab']) && !empty($_POST['woo_slg_options_selected_tab']) ){
			// If settings update
			$selected_tab = $this->model->woo_slg_escape_slashes_deep( $_POST['woo_slg_options_selected_tab'] );
		} elseif( !empty($_GET['tab']) ) {
			//If tab argument from sort order page
			$selected_tab = $woo_slg_model->woo_slg_escape_slashes_deep( $_GET['tab'] );
		}

		switch( $selected_tab ) {
				
			case 'facebook' : 
							$facebook_tab = ' nav-tab-active';
							$facebook_content = ' woo-slg-selected-tab';
							break;
			case 'googleplus' : 
							$google_plus_tab = ' nav-tab-active';
							$google_plus_content = ' woo-slg-selected-tab';
							break;
			case 'linkedin' : 
							$linkedin_tab = ' nav-tab-active';
							$linkedin_content = ' woo-slg-selected-tab';
							break;
			case 'twitter' : 
							$twitter_tab = ' nav-tab-active';
							$twitter_content = ' woo-slg-selected-tab';
							break;
			case 'yahoo' : 
							$yahoo_tab = ' nav-tab-active';
							$yahoo_content = ' woo-slg-selected-tab';
							break;
			case 'foursquare' : 
							$foursquare_tab = ' nav-tab-active';
							$foursquare_content = ' woo-slg-selected-tab';
							break;
			case 'windowslive' : 
							$windows_live_tab = ' nav-tab-active';
							$windows_live_content = ' woo-slg-selected-tab';
							break;
			case 'vk' : 
							$vk_tab = ' nav-tab-active';
							$vk_content = ' woo-slg-selected-tab';
							break;
			case 'amazon' : 
							$amazon_tab = ' nav-tab-active';
							$amazon_content = ' woo-slg-selected-tab';
							break;
			case 'paypal' : 
							$paypal_tab = ' nav-tab-active';
							$paypal_content = ' woo-slg-selected-tab';
							break;
			case 'line' : 
							$line_tab = ' nav-tab-active';
							$line_content = ' woo-slg-selected-tab';
							break;
			case 'misc' : 
							$misc_tab = ' nav-tab-active';
							$misc_content = ' woo-slg-selected-tab';
							break;
			case 'general' : 
							$general_tab = ' nav-tab-active';
							$general_content = ' woo-slg-selected-tab';
							break;
			case 'email' : 
							$email_login_tab = ' nav-tab-active';
							$email_login_content = ' woo-slg-selected-tab';
							break;

			case 'apple' : 
							$apple_login_tab = ' nav-tab-active';
							$apple_login_content = ' woo-slg-selected-tab';
							break;							
		}
	?>
	
		<!-- beginning of the left meta box section -->
		<div class="content woo-slg-content-section">
		
			<h2 class="nav-tab-wrapper woo-slg-h2">
				
				<?php
				    /**
			         * Fires woocommerce social login settings tab before.
			         *
			         * add custom setting tab in this page
			         * 
					 * @package WooCommerce - Social Login
					 * @since 1.6.4
			         */ 
				    do_action( 'woo_slg_settings_tab_before' ); 
		    	?>
		    	
				<a id="general_tab" class="nav-tab<?php echo $general_tab; ?>" href="#woo-slg-tab-general"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/wp-logo.png'; ?>" alt="<?php esc_html_e('General','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $facebook_tab; ?>" href="#woo-slg-tab-facebook"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/facebook.png'; ?>" alt="<?php esc_html_e('Facebook','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $google_plus_tab; ?>" href="#woo-slg-tab-googleplus"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/google-plus.png'; ?>" alt="<?php esc_html_e('Google','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $linkedin_tab; ?>" href="#woo-slg-tab-linkedin"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/linkedin.png'; ?>" alt="<?php esc_html_e('LinkedIn','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $twitter_tab; ?>" href="#woo-slg-tab-twitter"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/twitter.png'; ?>" alt="<?php esc_html_e('Twitter','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $yahoo_tab; ?>" href="#woo-slg-tab-yahoo"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/yahoo.png'; ?>" alt="<?php esc_html_e('Yahoo','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $foursquare_tab; ?>" href="#woo-slg-tab-foursquare"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/foursquare.png'; ?>" alt="<?php esc_html_e('Foursquare','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $windows_live_tab; ?>" href="#woo-slg-tab-windowslive"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/windows.png'; ?>" alt="<?php esc_html_e('Windows Live','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $vk_tab; ?>" href="#woo-slg-tab-vk"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/vk.png'; ?>" alt="<?php esc_html_e('VK','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $amazon_tab; ?>" href="#woo-slg-tab-amazon"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/amazon.png'; ?>" alt="<?php esc_html_e('Amazon','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $paypal_tab; ?>" href="#woo-slg-tab-paypal"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/paypal.png'; ?>" alt="<?php esc_html_e('Paypal','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $line_tab; ?>" href="#woo-slg-tab-line"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/line.png'; ?>" alt="<?php esc_html_e('Line','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $apple_login_tab; ?>" href="#woo-slg-tab-apple-login"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/apple.png'; ?>" alt="<?php esc_html_e('Apple','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $email_login_tab; ?>" href="#woo-slg-tab-email-login"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/email.png'; ?>" alt="<?php esc_html_e('Email','wooslg');?>"/></a>
				<a class="nav-tab<?php echo $misc_tab; ?>" href="#woo-slg-tab-misc"><img src="<?php echo esc_url(WOO_SLG_IMG_URL).'/tab-icon/misc-settings.png'; ?>" alt="<?php esc_html_e('Misc','wooslg');?>"/></a>
	        
		        <?php 
				    /**
			         * Fires woocommerce social login settings tab after.
			         *
			         * add custom setting tab in this page
			         * 
					 * @package WooCommerce - Social Login
					 * @since 1.6.4
			         */
				    do_action( 'woo_slg_add_settings_tab_after' ); 
				?>
		        
		    </h2><!--nav-tab-wrapper-->

		    <input type="hidden" id="woo_slg_selected_tab" name="woo_slg_options_selected_tab" value="<?php echo $selected_tab;?>"/>
		    <!--beginning of tabs panels-->
			 <div class="woo-slg-content">
			 	
			 	<?php 
				    /**
			         * Fires woocommerce social login settings tab data before.
			         *
			         * add custom setting in this page
			         * 
					 * @package WooCommerce - Social Login
					 * @since 1.6.4
			         */
				    do_action('woo_slg_add_settings_tab_data_before', $selected_tab );
			    ?>
			    
			 	<div class="woo-slg-tab-content<?php echo $general_content; ?>" id="woo-slg-tab-general"> 
				 	<?php   
				 		// General
						require( WOO_SLG_ADMIN . '/forms/woo-slg-general-settings.php' ); 
					?>
			 	</div>
			 	<div class="woo-slg-tab-content<?php echo $facebook_content; ?>" id="woo-slg-tab-facebook"> 
			 		<?php   
				 		// Facebook
						require( WOO_SLG_ADMIN . '/forms/woo-slg-facebook-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $google_plus_content; ?>" id="woo-slg-tab-googleplus"> 
			 		<?php   
				 		// Google Plus
						require( WOO_SLG_ADMIN . '/forms/woo-slg-google-plus-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $linkedin_content; ?>" id="woo-slg-tab-linkedin"> 
			 		<?php   
				 		// Linkedin
						require( WOO_SLG_ADMIN . '/forms/woo-slg-linkedin-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $twitter_content; ?>" id="woo-slg-tab-twitter"> 
			 		<?php   
				 		// Twitter
						require( WOO_SLG_ADMIN . '/forms/woo-slg-twitter-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $yahoo_content; ?>" id="woo-slg-tab-yahoo"> 
			 		<?php   
				 		// Yahoo
						require( WOO_SLG_ADMIN . '/forms/woo-slg-yahoo-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $foursquare_content; ?>" id="woo-slg-tab-foursquare"> 
			 		<?php   
				 		// Foursquare
						require( WOO_SLG_ADMIN . '/forms/woo-slg-foursquare-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $windows_live_content; ?>" id="woo-slg-tab-windowslive"> 
			 		<?php   
				 		// Windows Live
						require( WOO_SLG_ADMIN . '/forms/woo-slg-windows-live-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $vk_content; ?>" id="woo-slg-tab-vk"> 
			 		<?php   
				 		// VK
						require( WOO_SLG_ADMIN . '/forms/woo-slg-vk-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $amazon_content; ?>" id="woo-slg-tab-amazon"> 
			 		<?php   
				 		// Amazon
						require( WOO_SLG_ADMIN . '/forms/woo-slg-amazon-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $paypal_content; ?>" id="woo-slg-tab-paypal"> 
			 		<?php   
				 		// Paypal
						require( WOO_SLG_ADMIN . '/forms/woo-slg-paypal-settings.php' ); 
					?>
			 	</div>
				<div class="woo-slg-tab-content<?php echo $line_content; ?>" id="woo-slg-tab-line"> 
			 		<?php   
				 		// line
						require( WOO_SLG_ADMIN . '/forms/woo-slg-line-settings.php' ); 
					?>
			 	</div>

			 	<div class="woo-slg-tab-content<?php echo $email_login_content; ?>" id="woo-slg-tab-email-login"> 
			 		<?php   
				 		// Paypal
						require( WOO_SLG_ADMIN . '/forms/woo-slg-email-settings.php' ); 
					?>
			 	</div>


			 	<div class="woo-slg-tab-content<?php echo $apple_login_content; ?>" id="woo-slg-tab-apple-login"> 
			 		<?php   
				 		// Apple
						require( WOO_SLG_ADMIN . '/forms/woo-slg-apple-settings.php' ); 
					?>
			 	</div>
			 	
			 	<div class="woo-slg-tab-content<?php echo $misc_content; ?>" id="woo-slg-tab-misc"> 
			 		<?php   
				 		// Misc
						require( WOO_SLG_ADMIN . '/forms/woo-slg-misc-settings.php' ); 
					?>
			 	</div>
			 	
			 	<?php 
				    /**
			         * Fires woocommerce social login settings tab data after.
			         *
			         * add custom setting in this page
			         * 
					 * @package WooCommerce - Social Login
					 * @since 1.6.4
			         */
					
				    do_action( 'woo_slg_add_settings_tab_data_after' );
				?>
			 <!--end of tabs panels-->
			 </div>
		<!--end of the left meta box section -->
		</div><!--.content woo-slg-content-section-->
	</form>
	<div class="woo_slg_overlay"></div>
	<!-- including the about box -->
<!--end .wrap-->
</div>