<?php
/**
 * The admin-specific template of the plugin for creation of order bumps.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/admin/partials/templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

/**
 * Bump Creation Template.
 *
 * This template is used for creating new bump as well
 * as viewing/editing previous bump.
 */

// New Bump id.
if ( ! isset( $_GET['bump_id'] ) ) {

	// Get all Bump.
	$wps_upsell_bumps_list = get_option( 'wps_ubo_bump_list', array() );

	if ( ! empty( $wps_upsell_bumps_list ) ) {

		// Temp bump variable.
		$wps_upsell_bumps_list_duplicate = $wps_upsell_bumps_list;

		// Make key pointer point to the end bump.
		end( $wps_upsell_bumps_list_duplicate );

		// Now key function will return last bump key.
		$wps_upsell_bumps_last_index = key( $wps_upsell_bumps_list_duplicate );

		/**
		 * So new bump id will be last key+1.
		 *
		 *Bump key in array is bump id. ( not really.. need to find, if bump is deleted then keys change)
		 */
		$wps_upsell_bump_id = $wps_upsell_bumps_last_index + 1;
	} else {

		// First bump.
		// Firstly it was 0 now changed it to 1, make sure that doesn't cause any issues.
		$wps_upsell_bump_id = 1;
	}
} else {

	// Retrieve new bump id from GET parameter when redirected from bump list's page.
	$wps_upsell_bump_id = sanitize_text_field( wp_unslash( $_GET['bump_id'] ) );
}

