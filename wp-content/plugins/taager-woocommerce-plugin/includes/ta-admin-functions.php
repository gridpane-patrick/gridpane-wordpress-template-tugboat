<?php

defined( 'ABSPATH' ) || exit;

// Create WordPress admin menu for Taager API Setting
add_action( 'admin_menu', 'taager_menu_setup' );
function taager_menu_setup() {
	global $wpdb;
	$ta_initial_status = get_option( 'ta_initial_status' );

	$page_title = 'Taager Account';
	$menu_title = 'Taager - منصة تاجر';
	$capability = 'manage_options';
	$menu_slug  = 'taager_setting';
	$function   = 'taager_account_page';
	$icon_url   = 'dashicons-media-code';
	$position   = 85;

	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

	$parent_slug         = 'taager_setting';
	$sub_menu_page_title = 'استيراد المنتجات';

	$sub_menu_title      = 'استيراد المنتجات';
	$sub_menu_capability = 'manage_options';
	$sub_menu_slug       = 'taager_product_setting';
	$sub_menu_function   = 'taager_product_setting_page';

	$sub_menu_page_title3 = 'تحديث الاسعار';
	$sub_menu_title3      = 'تحديث الاسعار';
	$sub_menu_capability3 = 'manage_options';
	$sub_menu_slug3       = 'taager_price_update_list';
	$sub_menu_function3   = 'taager_price_update_list_fn';
	
	$country_selection_page_title = 'اختيار البلد';
	$country_selection_title      = 'اختيار البلد';
	$country_selection_capability = 'manage_options';
	$country_selection_slug       = 'taager_country_selection';
	$country_selection_function   = 'taager_country_selection_page';
	
	
	if ( $ta_initial_status == 'done' ) {
		add_submenu_page( $parent_slug, $sub_menu_page_title, $sub_menu_title, $sub_menu_capability, $sub_menu_slug, $sub_menu_function, 0 );
		
	
	  	// add_submenu_page( $parent_slug, $sub_menu_page_title3, $sub_menu_title3, $sub_menu_capability3, $sub_menu_slug3, $sub_menu_function3, 20 );
		
	} else if ( $ta_initial_status == 'country_selection') {
		add_submenu_page( $parent_slug, $country_selection_page_title, $country_selection_title, $country_selection_capability, $country_selection_slug, $country_selection_function, 0 );
	}
}

/**
 * Create cron job to import products with filters
 */
// add_action( 'admin_post_ta_products_filter', 'ta_products_filter' );
function ta_products_filter() {
	if ( isset( $_POST['product_category'] ) && isset( $_POST['product_name'] ) ) {
		$product_category = $_POST['product_category'];
		$product_name     = $_POST['product_name'];

		$ta_product_import_status = get_option( 'ta_product_import_status' );

		if ( ! $ta_product_import_status || $ta_product_import_status == 'done' ) {
			if ( isset( $_POST['once_only'] ) ) {
				ta_import_products( $product_category, $product_name );
				wp_redirect( admin_url( 'edit.php?post_type=product' ) );
			} else {
				$last_category_filter = get_option( 'ta_last_category_filter' );
				$last_name_filter     = get_option( 'ta_last_name_filter' );

				$last_args = array( $last_category_filter, $last_name_filter );
				// wp_clear_scheduled_hook( 'taager_import_products', $last_args );

				update_option( 'ta_last_category_filter', $product_category );
				update_option( 'ta_last_name_filter', $product_name );

				$args = array( $product_category, $product_name );
				// Delay the first run of `taager_import_products` by 1 mins
				// wp_schedule_event( ( time() + MINUTE_IN_SECONDS ), 'twicedaily', 'taager_import_products', $args );

				wp_redirect( admin_url( 'edit.php?post_type=product' ) );
			}
		} elseif ( $ta_product_import_status == 'running' ) {
			wp_redirect( admin_url( 'admin.php?page=taager_product_setting' ) );
		}

		exit;
	}
}

function taager_shipping_page() {
	
	$taager_last_update_provinces = get_option('taager_last_update_provinces');
	
	?>
	<div class="ta_shipping_setting_section">
	
		<h1 class="ta_setting_heading">اعدادات الشحن</h1>
		<form id="ta_shipping_setting_form" method="post" action="<?php //echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="taager_shipping_update" />
			<table class="form-table">
				<tr>
					<th>اضغط هنا لتحديث اعدادات واسعار الشحن:</th>
					<td><?php submit_button( 'تحديث', 'primary btn-shippig_update' ); ?></td>
				</tr>
				
				<?php 
				if($taager_last_update_provinces!='') { ?>
				<tr>
					<th>تاريخ اخر تحديث:</th>
					<td class="ta_last_updated_time"><?php echo date('d/m/Y h:i:s A', strtotime($taager_last_update_provinces)); ?></td>
				</tr>
				<?php } ?>
			</table>
			<div class="ta_shipping_running"><img src="<?php echo plugin_dir_url(  __DIR__  )?>assets/images/spin.gif">التحديث</div>
			<div class="ta_shipping_response"></div>
		</form>
	</div>	
	<?php
}	