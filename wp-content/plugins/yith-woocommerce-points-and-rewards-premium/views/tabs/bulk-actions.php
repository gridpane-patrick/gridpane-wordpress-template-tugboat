<?php
/**
 * Bulk Action tab view
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$fields = array(
	'actions'                                => array(
		'id'      => 'ywpar_bulk_action_type',
		'name'    => 'ywpar_bulk_action_type',
		'title'   => esc_html__( 'Action', 'yith-woocommerce-points-and-rewards' ),
		'type'    => 'radio',
		'options' => array(
			'reset'                    => esc_html__( 'Reset points', 'yith-woocommerce-points-and-rewards' ),
			'add_points_to_orders'     => esc_html__( 'Add points to previous orders', 'yith-woocommerce-points-and-rewards' ),
			'add_points'               => esc_html__( 'Add points to users', 'yith-woocommerce-points-and-rewards' ),
			'remove_points'            => esc_html__( 'Remove points to users', 'yith-woocommerce-points-and-rewards' ),
			'ban'                      => esc_html__( 'Ban Users', 'yith-woocommerce-points-and-rewards' ),
			'unban'                    => esc_html__( 'Unban Users', 'yith-woocommerce-points-and-rewards' ),
			'recalculate_total_points' => esc_html__( 'Recalculate total points', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'   => 'reset',
		'desc'    => esc_html__( 'Choose the action you want to execute', 'yith-woocommerce-points-and-rewards' ),
	),

	'points_of'                              => array(
		'id'                => 'ywpar_bulk_apply_to',
		'name'              => 'ywpar_bulk_apply_to',
		'title'             => esc_html__( 'Apply action to', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose to which users apply this action', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'radio',
		'options'           => array(
			'everyone'       => esc_html__( 'All users', 'yith-woocommerce-points-and-rewards' ),
			'role_list'      => esc_html__( 'Only specified user roles', 'yith-woocommerce-points-and-rewards' ),
			'customers_list' => esc_html__( 'Only specified users', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'             => 'everyone',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type',
			'data-deps_value' => 'reset|add_points|ban|remove_points|unban|recalculate_total_points',
		),
	),

	'specific_user_roles'                    => array(
		'id'                => 'ywpar_user_role',
		'name'              => 'ywpar_user_role',
		'title'             => esc_html__( 'Choose which roles', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which user roles to apply the action', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'style'             => 'width:500px',
		'multiple'          => true,
		'options'           => yith_ywpar_get_roles(),
		'default'           => array( 'all' ),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type,ywpar_bulk_apply_to',
			'data-deps_value' => 'reset|add_points|ban|unban|remove_points|recalculate_total_points,role_list',
		),
	),

	'specific_users'                         => array(
		'id'                => 'ywpar_customer_list',
		'name'              => 'ywpar_customer_list',
		'type'              => 'ajax-customers',
		'title'             => esc_html__( 'Choose which users', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which users to apply the action', 'yith-woocommerce-points-and-rewards' ),
		'multiple'          => true,
		'allow_clear'       => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type,ywpar_bulk_apply_to',
			'data-deps_value' => 'reset|add_points|ban|remove_points|unban|recalculate_total_points,customers_list',
		),
	),

	/* exclusion fields */
	'active_exclusion'                       => array(
		'id'                => 'ywpar_active_exclusion',
		'name'              => 'ywpar_active_exclusion',
		'title'             => esc_html__( 'Exclude users', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable if you want to exclude specific users or user roles', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'onoff',
		'value'             => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type,ywpar_bulk_apply_to',
			'data-deps_value' => 'reset|add_points|ban|remove_points|unban|recalculate_total_points,everyone',
		),
	),

	'exclude_users_type'                     => array(
		'id'                => 'ywpar_exclude_users_type',
		'name'              => 'ywpar_exclude_users_type',
		'title'             => esc_html__( 'Don\'t apply action to', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set which users to apply the selected action', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'radio',
		'options'           => array(
			'by_user' => esc_html__( 'Specified users', 'yith-woocommerce-points-and-rewards' ),
			'by_role' => esc_html__( 'Specified user roles', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'             => 'by_user',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_active_exclusion,ywpar_bulk_action_type,ywpar_bulk_apply_to',
			'data-deps_value' => 'yes,reset|add_points|ban|remove_points|recalculate_total_points|unban,everyone',
		),
	),

	'select_user_roles_to_exclude'           => array(
		'id'                => 'ywpar_user_role_excluded',
		'name'              => 'ywpar_user_role_excluded',
		'title'             => esc_html__( 'Choose which roles to exclude', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which user roles to exclude from this bulk action', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'style'             => 'width:500px',
		'multiple'          => true,
		'options'           => yith_ywpar_get_roles(),
		'default'           => array(),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_active_exclusion,ywpar_bulk_action_type,ywpar_bulk_apply_to,ywpar_exclude_users_type',
			'data-deps_value' => 'yes,reset|add_points|ban|remove_points|recalculate_total_points|unban,everyone,by_role',
		),

	),

	'select_users_to_exclude'                => array(
		'id'                => 'ywpar_customer_list_exclude',
		'name'              => 'ywpar_customer_list_exclude',
		'type'              => 'ajax-customers',
		'title'             => esc_html__( 'Choose which users to exclude', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which users to exclude from this bulk action', 'yith-woocommerce-points-and-rewards' ),
		'multiple'          => true,
		'allow_clear'       => true,
		'default'           => array(),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_active_exclusion,ywpar_bulk_action_type,ywpar_bulk_apply_to,ywpar_exclude_users_type',
			'data-deps_value' => 'yes,reset|add_points|ban|remove_points|unban|recalculate_total_points,everyone,by_user',
		),

	),

	/* add points quantity field */
	'add_points_quantity_field'              => array(
		'title'             => esc_html__( 'Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set how many points.', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'number',
		'value'             => 0,
		'data-deps'         => 'add_points,remove_points',
		'id'                => 'ywpar_bulk_add_points',
		'name'              => 'ywpar_bulk_add_points',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type',
			'data-deps_value' => 'add_points|remove_points',
		),
	),

	/* add points description field */
	'add_points_description'                 => array(
		'title'             => esc_html__( 'Description', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enter a description to explain to your users the reason for the points action', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'text',
		'value'             => '',
		'id'                => 'ywpar_bulk_add_description',
		'name'              => 'ywpar_bulk_add_description',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type',
			'data-deps_value' => 'remove_points|add_points',
		),
	),

	/* apply points to previous order date field */
	'apply_points_previous_order_type'       => array(
		'title'             => esc_html__( 'Add points to', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose to assign points to all previous orders or only orders placed from a specific date. All orders from this date will be checked and points will be added to the customer\'s profile.', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'radio',
		'options'           => array(
			'all'  => esc_html__( 'All previous orders', 'yith-woocommerce-points-and-rewards' ),
			'from' => esc_html__( 'Orders placed from a specific date', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'             => 'all',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type',
			'data-deps_value' => 'add_points_to_orders',
		),
		'id'                => 'ywpar_apply_points_previous_order_to',
		'name'              => 'ywpar_apply_points_previous_order_to',
	),

	'apply_points_previous_order_start_date' => array(
		'title'             => esc_html__( 'Add points to orders placed from', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose from which date to assign points to previous orders', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'datepicker',
		'data'              => array(
			'date-format' => 'yy-mm-dd',
		),
		'style'             => 'max-width: 200px;',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_bulk_action_type,ywpar_apply_points_previous_order_to',
			'data-deps_value' => 'add_points_to_orders,from',
		),
		'id'                => 'ywpar_apply_points_previous_order_start_date',
		'name'              => 'ywpar_apply_points_previous_order_start_date',

	),

);
?>

<div id="yith_woocommerce_points_and_rewards_bulk" class="yith-plugin-fw-wp-page-wrapper yith-plugin-fw  yit-admin-panel-container">
	<div class="yit-admin-panel-content-wrap">
		<?php
		if ( isset( $_GET['action'] ) && isset( $link ) ) : //phpcs:ignore
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			class="add-new-h2"><?php esc_html_e( '< back to list', 'yith-woocommerce-points-and-rewards' ); ?></a><?php endif ?>
		<h2><?php esc_html_e( 'Bulk Actions', 'yith-woocommerce-points-and-rewards' ); ?></h2>

		<div id="yith_woocommerce_points_and_rewards_bulk-container" class="yit_options rm_option rm_input rm_text">
			<form id="yith_woocommerce_points_and_rewards_bulk_form" class="yith-dev-handle" method="post">
				<?php wp_nonce_field( 'ywpar_bulk_actions', 'security' ); ?>
				<table class="form-table">
					<tbody>
					<?php
					foreach ( $fields as $field ) :
						$default_field   = array(
							'id'    => '',
							'title' => isset( $field['name'] ) ? $field['name'] : '',
							'desc'  => '',
						);
						$field           = wp_parse_args( $field, $default_field );
						$extra_row_class = isset( $field['extra_row_class'] ) ? $field['extra_row_class'] : '';

						$display_row = ! in_array( $field['type'], array( 'hidden', 'html', 'sep', 'simple-text', 'title', 'list-table' ), true );
						$display_row = isset( $field['yith-display-row'] ) ? ! ! $field['yith-display-row'] : $display_row;
						$is_required = ! empty( $field['required'] );

						$extra_row_classes = $is_required ? array( 'yith-plugin-fw--required' ) : array();
						$extra_row_classes = (array) apply_filters( 'yith_plugin_fw_panel_wc_extra_row_classes', $extra_row_classes, $field );

						$row_classes = array( 'yith-plugin-fw-panel-wc-row', $field['type'] );
						$row_classes = array_merge( $row_classes, $extra_row_classes, array( $extra_row_class ) );
						$row_classes = implode( ' ', $row_classes );

						$field['custom_attributes'] = ywpar_get_custom_attributes_of_custom_field( $field );
						?>
						<tr class="<?php echo esc_attr( $row_classes ); ?>" <?php echo wp_kses_post( yith_field_deps_data( $field ) ); ?> <?php echo wp_kses_post( $field['custom_attributes'] ); ?> >
							<?php if ( $display_row ) : ?>
								<th scope="row" class="titledesc">
									<label
										for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
								</th>
								<td class="forminp forminp-<?php echo esc_attr( $field['type'] ); ?>">
									<?php yith_plugin_fw_get_field( $field, true ); ?>
									<?php echo '<span class="description">' . wp_kses_post( $field['desc'] ) . '</span>'; ?>
								</td>
							<?php else : ?>
								<td colspan="2">
									<?php yith_plugin_fw_get_field( $field, true ); ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<div class="inner-option ywpar-bulk-trigger">
					<input type="hidden" class="ywpar_safe_submit_field" name="ywpar_safe_submit_field" value=""
						data-std="">
					<button class="button button-primary"
						id="ywpar_bulk_action_points"><?php esc_html_e( 'Apply Action', 'yith-woocommerce-points-and-rewards' ); ?></button>
				</div>
				<div class="clear"></div>
			</form>
		</div>
	</div>
</div>
