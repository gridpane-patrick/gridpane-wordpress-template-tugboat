<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wt_iew_export_main">
	<p><?php echo $step_info['description']; ?></p>
	<div class="wt_iew_warn wt_iew_post_type_wrn" style="display:none;">
		<?php _e( 'Please select a post type', 'order-import-export-for-woocommerce' );?>
	</div>
	<table class="form-table wt-iew-form-table">
		<tr>
			<th><label><?php _e( 'Select a post type to export', 'order-import-export-for-woocommerce' ); ?></label></th>
			<td>
				<select name="wt_iew_export_post_type">
					<option value="">-- <?php _e( 'Select post type', 'order-import-export-for-woocommerce' ); ?> --</option>
					<?php
					foreach($post_types as $key=>$value)
					{
						?>
						<option value="<?php echo $key;?>" <?php echo ($item_type==$key ? 'selected' : '');?>><?php echo $value;?></option>
						<?php
					}
					?>
				</select>
			</td>
			<td></td>
		</tr>
	</table>
	<br/>
	<?php 
	$wt_iew_post_types = array(
		'product' => array(
			'message' => __( 'The <b>Product Import Export for WooCommerce Add-On</b> is required to export WooCommerce Products.', 'order-import-export-for-woocommerce' ),
			'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo')
		),
		'product_review' => array(
			'message' => __( 'The <b>Product Import Export for WooCommerce Add-On</b> is required to export WooCommerce Product reviews.', 'order-import-export-for-woocommerce' ),
			'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo')
		),
		'product_categories' => array(
			'message' => __( 'The <b>Product Import Export for WooCommerce Add-On</b> is required to export WooCommerce Product categories.', 'order-import-export-for-woocommerce' ),
			'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo')
		),
		'product_tags' => array(
			'message' => __( 'The <b>Product Import Export for WooCommerce Add-On</b> is required to export WooCommerce Product tags.', 'order-import-export-for-woocommerce' ),
			'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=product-import-export-for-woo')
		),
		'user' => array(
			'message' => __( 'The <b>Import Export WordPress Users and WooCommerce Customers Add-On</b> is required to export users/customers.', 'order-import-export-for-woocommerce' ),
			'link' => admin_url('plugin-install.php?tab=plugin-information&plugin=users-customers-import-export-for-wp-woocommerce')
		),
		'subscription' => array(
			'message' => __( 'The <b>Order, Coupon, Subscription Export Import for WooCommerce</b> premium is required to export WooCommerce Subscriptions.', 'order-import-export-for-woocommerce' ),
			'link' => esc_url( 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp_post_type&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=' . WT_O_IEW_VERSION )
		),		
	);
	foreach ($wt_iew_post_types as $wt_iew_post_type => $wt_iew_post_type_detail) { ?>
			
	<div class="wt_iew_free_addon wt_iew_free_addon_warn <?php echo 'wt_iew_type_'.$wt_iew_post_type; ?>" style="display:none">
		<p><?php echo $wt_iew_post_type_detail['message']; ?></p>
		<?php 
		$install_now = esc_html( 'Install now for free', 'order-import-export-for-woocommerce' ); 
		$is_pro = false;
		if( 'subscription' === $wt_iew_post_type ){
			$install_now = esc_html( 'Get the plugin', 'order-import-export-for-woocommerce' ); 
			$is_pro = true;
		}
		?>		
		<a target="_blank" href="<?php echo $wt_iew_post_type_detail['link']; ?>"><?php esc_attr_e( $install_now ); ?></a>
	</div>
	
	<?php
	}
	?>
</div>