// When save changes is clicked.
if ( isset( $_POST['wps_upsell_bump_creation_setting_save'] ) ) {

	unset( $_POST['wps_upsell_bump_creation_setting_save'] );

	// Nonce verification.
	check_admin_referer( 'wps_upsell_bump_creation_nonce', 'wps_upsell_bump_nonce' );

	// Saved bump id.
	$wps_upsell_bump_id = ! empty( $_POST['wps_upsell_bump_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_id'] ) ) : 1;

	if ( empty( $_POST['wps_upsell_bump_target_categories'] ) ) {

		$_POST['wps_upsell_bump_target_categories'] = array();
	}

	if ( empty( $_POST['wps_upsell_bump_target_ids'] ) ) {

		$_POST['wps_upsell_bump_target_ids'] = array();
	}

	if ( empty( $_POST['wps_upsell_bump_status'] ) ) {

		$_POST['wps_upsell_bump_status'] = 'no';
	}

	// When price is saved.
	if ( empty( $_POST['wps_upsell_bump_offer_discount_price'] ) ) {

		if ( '' === $_POST['wps_upsell_bump_offer_discount_price'] ) {

			$_POST['wps_upsell_bump_offer_discount_price'] = '20';

		} else {

			$_POST['wps_upsell_bump_offer_discount_price'] = '0';
		}
	}

	// From these versions we will be having multiselect for schedules.
	if ( empty( $_POST['wps_upsell_bump_schedule'] ) || '' === $_POST['wps_upsell_bump_schedule'] ) {
		$_POST['wps_upsell_bump_schedule'] = array( '0' );
	}

	// New bump to be made.
	$wps_upsell_new_bump = array();

	// Sanitize and strip slashes for Texts.
	$wps_upsell_new_bump['wps_upsell_bump_status'] = ! empty( $_POST['wps_upsell_bump_status'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_status'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_name'] = ! empty( $_POST['wps_upsell_bump_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_name'] ) ) : '';

	// Updated after v1.2.0.
	$wps_upsell_new_bump['wps_upsell_bump_schedule'] = ! empty( $_POST['wps_upsell_bump_schedule'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wps_upsell_bump_schedule'] ) ) : array( '0' );

	$wps_upsell_new_bump['wps_upsell_bump_offer_discount_price'] = ! empty( $_POST['wps_upsell_bump_offer_discount_price'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_offer_discount_price'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_products_in_offer'] = ! empty( $_POST['wps_upsell_bump_products_in_offer'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_products_in_offer'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_offer_price_type'] = ! empty( $_POST['wps_upsell_offer_price_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_offer_price_type'] ) ) : '';

	$wps_upsell_new_bump['wps_ubo_discount_title_for_percent'] = ! empty( $_POST['wps_ubo_discount_title_for_percent'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_ubo_discount_title_for_percent'] ) ) : '';

	$wps_upsell_new_bump['wps_bump_offer_decsription_text'] = ! empty( $_POST['wps_bump_offer_decsription_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_bump_offer_decsription_text'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_offer_description'] = ! empty( $_POST['wps_upsell_bump_offer_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_upsell_bump_offer_description'] ) ) : '';

	$wps_upsell_new_bump['wps_bump_upsell_selected_template'] = ! empty( $_POST['wps_bump_upsell_selected_template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_bump_upsell_selected_template'] ) ) : '';

	$wps_upsell_new_bump['wps_ubo_selected_template'] = ! empty( $_POST['wps_ubo_selected_template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_ubo_selected_template'] ) ) : '';

	// Sanitize and stripe slashes all the arrays.
	$wps_upsell_new_bump['wps_upsell_bump_target_categories'] = ! empty( $_POST['wps_upsell_bump_target_categories'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wps_upsell_bump_target_categories'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_target_ids'] = ! empty( $_POST['wps_upsell_bump_target_ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wps_upsell_bump_target_ids'] ) ) : '';

	// After v1.2.0.
	$wps_upsell_new_bump['wps_ubo_offer_replace_target'] = ! empty( $_POST['wps_ubo_offer_replace_target'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_ubo_offer_replace_target'] ) ) : 'no';

	// After v1.4.2!
	$wps_upsell_new_bump['wps_ubo_offer_exclusive_limit_switch'] = ! empty( $_POST['wps_ubo_offer_exclusive_limit_switch'] ) ? 'yes' : 'no';
	$wps_upsell_new_bump['wps_ubo_offer_exclusive_limit']        = ! empty( $_POST['wps_ubo_offer_exclusive_limit'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['wps_ubo_offer_exclusive_limit'] ) ) ) : '0';
	$wps_upsell_new_bump['wps_ubo_offer_meta_forms']             = ! empty( $_POST['wps_ubo_offer_meta_forms'] ) ? 'yes' : 'no';

	// After v2.0.1!
	$wps_upsell_new_bump['wps_upsell_offer_image']      = ! empty( $_POST['wps_upsell_offer_image'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['wps_upsell_offer_image'] ) ) ) : '';
	$wps_upsell_new_bump['wps_ubo_offer_global_funnel'] = ! empty( $_POST['wps_ubo_offer_global_funnel'] ) ? 'yes' : 'no';
	$wps_upsell_new_bump['wps_upsell_enable_quantity'] = ! empty( $_POST['wps_upsell_enable_quantity'] ) ? 'yes' : 'no';
	$wps_upsell_new_bump['wps_upsell_bump_products_fixed_quantity'] = ! empty( $_POST['wps_upsell_bump_offer_fixed_q'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_offer_fixed_q'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_products_min_quantity'] = ! empty( $_POST['wps_upsell_bump_offer_min_q'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_offer_min_q'] ) ) : '';
	$wps_upsell_new_bump['wps_upsell_bump_products_max_quantity'] = ! empty( $_POST['wps_upsell_bump_offer_max_q'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_offer_max_q'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_offer_quantity_type'] = ! empty( $_POST['wps_upsell_offer_quantity_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_offer_quantity_type'] ) ) : '';
	$wps_upsell_new_bump['wps_upsell_bump_priority'] = ! empty( $_POST['wps_upsell_bump_priority'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_bump_priority'] ) ) : '';

	$wps_upsell_new_bump['wps_upsell_bump_exclude_roles'] = ! empty( $_POST['wps_upsell_bump_exclude_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wps_upsell_bump_exclude_roles'] ) ) : '';

	// When Bump is saved for the first time so load default Design Settings.
	if ( empty( $_POST['parent_border_type'] ) ) {

		$design_settings = wps_ubo_lite_offer_template_1();

		$wps_upsell_new_bump['design_css'] = $design_settings;

		$wps_upsell_new_bump['design_text'] = wps_ubo_lite_offer_default_text();

	} else {    // When design Settings is saved from Post.

		// PARENT WRAPPER DIV CSS( parent_wrapper_div ).
		$design_settings_post['parent_border_type']      = ! empty( $_POST['parent_border_type'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_border_type'] ) ) : '';
		$design_settings_post['parent_border_color']     = ! empty( $_POST['parent_border_color'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_border_color'] ) ) : '';
		$design_settings_post['top_vertical_spacing']    = ! empty( $_POST['top_vertical_spacing'] ) ? sanitize_text_field( wp_unslash( $_POST['top_vertical_spacing'] ) ) : '';
		$design_settings_post['bottom_vertical_spacing'] = ! empty( $_POST['bottom_vertical_spacing'] ) ? sanitize_text_field( wp_unslash( $_POST['bottom_vertical_spacing'] ) ) : '';
		// v2.1.1 version.
		$design_settings_post['parent_background_color']     = ! empty( $_POST['parent_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_background_color'] ) ) : '';

		unset( $_POST['parent_background_color'] );
		unset( $_POST['parent_border_type'] );
		unset( $_POST['parent_border_color'] );
		unset( $_POST['top_vertical_spacing'] );
		unset( $_POST['bottom_vertical_spacing'] );

		// DISCOUNT SECTION( discount_section ).
		$design_settings_post['discount_section_background_color'] = ! empty( $_POST['discount_section_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_section_background_color'] ) ) : '';
		$design_settings_post['discount_section_text_color']       = ! empty( $_POST['discount_section_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_section_text_color'] ) ) : '';
		$design_settings_post['discount_section_text_size']        = ! empty( $_POST['discount_section_text_size'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_section_text_size'] ) ) : '';

		unset( $_POST['discount_section_background_color'] );
		unset( $_POST['discount_section_text_color'] );
		unset( $_POST['discount_section_text_size'] );


		// PRODUCT SECTION(product_section).
		$design_settings_post['product_section_text_color'] = ! empty( $_POST['product_section_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['product_section_text_color'] ) ) : '';
		$design_settings_post['product_section_text_size']  = ! empty( $_POST['product_section_text_size'] ) ? sanitize_text_field( wp_unslash( $_POST['product_section_text_size'] ) ) : '';

		unset( $_POST['product_section_text_color'] );
		unset( $_POST['product_section_text_size'] );

		// Accept Offer Section(primary_section).
		$design_settings_post['primary_section_background_color'] = ! empty( $_POST['primary_section_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['primary_section_background_color'] ) ) : '';
		$design_settings_post['primary_section_text_color']       = ! empty( $_POST['primary_section_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['primary_section_text_color'] ) ) : '';
		$design_settings_post['primary_section_text_size']        = ! empty( $_POST['primary_section_text_size'] ) ? sanitize_text_field( wp_unslash( $_POST['primary_section_text_size'] ) ) : '';

		unset( $_POST['primary_section_background_color'] );
		unset( $_POST['primary_section_text_color'] );
		unset( $_POST['primary_section_text_size'] );

		// SECONDARY SECTION(secondary_section).
		$design_settings_post['secondary_section_background_color'] = ! empty( $_POST['secondary_section_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['secondary_section_background_color'] ) ) : '';
		$design_settings_post['secondary_section_text_color']       = ! empty( $_POST['secondary_section_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['secondary_section_text_color'] ) ) : '';
		$design_settings_post['secondary_section_text_size']        = ! empty( $_POST['secondary_section_text_size'] ) ? sanitize_text_field( wp_unslash( $_POST['secondary_section_text_size'] ) ) : '';

		unset( $_POST['secondary_section_background_color'] );
		unset( $_POST['secondary_section_text_color'] );
		unset( $_POST['secondary_section_text_size'] );

		$wps_upsell_new_bump['design_css'] = $design_settings_post;

		$text_settings_post = array(

			'wps_ubo_discount_title_for_fixed'   => ! empty( $_POST['wps_ubo_discount_title_for_fixed'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_ubo_discount_title_for_fixed'] ) ) : '',

			'wps_ubo_discount_title_for_percent' => ! empty( $_POST['wps_ubo_discount_title_for_percent'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_ubo_discount_title_for_percent'] ) ) : '',

			'wps_bump_offer_decsription_text'    => ! empty( $_POST['wps_bump_offer_decsription_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_bump_offer_decsription_text'] ) ) : '',

			'wps_upsell_offer_title'             => ! empty( $_POST['wps_upsell_offer_title'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_upsell_offer_title'] ) ) : '',

			'wps_upsell_bump_offer_description'  => ! empty( $_POST['wps_upsell_bump_offer_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_upsell_bump_offer_description'] ) ) : '',
		);

		unset( $_POST['wps_ubo_discount_title_for_fixed'] );
		unset( $_POST['wps_ubo_discount_title_for_percent'] );
		unset( $_POST['wps_bump_offer_decsription_text'] );
		unset( $_POST['wps_upsell_offer_title'] );
		unset( $_POST['wps_upsell_bump_offer_description'] );
		$wps_upsell_new_bump['design_text'] = $text_settings_post;
	}

	// Get all bumps.
	$wps_created_upsell_bumps = get_option( 'wps_ubo_bump_list', array() );

	// If Order Bump already exists then save Sales By Bump - Stats if present.
	if ( ! empty( $wps_created_upsell_bumps[ $wps_upsell_bump_id ]['offer_view_count'] ) ) {

		$sales_stats_bump = $wps_created_upsell_bumps[ $wps_upsell_bump_id ];

		// Not Post data, so no need to Sanitize and Strip slashes.

		// Empty for this already checked above.
		$wps_upsell_new_bump['offer_view_count'] = $sales_stats_bump['offer_view_count'];

		$wps_upsell_new_bump['offer_accept_count'] = ! empty( $sales_stats_bump['offer_accept_count'] ) ? $sales_stats_bump['offer_accept_count'] : 0;

		$wps_upsell_new_bump['offer_remove_count'] = ! empty( $sales_stats_bump['offer_remove_count'] ) ? $sales_stats_bump['offer_remove_count'] : 0;

		$wps_upsell_new_bump['bump_success_count'] = ! empty( $sales_stats_bump['bump_success_count'] ) ? $sales_stats_bump['bump_success_count'] : 0;

		$wps_upsell_new_bump['bump_total_sales'] = ! empty( $sales_stats_bump['bump_total_sales'] ) ? $sales_stats_bump['bump_total_sales'] : 0;

		$wps_upsell_new_bump['bump_orders_count'] = ! empty( $sales_stats_bump['bump_orders_count'] ) ? $sales_stats_bump['bump_orders_count'] : array();

	}

	// If Order Bump already exists then save form details if present.
	if ( ! empty( $wps_created_upsell_bumps[ $wps_upsell_bump_id ]['meta_form_fields'] ) ) {

		$form_stats_bump = $wps_created_upsell_bumps[ $wps_upsell_bump_id ];

		// Not Post data, so no need to Sanitize and Strip slashes.
		// Empty for this already checked above.
		$wps_upsell_new_bump['meta_form_fields'] = ! empty( $form_stats_bump['meta_form_fields'] ) ? $form_stats_bump['meta_form_fields'] : array();
	}

	// When Bump is saved for the first time so load default text Settings.
	$wps_upsell_bump_series = array();

	// POST bump as array at bump id key.
	$wps_upsell_bump_series[ $wps_upsell_bump_id ] = $wps_upsell_new_bump;

	// If there are other bumps.
	if ( is_array( $wps_created_upsell_bumps ) && count( $wps_created_upsell_bumps ) ) {

		$flag = 0;

		foreach ( $wps_created_upsell_bumps as $key => $data ) {

			// If bump id key is already present, then replace that key in array.
			if ( (string) $key === (string) $wps_upsell_bump_id ) {

				$wps_created_upsell_bumps[ $key ] = $wps_upsell_bump_series[ $wps_upsell_bump_id ];
				$flag                             = 1;
				break;
			}
		}

		// If Bump id key not present then merge array.
		if ( 1 !== $flag ) {

			// Array merge was reindexing keys so using array union operator.
			$wps_created_upsell_bumps = $wps_created_upsell_bumps + $wps_upsell_bump_series;
		}

		update_option( 'wps_ubo_bump_list', $wps_created_upsell_bumps );

	} else {

		// If there are no other bumps.
		update_option( 'wps_ubo_bump_list', $wps_upsell_bump_series );
	}

	?>

	<!-- Settings saved notice. -->
	<div class="notice notice-success is-dismissible"> 
		<p><strong><?php esc_html_e( 'Settings saved', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></strong></p>
	</div>

	<?php
}

// Get all Bump.
$wps_upsell_bumps_list = get_option( 'wps_ubo_bump_list', array() );
$wps_bump_offer_type   = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_price_type'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_price_type'] : '';

$wps_upsell_bump_schedule_options = array(
	'0' => __( 'Daily', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'1' => __( 'Monday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'2' => __( 'Tuesday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'3' => __( 'Wednesday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'4' => __( 'Thursday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'5' => __( 'Friday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'6' => __( 'Saturday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
	'7' => __( 'Sunday', 'upsell-order-bump-offer-for-woocommerce-pro' ),
);

global $wp_roles;

$all_roles          = $wp_roles->roles;
$all_roles['guest'] = array(
	'name' => esc_html__( 'Guest/Logged Out User', 'upsell-order-bump-offer-for-woocommerce-pro' ),
);
$editable_roles     = apply_filters( 'wps_upsell_order_bump_editable_roles', $all_roles );

?>

<!-- For Single Bump. -->
<form action="" method="POST">
	<div class="wps_upsell_table">
		<table class="form-table wps_upsell_bump_creation_setting" >
			<tbody>

				<!-- Nonce field here. -->
				<?php wp_nonce_field( 'wps_upsell_bump_creation_nonce', 'wps_upsell_bump_nonce' ); ?>

				<input type="hidden" class="wps_upsell_bump_id" name="wps_upsell_bump_id" value="<?php echo esc_html( $wps_upsell_bump_id ); ?>">

				<?php

				$bump_name = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_name'] ) ? sanitize_text_field( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_name'] ) : esc_html__( 'Order Bump', 'upsell-order-bump-offer-for-woocommerce-pro' ) . " #$wps_upsell_bump_id";

				$bump_status = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_status'] ) ? sanitize_text_field( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_status'] ) : 'no';

				// Order bump priority v2.0.1.
				$bump_priority = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_priority'] ) ? sanitize_text_field( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_priority'] ) : '';
				?>

				<!-- Bump Header start.-->
				<div id="wps_upsell_bump_name_heading">
					<h2><?php echo esc_html( $bump_name ); ?></h2>
					<div id="wps_upsell_bump_status" >
						<label>
							<input type="checkbox" id="wps_upsell_bump_status_input" name="wps_upsell_bump_status" value="yes" <?php checked( 'yes', $bump_status ); ?> >
							<span class="wps_upsell_bump_span"></span>
						</label>

						<span class="wps_upsell_bump_status_on <?php echo 'yes' === $bump_status ? 'active' : ''; ?>"><?php esc_html_e( 'Live', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>
						<span class="wps_upsell_bump_status_off <?php echo 'no' === $bump_status ? 'active' : ''; ?>"><?php esc_html_e( 'Sandbox', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>
					</div>
				</div>

				<!-- Bump Name start.-->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_upsell_bump_name"><?php esc_html_e( 'Name of the Order Bump', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php

						$description = esc_html__( 'Provide the name of your Order Bump.', 'upsell-order-bump-offer-for-woocommerce-pro' );

						wps_ubo_lite_help_tip( $description );

						?>

						<input type="text" id="wps_upsell_bump_name" name="wps_upsell_bump_name" value="<?php echo esc_attr( $bump_name ); ?>" class="input-text wps_upsell_bump_commone_class" required="" maxlength="30">
					</td>
				</tr>
				<!-- Bump Name end.-->

				<!-- Bump Priority HTML start.-->
				<tr valign="top">

						<th scope="row" class="titledesc">
						<span class="wps_ubo_premium_strip">Pro</span>
							<label for="wps_upsell_bump_priority"><?php esc_html_e( 'Priority of the Order Bump', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						</th>

						<td class="forminp forminp-text">

							<?php

							$description = esc_html__( 'Priortize Order Bump. Do not use same priority for multiple order bumps, this will override triggered order bump.', 'upsell-order-bump-offer-for-woocommerce-pro' );

							wps_ubo_lite_help_tip( $description );

							?>

							<input type="number" id="wps_upsell_bump_priority" name="wps_upsell_bump_priority" value="<?php echo esc_attr( $bump_priority ); ?>" class="input-text wps_upsell_bump_commone_class" max="100000">
						</td>
				</tr>
				<!-- Bump Priority HTML end.-->

				<!-- Select Target product start. -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_upsell_bump_target_ids_search"><?php esc_html_e( 'Select target product(s)', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php

						$description = esc_html__( 'If any one of these Target Products is checked out then this Order Bump will be triggered and the below offer will be shown.', 'upsell-order-bump-offer-for-woocommerce-pro' );

						wps_ubo_lite_help_tip( $description );

						?>

						<select id="wps_upsell_bump_target_ids_search" class="wc-bump-product-search" multiple="multiple" name="wps_upsell_bump_target_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">

							<?php

							if ( ! empty( $wps_upsell_bumps_list ) ) {

								$wps_upsell_bump_target_products = isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_target_ids'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_target_ids'] : array();

								// Array_map with absint converts negative array values to positive, so that we dont get negative ids.
								$wps_upsell_bump_target_products_ids = ! empty( $wps_upsell_bump_target_products ) ? array_map( 'absint', $wps_upsell_bump_target_products ) : null;

								if ( $wps_upsell_bump_target_products_ids ) {

									foreach ( $wps_upsell_bump_target_products_ids as $wps_upsell_bump_single_target_products_ids ) {

										if ( function_exists( 'wps_ubo_lite_get_title' ) ) {

											$product_name = wps_ubo_lite_get_title( $wps_upsell_bump_single_target_products_ids );

										} else {

											$product_name = ! empty( get_the_title( $wps_upsell_bump_single_target_products_ids ) ) ? get_the_title( $wps_upsell_bump_single_target_products_ids ) : esc_html__( 'Product Not Found', 'upsell-order-bump-offer-for-woocommerce-pro' );
										}

										?>

										<option value="<?php echo esc_html( $wps_upsell_bump_single_target_products_ids ); ?>" selected="selected"><?php echo( esc_html( $product_name ) . '(#' . esc_html( $wps_upsell_bump_single_target_products_ids ) . ')' ); ?></option>';

										<?php
									}
								}
							}

							?>
						</select>		
					</td>	
				</tr>

				<!-- Target category starts. -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_upsell_bump_target_categories_search"><?php esc_html_e( 'Select target categories', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php

						$description = esc_html__( 'If any one of these Target categories is checked out then this Order Bump will be triggered and the below offer will be shown.', 'upsell-order-bump-offer-for-woocommerce-pro' );

						wps_ubo_lite_help_tip( $description );

						?>

						<select id="wps_upsell_bump_target_categories_search" class="wc-bump-product-category-search" multiple="multiple" name="wps_upsell_bump_target_categories[]" data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">

							<?php

							if ( ! empty( $wps_upsell_bumps_list ) ) {

								$wps_upsell_bump_target_categories = isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_target_categories'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_target_categories'] : array();

								// Array_map with absint converts negative array values to positive, so that we dont get negative ids.
								$wps_upsell_bump_target_categories = ! empty( $wps_upsell_bump_target_categories ) ? array_map( 'absint', $wps_upsell_bump_target_categories ) : null;

								if ( $wps_upsell_bump_target_categories ) {

									foreach ( $wps_upsell_bump_target_categories as $single_target_category_id ) {

										if ( function_exists( 'wps_ubo_lite_getcat_title' ) ) {

											$single_category_name = wps_ubo_lite_getcat_title( $single_target_category_id );

										} else {

											$single_category_name = ! empty( get_the_category_by_ID( $single_target_category_id ) ) ? get_the_category_by_ID( $single_target_category_id ) : esc_html__( 'Category Not Found', 'upsell-order-bump-offer-for-woocommerce-pro' );
										}

										?>
										<option value="<?php echo esc_html( $single_target_category_id ); ?>" selected="selected"><?php echo( esc_html( $single_category_name ) . '(#' . esc_html( $single_target_category_id ) . ')' ); ?></option>';
										<?php
									}
								}
							}

							?>
						</select>		
					</td>	
				</tr>
				<!-- Target category ends. -->

				<!-- Exclude roles starts. -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_upsell_bump_exclude_roles"><?php esc_html_e( 'Select roles to exclude', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php

						$description = esc_html__( 'Order Bumps will not be shown to these roles.', 'upsell-order-bump-offer-for-woocommerce-pro' );

						wps_ubo_lite_help_tip( $description );

						?>

						<select id="wps_upsell_bump_exclude_roles" class="wc-bump-exclude-roles-search" multiple="multiple" name="wps_upsell_bump_exclude_roles[]" data-placeholder="<?php esc_attr_e( 'Search for a role&hellip;', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">

							<?php

							if ( ! empty( $editable_roles ) && is_array( $editable_roles ) ) {

								$wps_upsell_bump_exclude_roles = isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_exclude_roles'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_exclude_roles'] : array();

								foreach ( $editable_roles as $key => $value ) {

									?>
									<option <?php echo in_array( (string) $key, (array) $wps_upsell_bump_exclude_roles, true ) ? 'selected' : ''; ?> value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $value['name'] ); ?></option>
									<?php
								}
							}

							?>
						</select>	
					</td>	
				</tr>
				<!-- Exclude roles ends. -->

				<!-- Schedule your Bump start. -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_upsell_bump_schedule_select"><?php esc_html_e( 'Order Bump Schedule', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php

						$description = __( 'Schedule your Order Bump for specific weekdays.', 'upsell-order-bump-offer-for-woocommerce-pro' );

						wps_ubo_lite_help_tip( $description );

						?>

						<?php

						// For earlier versions we will get a string over here.
						if ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_schedule'] ) && ! is_array( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_schedule'] ) ) {

							// Whatever was the selected day, add as an array.
							$wps_ubo_selected_schedule = array( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_schedule'] );

						} else {

							$wps_ubo_selected_schedule = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_schedule'] ) ? ( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_schedule'] ) : array( '0' );
						}

						?>

						<select id="wps_upsell_bump_schedule_select" class="wc-bump-schedule-search wps_upsell_bump_schedule" multiple="multiple" name="wps_upsell_bump_schedule[]" data-placeholder="<?php esc_attr_e( 'Search for a specific days&hellip;', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">

							<?php foreach ( $wps_upsell_bump_schedule_options as $key => $day ) : ?>

								<option <?php echo in_array( (string) $key, $wps_ubo_selected_schedule, true ) ? 'selected' : ''; ?> value="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $day ); ?></option>

							<?php endforeach; ?>

						</select>
					</td>
				</tr>
				<!-- Schedule your Bump end. -->

				<!-- After v1.2.0 -->

				<!-- Replace with target start. -->
				<tr valign="top">

				<!-- Add version compare to dependent plugin -->
				<?php
					$is_update_needed = 'false';
				if ( version_compare( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_VERSION, '1.2.0' ) < 0 ) {

					$is_update_needed = 'true';
				}
				?>

				<input type="hidden" id="is_update_needed" value="<?php echo esc_html( $is_update_needed ); ?>">

					<th scope="row" class="titledesc">

						<span class="wps_ubo_premium_strip"><?php esc_html_e( 'Pro', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>

						<label for="wps_ubo_offer_replace_target"><?php esc_html_e( 'Smart Offer Upgrade', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php
							$attribute_description = esc_html__( 'Replace the existing target product with the offer product as soon as the customer accepts the Order Bump offer.', 'upsell-order-bump-offer-for-woocommerce-pro' );
							wps_ubo_lite_help_tip( $attribute_description );

							$bump_offer_replace_target = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_replace_target'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_replace_target'] : 'no';
						?>

						<label class="wps-upsell-smart-offer-upgrade" for="wps_ubo_offer_replace_target">
						<input class="wps-upsell-smart-offer-upgrade-wrap" type='checkbox' <?php echo wps_ubo_lite_if_pro_exists() && ! empty( $bump_offer_replace_target ) && 'yes' === $bump_offer_replace_target ? 'checked' : ''; ?> id='wps_ubo_offer_replace_target' value='yes' name='wps_ubo_offer_replace_target'>
						<span class="upsell-smart-offer-upgrade-btn"></span>
						</label>

					</td>
				</tr>
				<!-- Replace with target end. -->


				<!-- Order Bump Limit start. -->
				<tr valign="top">
					<th scope="row" class="titledesc">

						<span class="wps_ubo_premium_strip"><?php esc_html_e( 'Pro', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>

						<label for="wps_ubo_offer_exclusive_limit"><?php esc_html_e( 'Exclusive Limits', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<?php $exclusive_limit = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_exclusive_limit'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_exclusive_limit'] : '0'; ?>
					<?php $exclusive_limit_switch = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_exclusive_limit_switch'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_exclusive_limit_switch'] : 'no'; ?>
					<?php $exclusive_limit_used = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['bump_orders_count'] ) ? count( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['bump_orders_count'] ) : '0'; ?>

					<td class="forminp forminp-text">

						<?php
							$attribute_description = esc_html__( 'This feature allows you to limit Order bump for some exclusive sales/order counts.', 'upsell-order-bump-offer-for-woocommerce-pro' );
							wps_ubo_lite_help_tip( $attribute_description );
						?>

						<label class="wps-upsell-smart-offer-upgrade" for="wps_ubo_offer_exclusive_limit">
						<input class="wps-upsell-smart-offer-upgrade-wrap" type='checkbox' id='wps_ubo_offer_exclusive_limit' name='wps_ubo_offer_exclusive_limit_switch' <?php echo wps_ubo_lite_if_pro_exists() && ! empty( $exclusive_limit_switch ) && 'yes' === $exclusive_limit_switch ? 'checked' : ''; ?> >
						<span class="upsell-smart-offer-upgrade-btn"></span>
						</label>

						<div class="wps-ubo-offer-exclusive-limit-wrap <?php echo wps_ubo_lite_if_pro_exists() && ( empty( $exclusive_limit_switch ) || 'no' === $exclusive_limit_switch ) ? 'keep-hidden' : ''; ?>">
							<input placeholder="<?php esc_html_e( 'Enter the limit', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>" class="wps-ubo-offer-exclusive-limit input-text wps_upsell_bump_commone_class" value="<?php echo esc_html( $exclusive_limit ); ?>" name="wps_ubo_offer_exclusive_limit">
							<span class="wps-ubo-offer-exclusive-limit-used"><?php esc_html_e( 'Limit Used : ', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?><?php echo esc_html( $exclusive_limit_used ); ?></span>
						</div>
					</td>
				</tr>
				<!-- Order Bump Limit end. -->

				<!-- V2.0.1 with global funnel start. -->
				<?php $global_switch = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_global_funnel'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_global_funnel'] : 'no'; ?>
				<tr valign="top">
					<th scope="row" class="titledesc">

						<span class="wps_ubo_premium_strip"><?php esc_html_e( 'Pro', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>
						<label for="wps_ubo_offer_global_funnel"><?php esc_html_e( 'Global Order Bump', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php
							$attribute_description = esc_html__( 'This feature allows you to trigger this specific order bump, no matter target product is present or not.', 'upsell-order-bump-offer-for-woocommerce-pro' );
							wps_ubo_lite_help_tip( $attribute_description );
						?>

						<label class="wps-upsell-smart-offer-upgrade" for="wps_ubo_offer_global_funnel">
						<input class="wps-upsell-smart-offer-upgrade-wrap" type='checkbox' <?php echo esc_attr( checked( 'yes', $global_switch ) ); ?>  id='wps_ubo_offer_global_funnel' value="1" name="wps_ubo_offer_global_funnel">
						<span class="upsell-smart-offer-upgrade-btn"></span>
						</label>

					</td>
				</tr>
				<!-- V2.0.1 with global funnel end. -->


				<!-- Meta Forms start. -->
				<?php $meta_forms_switch = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_meta_forms'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_offer_meta_forms'] : 'no'; ?>
				<tr valign="top">
					<th scope="row" class="titledesc">

						<span class="wps_ubo_premium_strip"><?php esc_html_e( 'Pro', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>

						<label for="wps_ubo_offer_meta_forms"><?php esc_html_e( 'Meta Forms', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">

						<?php
							$attribute_description = esc_html__( 'This feature allows you to add a custom form to receive answers before adding Offer product.', 'upsell-order-bump-offer-for-woocommerce-pro' );
							wps_ubo_lite_help_tip( $attribute_description );
						?>

						<label class="wps-upsell-smart-offer-upgrade" for="wps_ubo_offer_meta_forms">
						<input class="wps-upsell-smart-offer-upgrade-wrap" type='checkbox' <?php echo esc_attr( checked( 'yes', $meta_forms_switch ) ); ?> id='wps_ubo_offer_meta_forms' name='wps_ubo_offer_meta_forms'>
						<span class="upsell-smart-offer-upgrade-btn"></span>
						</label>
					</td>
				</tr>
				<!-- Meta Forms end. -->
			</tbody>
		</table>

		<!-- Meta form fields start -->
		<div class="wps-ubo-meta-form__table-wrap">
			<table class="wps-ubo-meta-form__table">
				<thead>
					<tr>
						<th class="wps-ubo-meta-form__tabel-label"><?php esc_html_e( 'Label', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
						<th class="wps-ubo-meta-form__tabel-placeholder"><?php esc_html_e( 'Placeholder', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
						<th class="wps-ubo-meta-form__tabel-desc"><?php esc_html_e( 'Description', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
						<th class="wps-ubo-meta-form__tabel-type"><?php esc_html_e( 'Type', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
						<th class="wps-ubo-meta-form__tabel-type"><?php esc_html_e( 'Options', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
						<th class="wps-ubo-meta-form__tabel-action"><?php esc_html_e( 'Actions', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></th>
					</tr>
					<?php $meta_form_fields = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['meta_form_fields'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['meta_form_fields'] : array(); ?>
					<?php $meta_form_count = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['meta_form_fields'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['meta_form_fields'] : array(); ?>
					<?php $meta_new_row_id = ! empty( $meta_form_fields ) && is_array( $meta_form_fields ) ? array_key_last( $meta_form_fields ) : 0; ?>
					<input type="hidden" id="wps_ubo_meta_new_row_id" value="<?php echo esc_html( $meta_new_row_id ); ?>">
					<?php if ( ! empty( $meta_form_fields ) && is_array( $meta_form_fields ) ) : ?>
						<?php foreach ( $meta_form_fields as $key => $values ) : ?>
						<tr>
							<td><?php echo esc_html( $values['label'] ); ?></td>
							<td><?php echo esc_html( $values['placeholder'] ); ?></td>
							<td><?php echo esc_html( $values['description'] ); ?></td>
							<td><?php echo esc_html( $values['type'] ); ?></td>
							<td><?php echo esc_html( $values['options'] ); ?></td>
							<td>
								<div class="wps-ubo-meta-form__table-icons">
									<span data-row-id="<?php echo esc_html( $key ); ?>" class="wps-ubo-meta-form__table-icon--edit dashicons dashicons-edit"></span>
									<span data-row-id="<?php echo esc_html( $key ); ?>" class="wps-ubo-meta-form__table--icon--delete dashicons dashicons-trash"></span>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="6" class="wps-ubo-meta-form__no_fields"><?php esc_html_e( 'No Fields Added', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></td>
							</tr>
					<?php endif; ?>
				</thead>
			</table>
			<div class="wps-ubo-meta-form__btn-wrap">
				<a href="#" id="wps-ubo-meta-add_new__btn" class="wps-ubo-meta-form__btn"><?php esc_html_e( 'add new field', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
			</div>	
		</div>
		<!-- Meta form fields start -->

		<div class="wps_upsell_bump_offers"><h1><?php esc_html_e( 'Order Bump Offer', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h1>
		</div>

		<?php

		// Offers with discount.
		$wps_upsell_bump_product_in_offer = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_in_offer'] ) ? sanitize_text_field( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_in_offer'] ) : '';

		// Offers with discount.
		$wps_upsell_bump_products_discount = ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ] ) && '' !== $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_offer_discount_price'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_offer_discount_price'] : '20';

		// Offers quantity.
		$wps_upsell_bump_products_fixed_quantity = '';
		$wps_upsell_bump_products_max_quantity   = '';
		$wps_upsell_bump_products_min_quantity   = '';
		if ( isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_fixed_quantity'] ) && isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_max_quantity'] ) && isset( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_min_quantity'] ) ) {
			$wps_upsell_bump_products_fixed_quantity = ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ] ) && '' !== $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_fixed_quantity'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_fixed_quantity'] : '1';
			$wps_upsell_bump_products_max_quantity = ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ] ) && '' !== $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_max_quantity'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_max_quantity'] : '0';
			$wps_upsell_bump_products_min_quantity = ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ] ) && '' !== $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_min_quantity'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_min_quantity'] : '0';
		}

		?>
		<!-- Loader for template generation starts. -->
		<div class="wps_ubo_animation_loader">
			<img src="images/spinner-2x.gif">
		</div>
		<!-- Loader for template generation ends. -->

		<!-- Bump Offers Start.-->
		<div class="new_offers">

			<!-- Single offer html start. -->
			<div class="new_created_offers wps_upsell_single_offer" data-scroll-id="#offer-section-1" >

				<h2 class="wps_upsell_offer_title" >
					<?php esc_html_e( 'Offer Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
				</h2>

				<table>
					<!-- Offer product start. -->
					<tr>

						<th scope="row" class="titledesc">
							<label for="wps_upsell_offer_product_select"><?php esc_html_e( 'Offer Product', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						</th>

						<td class="forminp forminp-text">

							<select class="wc-offer-product-search wps_upsell_offer_product" id="wps_upsell_offer_product_select" name="wps_upsell_bump_products_in_offer" data-placeholder="<?php esc_html_e( 'Search for a product&hellip;', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">

								<?php
								$current_offer_product_id = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_in_offer'] ) ? sanitize_text_field( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_in_offer'] ) : '';

								if ( ! empty( $current_offer_product_id ) ) {

									if ( function_exists( 'wps_ubo_lite_get_title' ) ) {

										$product_title = wps_ubo_lite_get_title( $current_offer_product_id );

									} else {

										$product_title = ! empty( get_the_title( $current_offer_product_id ) ) ? get_the_title( $current_offer_product_id ) : esc_html__( 'Product Not Found', 'upsell-order-bump-offer-for-woocommerce-pro' );
									}

									?>

									<option value="<?php echo esc_html( $current_offer_product_id ); ?>" selected="selected"><?php echo esc_html( $product_title ) . '( #' . esc_html( $current_offer_product_id ) . ' )'; ?>
									</option>

									<?php

								}
								?>
							</select>

							<span class="wps_upsell_offer_description wps_upsell_offer_desc_text"><?php esc_html_e( 'Select the product you want to show as offer.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>
						</td>
					</tr>
					<!-- Offer product end. -->

					<!-- Offer price start. -->
					<tr>
						<th scope="row" class="titledesc">
							<label for="wps_upsell_offer_price_type_id"><?php esc_html_e( 'Offer Price/Discount', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						</th>

						<td class="forminp forminp-text">
							<select name="wps_upsell_offer_price_type" id = 'wps_upsell_offer_price_type_id' >

								<option <?php echo esc_html( '%' === $wps_bump_offer_type ? 'selected' : '' ); ?> value="%"><?php esc_html_e( 'Discount %', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
								<option <?php echo esc_html( 'fixed' === $wps_bump_offer_type ? 'selected' : '' ); ?> value="fixed"><?php esc_html_e( 'Fixed price', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
								<option <?php echo esc_html( 'no_disc' === $wps_bump_offer_type ? 'selected' : '' ); ?> value="no_disc"><?php esc_html_e( 'No Discount', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							</select>
							<input type="number" min="0" step="any" class = "wps_upsell_offer_input_type" class="wps_upsell_offer_price" name="wps_upsell_bump_offer_discount_price" value="<?php echo esc_html( $wps_upsell_bump_products_discount ); ?>">
							<span class="wps_upsell_offer_description"><?php esc_html_e( 'Specify new offer price or discount %', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>

						</td>
					</tr>
					<!-- Offer price end. -->

					<!-- Offer image start. -->
					<tr>
						<th scope="row" class="titledesc">
							<label for="wps_upsell_offer_custom_image"><?php esc_html_e( 'Offer Image', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						</th>

						<td class="forminp forminp-text">
							<?php

								$image_post_id = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_image'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_image'] : '';

								Upsell_Order_Bump_Offer_For_Woocommerce_Admin::wps_ubo_image_uploader_field( $image_post_id );
							?>
						</td>
					</tr>
					<!-- Offer image end. -->

				</table>

			</div>
			<!-- Single offer html end. -->


			<!-- Quantity html start. -->
			<div class="new_created_offers wps_upsell_single_offer" data-scroll-id="#offer-section-1" >

				<h2 class="wps_upsell_offer_title" >
					<?php esc_html_e( 'Offer Quantity Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
				</h2>

				<table>


				<!-- Enable Qunatity start. -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_bump_enable_plugin  "><?php esc_html_e( 'Enable Offer Quantity', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
							$attribute_description = esc_html__( 'Enable quantity field on checkout.', 'upsell-order-bump-offer-for-woocommerce-pro' );

							wps_ubo_lite_help_tip( $attribute_description );
							$wps_upsell_enable_quantity_get = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_enable_quantity'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_enable_quantity'] : 'no';
						?>

						<label for="wps_ubo_enable_switch" class="wps_upsell_bump_enable_plugin_label wps-upsell-smart-offer-upgrade wps_bump_enable_plugin_support">

							<input id="wps_ubo_enable_switch" class="wps_upsell_bump_enable_plugin_input wps-upsell-smart-offer-upgrade-wrap" type="checkbox" <?php echo ( 'yes' === $wps_upsell_enable_quantity_get ) ? "checked='checked'" : ''; ?> name="wps_upsell_enable_quantity" >	
							<span class="wps_upsell_bump_enable_plugin_span upsell-smart-offer-upgrade-btn"></span>

						</label>
					</td>
				</tr>
				<!-- Enable Qunatity end. -->


					<!-- Quantity Fixed/Variable start. -->
					<tr>
						<th scope="row" class="titledesc">
							<label for="wps_upsell_offer_price_type_id"><?php esc_html_e( 'Quantity Fixed/Variable', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						</th>
						<?php
							$wps_upsell_enable_quantity_get = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_quantity_type'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_offer_quantity_type'] : '';
						?>

						<td class="forminp forminp-text">
							<select name="wps_upsell_offer_quantity_type" id='wps_upsell_offer_quantity_type_id' >
								<option <?php echo esc_html( 'fixed_q' === $wps_upsell_enable_quantity_get ? 'selected' : '' ); ?> value="fixed_q"><?php esc_html_e( 'Fixed Quantity', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
								<option <?php echo esc_html( 'variable_q' === $wps_upsell_enable_quantity_get ? 'selected' : '' ); ?> value="variable_q"><?php esc_html_e( 'Variable Quantity', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							</select>
							<input id="fixed_quantity" type="number" min="0" class = "wps_upsell_qty_input_type" class="wps_upsell_offer_price" name="wps_upsell_bump_offer_fixed_q" value="<?php echo esc_html( $wps_upsell_bump_products_fixed_quantity ); ?>">
							<div class="wps_upsell_offer_price__wrap">
								<div class="wps_upsell_offer_price_input-label__wrap">
									<input type="number" min="0" class = "wps_upsell_qty_input_type wps_variable_quantity" class="wps_upsell_offer_price" name="wps_upsell_bump_offer_min_q" value="<?php echo esc_html( $wps_upsell_bump_products_min_quantity ); ?>">
									<label class="wps_variable_quantity" for="wps_upsell_bump_offer_min_q"><?php esc_html_e( 'Minimum Quantity', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
								</div>
								<div class="wps_upsell_offer_price_input-label__wrap">
									<input type="number" min="0" class="wps_upsell_qty_input_type wps_upsell_offer_price wps_variable_quantity" name="wps_upsell_bump_offer_max_q" value="<?php echo esc_html( $wps_upsell_bump_products_max_quantity ); ?>">
									<label class="wps_variable_quantity" for="wps_upsell_bump_offer_max_q"><?php esc_html_e( 'Maximum Quantity', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
								</div>
								<span class="wps_upsell_offer_description"><?php esc_html_e( 'Specify quantity type enter quantity in fixed and min max in variable quantity.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span>
							</div>
						</td>

					</tr>
					<!-- Quantity Fixed/Variable end. -->

				</table>

			</div>
			<!-- Single offer html end. -->

		</div>	

		<!-- Appearance Section starts.	-->
		<?php

		// If Offer product is Saved only then show Appearance section.
		if ( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_upsell_bump_products_in_offer'] ) ) :

			?>

			<div class="wps_upsell_offer_templates"><?php esc_html_e( 'Appearance', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>

			<!-- Nav starts. -->
			<nav class="nav-tab-wrapper wps-ubo-appearance-nav-tab">
				<a class="nav-tab wps-ubo-appearance-template nav-tab-active" href="javascript:void(0);"><?php esc_html_e( 'Template', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
				<a class="nav-tab wps-ubo-appearance-design" href="javascript:void(0);"><?php esc_html_e( 'Design', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
				<a class="nav-tab wps-ubo-appearance-text" href="javascript:void(0);"><?php esc_html_e( 'Content', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
			</nav>
			<!-- Nav ends. -->

			<!-- Appearance Starts. -->		
			<div class="wps_upsell_template_div_wrapper" >
				<!-- Template start -->
				<div class="wps-ubo-template-section" >
					<?php

						$wps_bump_upsell_selected_template = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_bump_upsell_selected_template'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_bump_upsell_selected_template'] : '';

						$wps_ubo_selected_template = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_selected_template'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['wps_ubo_selected_template'] : '1';

					?>

					<!-- Image wrapper -->
					<div id="available_tab" class="wps_ubo_temp_class wps_upsell_template_select-wrapper" >

						<!-- Template one. -->
						<div class="wps_upsell_template_select <?php echo esc_html( 1 === (int) $wps_ubo_selected_template ? 'wps_ubo_selected_class' : '' ); ?>">

							<input type="hidden" class="wps_ubo_template" name="wps_bump_upsell_selected_template" value="<?php echo esc_html( $wps_bump_upsell_selected_template ); ?>" >

							<input type="hidden" class="wps_ubo_selected_template" name="wps_ubo_selected_template" value="<?php echo esc_html( $wps_ubo_selected_template ); ?>">

							<p class="wps_ubo_template_name" ><?php esc_html_e( 'Dazzling Bliss', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
							<a href="javascript:void" class="wps_ubo_template_link" data_link = '1' >
							<?php if ( file_exists( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_DIR_PATH . 'admin/resources/offer-templates/template-1.png' ) ) : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/offer-templates/template-1.png' ); ?>">
								<?php else : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/Offer templates/Template1.png' ); ?>">
							<?php endif; ?>
							</a>
						</div>

						<!-- Template two. -->
						<div class="wps_upsell_template_select <?php echo esc_html( 2 === (int) $wps_ubo_selected_template ? 'wps_ubo_selected_class' : '' ); ?> ">

							<p class="wps_ubo_template_name" ><?php esc_html_e( 'Alluring Lakeside', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
							<a href="javascript:void" class="wps_ubo_template_link" data_link = '2' >
								<?php if ( file_exists( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_DIR_PATH . 'admin/resources/offer-templates/template-1.png' ) ) : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/offer-templates/template-2.png' ); ?>">
								<?php else : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/Offer templates/Template2.png' ); ?>">
							<?php endif; ?>
							</a>
						</div>

						<!-- Template three. -->
						<div class="wps_upsell_template_select <?php echo esc_html( 3 === (int) $wps_ubo_selected_template ? 'wps_ubo_selected_class' : '' ); ?> ">

							<p class="wps_ubo_template_name" ><?php esc_html_e( 'Elegant Summers', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>

							<a href="javascript:void" class="wps_ubo_template_link" data_link = '3' >
								<?php if ( file_exists( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_DIR_PATH . 'admin/resources/offer-templates/template-1.png' ) ) : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/offer-templates/template-3.png' ); ?>">
								<?php else : ?>
								<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/Offer templates/Template3.png' ); ?>">
							<?php endif; ?>
							</a>
						</div>
					</div>
				</div>
				<!-- Template end -->

				<!-- Design start -->
				<div class="wps_upsell_table_column_wrapper wps-ubo-appearance-section-hidden">

					<div class="wps_upsell_table wps_upsell_table--border wps_upsell_custom_template_settings ">

						<div class="wps_upsell_offer_sections"><?php esc_html_e( 'Bump Offer Box', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>
						<table class="form-table wps_upsell_bump_creation_setting">
							<tbody>

								<!-- Populate rest fields with available templates if not custom is checked. -->
								<?php

								if ( ! empty( $wps_bump_upsell_selected_template ) ) {

									// Load the css of selected template.
									$template_callb_func = 'wps_ubo_lite_offer_template_' . $wps_bump_upsell_selected_template;

									$wps_bump_enable_available_design = $template_callb_func();

									$wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css'] = $wps_bump_enable_available_design;
								}

								?>
								<!-- Border style start. -->
								<tr valign="top">

									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Border type', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select among different border types for Bump Offer.', 'upsell-order-bump-offer-for-woocommerce-pro' );

											wps_ubo_lite_help_tip( $attribute_description );

										?>

										<label>

											<!-- Select options for border. -->
											<select name="parent_border_type" class="wps_ubo_preview_select_border_type" >

												<?php

												$border_type_array = array(
													'none' => esc_html__( 'No Border', 'upsell-order-bump-offer-for-woocommerce-pro' ),
													'solid' => esc_html__( 'Solid', 'upsell-order-bump-offer-for-woocommerce-pro' ),
													'dashed' => esc_html__( 'Dashed', 'upsell-order-bump-offer-for-woocommerce-pro' ),
													'double' => esc_html__( 'Double', 'upsell-order-bump-offer-for-woocommerce-pro' ),
													'dotted' => esc_html__( 'Dotted', 'upsell-order-bump-offer-for-woocommerce-pro' ),

												);

												?>
												<option value="" ><?php esc_html_e( '----Select Border Type----', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>

												<?php
												foreach ( $border_type_array as $value => $name ) :
													?>
													<option <?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['parent_border_type'] === $value ? 'selected' : '' ); ?> value="<?php echo esc_html( $value ); ?>" ><?php echo esc_html( $name ); ?></option>
												<?php endforeach; ?>
											</select>

										</label>		
									</td>
								</tr>
							<!-- Border style end. -->

							<!-- Border color start. -->
								<tr valign="top">

									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Border Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
									<?php
										$attribute_description = esc_html__( 'Select border color for Bump Offer.', 'upsell-order-bump-offer-for-woocommerce-pro' );
										wps_ubo_lite_help_tip( $attribute_description );
									?>
										<label>
											<!-- Color picker for description background. -->
											<input type="text" name="parent_border_color" class="wps_ubo_colorpicker wps_ubo_preview_select_border_color" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['parent_border_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['parent_border_color'] ) : ''; ?>">
										</label>			
									</td>

								</tr>
							<!-- Border color end. -->










							<!-- Background color start. -->
								<tr valign="top">

									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Background Color', 'upsell-order-bump-offer-for-woocommerce' ); ?></label>
									</th>

									<td class="forminp forminp-text">
									<?php
										$attribute_description = esc_html__( 'Select Background Color for Bump Offer.', 'upsell-order-bump-offer-for-woocommerce' );
										wps_ubo_lite_help_tip( $attribute_description );
									?>
										<label>
											<!-- Color picker for description background. -->
											<input type="text" name="parent_background_color" class="wps_ubo_colorpicker wps_ubo_preview_select_background_color" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['parent_background_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['parent_background_color'] ) : ''; ?>">
										</label>			
									</td>

								</tr>
							<!-- Background color end. -->










							<!-- Top Vertical Spacing control start. -->
								<tr valign="top">

									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Top Vertical Spacing', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Add top spacing to the Bump Offer Box.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>

										<label>
											<!-- Slider for spacing. -->
											<input type="range" min="10" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['top_vertical_spacing'] ); ?>"  max="40" value="" name='top_vertical_spacing' class="wps_ubo_top_vertical_spacing_slider" />
											<span class="wps_ubo_top_spacing_slider_size" ><?php echo esc_html( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['top_vertical_spacing'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['top_vertical_spacing'] . 'px' ) : '0px' ); ?></span>
										</label>
									</td>
								</tr>
								<!-- Top Vertical Spacing control ends. -->

								<!-- Bottom Vertical Spacing control start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Bottom Vertical Spacing', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
									<?php
										$attribute_description = esc_html__( 'Add bottom spacing to the Bump Offer Box.', 'upsell-order-bump-offer-for-woocommerce-pro' );
										wps_ubo_lite_help_tip( $attribute_description );
									?>
									<label>	
										<!-- Slider for spacing. -->
										<input type="range" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['bottom_vertical_spacing'] ); ?>" min="10" max="40" value="" name='bottom_vertical_spacing' class="wps_ubo_bottom_vertical_spacing_slider" />
										<span class="wps_ubo_bottom_spacing_slider_size" > 
										<?php echo esc_html( ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['bottom_vertical_spacing'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['bottom_vertical_spacing'] . 'px' ) : '0px' ); ?>
											</span>
										</label>		
									</td>
								</tr>
								<!-- Bottom Vertical Spacing control ends. -->

							</tbody>
						</table>
					</div>
					<!-- Global wrapper section. -->

					<!-- Discount_section section. -->
					<div class="wps_upsell_table wps_upsell_table--border wps_upsell_custom_template_settings ">
						<div class="wps_upsell_offer_sections"><?php esc_html_e( 'Discount Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>
						<table class="form-table wps_upsell_bump_creation_setting">
							<tbody>
								<!-- Background color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Background Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
									<?php
										$attribute_description = esc_html__( 'Select background color for Discount section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
										wps_ubo_lite_help_tip( $attribute_description );
									?>
										<label>
											<!-- Color picker for description background. -->
											<input type="text" name="discount_section_background_color" class="wps_ubo_colorpicker wps_ubo_select_discount_bcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_background_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_background_color'] ) : ''; ?>">

										</label>	
									</td>
								</tr>
								<!-- Background color end. -->

								<!-- Text color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select text color for Discount section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Color picker for description text. -->
											<input type="text" name="discount_section_text_color" class="wps_ubo_colorpicker wps_ubo_select_discount_tcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_text_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_text_color'] ) : ''; ?>">
										</label>			
									</td>

								</tr>
								<!-- Text color end. -->

								<!-- Text size control start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Size', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select font size for Discount section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Slider for spacing. -->
											<input type="range" min="20" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_text_size'] ); ?>"  max="50" value="" name = 'discount_section_text_size' class="wps_ubo_text_slider wps_ubo_discount_slider" />

											<span class="wps_ubo_slider_size wps_ubo_discount_slider_size" ><?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['discount_section_text_size'] . 'px' ); ?></span>
										</label>		
									</td>
								</tr>
								<!-- Text size control ends. -->
							</tbody>
						</table>
					</div>
					<!-- Discount_section section. -->

					<!-- Product_section section -->
					<div class="wps_upsell_table wps_upsell_table--border wps_upsell_custom_template_settings ">
						<div class="wps_upsell_offer_sections"><?php esc_html_e( 'Product Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>
						<table class="form-table wps_upsell_bump_creation_setting">
							<tbody>

								<!-- Text color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select text color for Product section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Color picker for description text. -->
											<input type="text" name="product_section_text_color" class="wps_ubo_colorpicker wps_ubo_select_product_tcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['product_section_text_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['product_section_text_color'] ) : ''; ?>">
										</label>			
									</td>
								</tr>
								<!-- Text color end. -->

								<!-- Text size control start. -->
								<tr valign="top">

									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Size', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select font size for Product section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>

										<label>

											<!-- Slider for spacing. -->
											<input type="range" min="10" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['product_section_text_size'] ); ?>"  max="30" value="" name = 'product_section_text_size' class="wps_ubo_text_slider wps_ubo_product_slider" />

											<span class="wps_ubo_slider_size wps_ubo_product_slider_size" ><?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['product_section_text_size'] . 'px' ); ?> </span>
										</label>		
									</td>

								</tr>
								<!-- Text size control ends. -->
							</tbody>
						</table>
					</div>
					<!-- Product_section section. -->

					<!-- Primary_section section. -->
					<div class="wps_upsell_table wps_upsell_table--border wps_upsell_custom_template_settings ">
						<div class="wps_upsell_offer_sections"><?php esc_html_e( 'Accept Offer Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>

						<table class="form-table wps_upsell_bump_creation_setting">
							<tbody>
								<!-- Background color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Background Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select background color for Accept Offer section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Color picker for description background. -->
											<input type="text" name="primary_section_background_color" class="wps_ubo_colorpicker wps_ubo_select_accept_offer_bcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_background_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_background_color'] ) : ''; ?>">
										</label>			
									</td>
								</tr>
								<!-- Background color end. -->

								<!-- Text color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>

									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select text color for Accept Offer section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>	
											<!-- Color picker for description text. -->
											<input type="text" name="primary_section_text_color" class="wps_ubo_colorpicker wps_ubo_select_accept_offer_tcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_text_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_text_color'] ) : ''; ?>">
										</label>			
									</td>
								</tr>
								<!-- Text color end. -->

								<!-- Text size control start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Size', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select font size for Accept Offer section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Slider for spacing. -->
											<input type="range" min="10" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_text_size'] ); ?>"  max="30" value="" name = 'primary_section_text_size' class="wps_ubo_text_slider wps_ubo_accept_offer_slider" />
											<span class="wps_ubo_slider_size wps_ubo_accept_offer_slider_size" ><?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['primary_section_text_size'] . 'px' ); ?></span>
										</label>	
									</td>
								</tr>
								<!-- Text size control ends. -->
							</tbody>
						</table>
					</div>
					<!-- Primary_section section. -->

					<!-- Secondary_section section. -->
					<div class="wps_upsell_table wps_upsell_table--border wps_upsell_custom_template_settings ">
						<div class="wps_upsell_offer_sections"><?php esc_html_e( 'Offer Description Section', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></div>
						<table class="form-table wps_upsell_bump_creation_setting">
							<tbody>
								<!-- Background color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Background Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select background color for Offer Description section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<label>
											<!-- Color picker for description background. -->
											<input type="text" name="secondary_section_background_color" class="wps_ubo_colorpicker wps_ubo_select_offer_description_bcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_background_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_background_color'] ) : ''; ?>">
										</label>			
									</td>
								</tr>
								<!-- Background color end. -->

								<!-- Text color start. -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Color', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select text color for Offer Description section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<!-- Color picker for description text. -->
										<input type="text" name="secondary_section_text_color" class="wps_ubo_colorpicker wps_ubo_select_offer_description_tcolor" value="<?php echo ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_text_color'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_text_color'] ) : ''; ?>">
									</td>
								</tr>
								<!-- Text color end. -->

								<!-- Text size control start -->
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label><?php esc_html_e( 'Select Text Size', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
									</th>
									<td class="forminp forminp-text">
										<?php
											$attribute_description = esc_html__( 'Select font size for Offer Description section.', 'upsell-order-bump-offer-for-woocommerce-pro' );
											wps_ubo_lite_help_tip( $attribute_description );
										?>
										<!-- Slider for spacing. -->
										<input type="range" min="10" value="<?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_text_size'] ); ?>"  max="30" value="" name = 'secondary_section_text_size' class="wps_ubo_text_slider wps_ubo_offer_description_slider" />

										<span class="wps_ubo_slider_size wps_ubo_offer_description_slider_size" ><?php echo esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_css']['secondary_section_text_size'] . 'px' ); ?></span>
									</td>
								</tr>
								<!-- Text size control ends. -->
							</tbody>
						</table>
					</div>
					<!-- Secondary_section section ends. -->
				</div>
				<!-- Design end -->

				<!-- Text start -->
				<div class="wps-ubo-text-section wps_upsell_table--border wps-ubo-appearance-section-hidden wps_upsell_table" >
					<table>
						<!-- Discount Title start. -->
						<tr valign="top">
							<th scope="row" class="titledesc">
								<p class='wps_ubo_row_heads' ><?php esc_html_e( 'Discount Title', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
							</th>

							<td class="forminp forminp-text">
								<?php
									$attribute_description = sprintf(
										'%s<br>%s %s<br>%s %s',
										esc_html__( 'Discount title content. Please use at respective places :', 'upsell-order-bump-offer-for-woocommerce-pro' ),
										'&rarr; {dc_%}',
										esc_html__( 'for % discount.', 'upsell-order-bump-offer-for-woocommerce-pro' ),
										'&rarr; {dc_price}',
										esc_html__( 'for fixed discount price.', 'upsell-order-bump-offer-for-woocommerce-pro' )
									);

									wps_ubo_lite_help_tip( $attribute_description );

									$wps_ubo_discount_title_for_fixed = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_ubo_discount_title_for_fixed'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_ubo_discount_title_for_fixed'] : '';

									$wps_ubo_discount_title_for_percent = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_ubo_discount_title_for_percent'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_ubo_discount_title_for_percent'] : '';

								?>
								<div class="d-inline-block">
									<p><?php esc_html_e( 'For Discount %', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>

									<input class="wps_upsell_offer_input_type" type="text" text_id ="percent" name="wps_ubo_discount_title_for_percent" value="<?php echo esc_attr( $wps_ubo_discount_title_for_percent ); ?>">

									<p>
										<?php esc_html_e( 'For Fixed Price', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
									</p>

									<input class="wps_upsell_offer_input_type" type="text" name="wps_ubo_discount_title_for_fixed" text_id ="fixed" value="<?php echo esc_html( $wps_ubo_discount_title_for_fixed ); ?>">			
								</div>				
							</td>
						</tr>
						<!--Discount Title end. -->

						<!-- Product Description start. -->
						<tr valign="top">
							<th scope="row" class="titledesc">
								<p class='wps_ubo_row_heads' ><?php esc_html_e( 'Product Description', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
								</p>
							</th>

							<td class="forminp forminp-text" colspan="2" >

								<?php
									$attribute_description = esc_html__( 'Bump Offer Product description content.', 'upsell-order-bump-offer-for-woocommerce-pro' );

									wps_ubo_lite_help_tip( $attribute_description );

									$wps_bump_offer_decsription_text = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_bump_offer_decsription_text'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_bump_offer_decsription_text'] : '';
								?>

								<textarea class="wps_textarea_class" text_id ="pro_desc" rows="4" cols="50" name="wps_bump_offer_decsription_text" ><?php echo esc_html( $wps_bump_offer_decsription_text ); ?></textarea>

							</td>
						</tr>
						<!-- Product Description end. -->

						<!-- Lead Title start. -->
						<tr valign="top">
							<th scope="row" class="titledesc">
								<p class='wps_ubo_row_heads' ><?php esc_html_e( ' Lead Title ', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
							</th>

							<td class="forminp forminp-text">
								<?php
									$attribute_description = esc_html__( 'Bump offer Lead title content.', 'upsell-order-bump-offer-for-woocommerce-pro' );
									wps_ubo_lite_help_tip( $attribute_description );
								?>

								<?php

									$offer_lead_title = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_upsell_offer_title'] ) ? $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_upsell_offer_title'] : '';

								?>

								<input type="text" class="wps_upsell_offer_input_type" name="wps_upsell_offer_title" text_id ="lead" value = "<?php echo esc_html( $offer_lead_title ); ?>">

							</td>
						</tr>
						<!--Lead Title ends.-->

						<!-- Offer Description start. -->
						<tr valign="top">
							<th scope="row" class="titledesc">
								<p class='wps_ubo_row_heads' ><?php esc_html_e( 'Offer Description ', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
							</th>

							<td class="forminp forminp-text" colspan="2" >
								<?php
								$attribute_description = esc_html__( 'Bump Offer description content.', 'upsell-order-bump-offer-for-woocommerce-pro' );
								wps_ubo_lite_help_tip( $attribute_description );

								$wps_upsell_bump_offer_description = ! empty( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_upsell_bump_offer_description'] ) ? esc_html( $wps_upsell_bumps_list[ $wps_upsell_bump_id ]['design_text']['wps_upsell_bump_offer_description'] ) : '';
								?>
								<textarea class="wps_textarea_class"  name="wps_upsell_bump_offer_description" text_id ="off_desc" rows="5" cols="50" ><?php echo esc_html( $wps_upsell_bump_offer_description ); ?></textarea>
							</td>
						</tr>
						<!-- Offer Description end. -->
					</table>
				</div>
				<!-- Text end -->

				<!-- Preview start -->
				<div class="wps_ubo_bump_offer_preview" >

					<?php

					// Send current Bump Offer id.
					$bump = wps_ubo_lite_fetch_bump_offer_details( $wps_upsell_bump_id, '' );

					$bumphtml = wps_ubo_lite_bump_offer_html( $bump );

					?>
					<h3 class="wps_ubo_offer_preview_heading"><?php esc_html_e( 'Offer Preview', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h3>

					<!-- Generate a live preview. -->
					<?php
						$allowed_html = wps_ubo_lite_allowed_html();
						echo wp_kses( $bumphtml, $allowed_html );
					?>
				</div>
				<!-- Preview end -->

			</div>
			<!-- Appearance Ends. -->

			<?php
		endif;

		?>

		<!-- Save Changes for whole Bump. -->
		<p class="submit">
			<input type="submit" value="<?php esc_html_e( 'Save Changes', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>" class="button-primary woocommerce-save-button" name="wps_upsell_bump_creation_setting_save" id="wps_upsell_bump_creation_setting_save" >
		</p>
	</div>
</form>

<!-- Skin Change Popup -->
<div class="wps_ubo_skin_popup_wrapper">
	<div class="wps_ubo_skin_popup_inner">
		<!-- Popup icon -->
		<div class="wps_ubo_skin_popup_head">
			<?php if ( file_exists( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_DIR_PATH . 'admin/resources/icons/warning.png' ) ) : ?>
				<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/icons/warning.png' ); ?>">
				<?php else : ?>
				<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/Icons/warning.png' ); ?>">
			<?php endif; ?>
		</div>
		<!-- Popup body. -->
		<div class="wps_ubo_skin_popup_content">
			<div class="wps_ubo_skin_popup_ques">
				<h5><?php esc_html_e( 'Do you really want to change template layout ?', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h5>
				<p><?php esc_html_e( 'Changing layout will reset Design settings back to default.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
			</div>
			<div class="wps_ubo_skin_popup_option">
				<!-- Yes button. -->
				<a href="javascript:void(0);" class="wps_ubo_template_layout_yes"><?php esc_html_e( 'Yes', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
				<!-- No button. -->
				<a href="javascript:void(0);" class="wps_ubo_template_layout_no"><?php esc_html_e( 'No', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
			</div>
		</div>
	</div>
</div>

<!-- Update required Popup -->
<div class="wps_ubo_update_popup_wrapper">
	<div class="wps_ubo_update_popup_inner">
		<!-- Popup icon -->
		<div class="wps_ubo_update_popup_head">
			<?php if ( file_exists( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_DIR_PATH . 'admin/resources/icons/warning.png' ) ) : ?>
				<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/icons/warning.png' ); ?>">
				<?php else : ?>
				<img src="<?php echo esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL . 'admin/resources/Icons/warning.png' ); ?>">
			<?php endif; ?>
		</div>
		<!-- Popup body. -->
		<div class="wps_ubo_update_popup_content">
			<div class="wps_ubo_update_popup_ques">
				<h5><?php esc_html_e( 'Update Required!', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h5>
				<p><?php esc_html_e( "Please Update 'Upsell Order Bump Offer for WooCommerce' to use this feature.", 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
			</div>
			<div class="wps_ubo_update_popup_option">

				<!-- Update Button button. -->
				<a target="_blank" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=Upsell+Order+Bump+Offer+for+WooCommerce+upselling+plugin&tab=search&type=term' ) ); ?>" class="wps_ubo_update_yes"><?php esc_html_e( 'Update Now', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
				<a href="javascript:void(0);" class="wps_ubo_update_no"><?php esc_html_e( "Don't update", 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
			</div>
		</div>
	</div>
</div>

<!-- Add/edit field modal -->
<div class="wps-ubo-meta-form__modal">
	<div class="wps-ubo-meta-form__modal-wrap">
		<div class="wps-ubo-meta-form__modal-head">
			<h2 class="wps-ubo-meta-form__modal-title"><?php esc_html_e( 'Custom Field Details', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h2>
			<span class="wps-ubo-meta-form__modal-close--wrap">
				<a href="#" class="wps-ubo-meta-form__modal-close"><?php esc_html_e( 'x', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h2></a>
			</span>
		</div>
		<div class="wps-ubo-meta-form__modal-body">
			<form action="#" method="POST" class="wps-ubo-meta-form">
				<div class="wps-form-grp">
					<div class="wps-form__label">
						<label><?php esc_html_e( 'Label', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h2></label>
					</div>
					<div class="wps-form__field">
						<input type="text" required="required" name="wps_add_new_field_label" class="wps_add_new_field_label">
					</div>
				</div>
				<div class="wps-form-grp">
					<div class="wps-form__label">
						<label><?php esc_html_e( 'Placeholder', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</div>
					<div class="wps-form__field">
						<input type="text" name="wps_add_new_field_placeholder" class="wps_add_new_field_placeholder">
					</div>
				</div>
				<div class="wps-form-grp">
					<div class="wps-form__label">
						<label><?php esc_html_e( 'Description', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</div>
					<div class="wps-form__field">
						<textarea rows="2" required="required" name="wps_add_new_field_description" class="wps_add_new_field_description"></textarea>
					</div>
				</div>
				<div class="wps-form-grp">
					<div class="wps-form__label">
						<label><?php esc_html_e( 'Type', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
					</div>
					<div class="wps-form__field">
						<select required="required" name="wps_add_new_field_type" class="wps_add_new_field_type">
							<option value="Text"><?php esc_html_e( 'Text', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							<option value="Checkbox"><?php esc_html_e( 'Checkbox', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							<option value="Select"><?php esc_html_e( 'Select', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							<option value="Number"><?php esc_html_e( 'Number', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							<option value="Date"><?php esc_html_e( 'Date', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
							<option value="Month"><?php esc_html_e( 'Month', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></option>
						</select>
					</div>
				</div>
				<div class="wps-form-grp">
					<div class="wps-form__label">
						<label><?php esc_html_e( 'Options', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label>
						<?php
							$attribute_description = esc_html__( 'For Select Field only. Add options like : Option 1 | Option 2 | Option 3', 'upsell-order-bump-offer-for-woocommerce-pro' );
							wps_ubo_lite_help_tip( $attribute_description );
						?>
					</div>
					<div class="wps-form__field">
						<input type="text" name="wps_add_new_field_options" class="wps_add_new_field_options">
					</div>
				</div>
				<div class="wps-form-grp">
					<div class="wps-form__submit-btn--wrap">
						<input type="submit" class="wps-form__submit-btn" value="<?php esc_html_e( 'Submit', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
