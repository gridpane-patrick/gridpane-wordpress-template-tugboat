<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 *
 * Text Plugin Admin View
 *
 * @package    YITH
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$fields = array(
	'actions'           => array(
		'id'      => 'type_action',
		'name'    => 'type_action',
		'title'   => esc_html_x( 'Action to execute', 'Import export panel option', 'yith-woocommerce-points-and-rewards' ),
		'type'    => 'radio',
		'options' => array(
			'import' => esc_html__( 'Import points from a CSV file', 'yith-woocommerce-points-and-rewards' ),
			'export' => esc_html__( 'Export points into a CSV file', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'   => 'import',
		'desc'    => esc_html__( 'Choose if you want to import or export', 'yith-woocommerce-points-and-rewards' ),
	),

	'csv_format'        => array(
		'id'      => 'csv_format',
		'name'    => 'csv_format',
		'title'   => esc_html__( 'Choose the CSV format', 'yith-woocommerce-points-and-rewards' ),
		'type'    => 'radio',
		'options' => array(
			'id'    => esc_html__( 'User ID / Points', 'yith-woocommerce-points-and-rewards' ),
			'email' => esc_html__( 'User email / Points', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'   => 'id',
		'desc'    => esc_html__( 'Choose if you want to import or export', 'yith-woocommerce-points-and-rewards' ),
	),

	'csv_import_action' => array(
		'id'      => 'csv_import_action',
		'name'    => 'csv_import_action',
		'title'   => esc_html__( 'CSV import action', 'yith-woocommerce-points-and-rewards' ),
		'type'    => 'radio',
		'options' => array(
			'add'    => esc_html__( 'Add points to current balance', 'yith-woocommerce-points-and-rewards' ),
			'remove' => esc_html__( 'Override points', 'yith-woocommerce-points-and-rewards' ),
		),
		'value'   => 'add',
		'desc'    => esc_html__( 'Choose if imported points will be added or will override the current balance.', 'yith-woocommerce-points-and-rewards' ),
	),

	'import_delimiter'  => array(
		'id'    => 'delimiter',
		'name'  => 'delimiter',
		'title' => esc_html__( 'Delimiter Type', 'yith-woocommerce-points-and-rewards' ),
		'type'  => 'text',
		'value' => ',',
		'desc'  => esc_html__( 'Enter the delimiter type. You can use for example , / ;', 'yith-woocommerce-points-and-rewards' ),
	),
);

$fields = apply_filters( 'ywpar_import_export_fields', $fields );

?>

<div id="yith_woocommerce_points_and_rewards_import_export" class="yith-plugin-fw  yit-admin-panel-container">
	<div class="yit-admin-panel-content-wrap">
		<form id="plugin-fw-wc" method="post">
			<?php wp_nonce_field( 'ywpar_import_export' ); ?>
			<h2 class="wp-heading-inline"><?php esc_html_e( 'Import/Export Points', 'yith-woocommerce-points-and-rewards' ); ?></h2>
			<table class="form-table">
				<tbody>
				<?php foreach ( $fields as $field ) : ?>
					<tr class="yith-plugin-fw-panel-wc-row <?php echo esc_html( $field['type'] ); ?> <?php echo esc_html( $field['name'] ); ?> ">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_html( $field['name'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_html( $field['type'] ); ?>">
							<?php
							yith_plugin_fw_get_field( $field, true );
							if ( isset( $field['desc'] ) && '' !== $field['desc'] ) {
								?>
								<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
								<?php
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr class="yith-plugin-fw-panel-wc-row upload file_import_csv">
					<th scope="row" class="titledesc">
						<label for="file_import_csv"><?php esc_html_e( 'Upload CSV file', 'yith-woocommerce-points-and-rewards' ); ?></label>
					</th>
					<td class="forminp forminp-upload">
						<button id="file_import_csv_btn" class="button button-primary"><?php esc_html_e( 'Upload', 'yith-woocommerce-points-and-rewards' ); ?></button>
						<span class="ywpar_file_name"></span>
						<input type="file" id="file_import_csv" name="file_import_csv" style="display: none">
						<span class="description"><?php esc_html_e( 'Upload the CSV file to import points', 'yith-woocommerce-points-and-rewards' ); ?></span>
					</td>

				</tr>
				</tbody>
				<tfoot>
				<tr>
					<td>
						<input type="hidden" class="ywpar_safe_submit_field" name="ywpar_safe_submit_field" value="" data-std="">
						<button class="button button-primary"
							id="ywpar_import_points"><?php esc_html_e( 'Start', 'yith-woocommerce-points-and-rewards' ); ?></button>
					</td>
				</tr>
				</tfoot>
			</table>


			<div class="clear"></div>
		</form>
	</div>

	<?php do_action( 'ywpar_import_more' ); ?>

</div>
