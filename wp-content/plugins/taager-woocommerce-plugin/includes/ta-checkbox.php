<?php


add_action('woocommerce_product_options_general_product_data', 'ta_shipping_options');
function ta_shipping_options()
{
	if (isset($_GET['post'])) {
		$product_id = intval($_GET['post']);
		$is_taager_product = get_post_meta($product_id, 'taager_product', true);
		if ($is_taager_product) {
			echo '<div class="options_group">';

			woocommerce_wp_checkbox(array(
				'id'      => 'ta__shipping_charge',
				//'value'   => get_post_meta( $product_id, 'ta__shipping_charge', true ),
				'label'   => 'Free Shipping',
				'desc_tip' => true,
				'description' => 'Free shipping',
			));

			echo '</div>';
		}
	}


	/* add_action( 'woocommerce_process_product_meta', 'ta_shipping_save', 10, 2 );
function ta_shipping_save( $id, $post ){
		update_post_meta( $id, 'ta__shipping_charge', $_POST['ta__shipping_charge'] );
} */
}


/* add_action( 'woocommerce_process_product_meta', 'ta_shipping_save');
function ta_shipping_save($post_id){
  $ta__shipping_charge=  $_POST['ta__shipping_charge'];
  if(!empty($ta__shipping_charge)){
        update_post_meta( $post_id, 'ta__shipping_charge', esc_attr( $ta__shipping_charge ) );
  }
} */

// add_action( 'woocommerce_process_product_meta', 'ta_shipping_save', 10, 2 );
// function ta_shipping_save( $id, $post ){
// 		update_post_meta( $id, 'ta__shipping_charge', $_POST['ta__shipping_charge'] );
// }
add_action('woocommerce_process_product_meta', 'taager_shipping_save', 10, 2);
function taager_shipping_save($id, $post)
{
	$ta__shipping_charge = sanitize_text_field($_POST['ta__shipping_charge']);
	if ($ta__shipping_charge === 'yes') {
		update_post_meta($id, 'ta__shipping_charge', $ta__shipping_charge);
	} else {
		delete_post_meta($id, 'ta__shipping_charge');
	}
}
