<?php
/**
 * Box packing setting HTML.
 *
 * Included by WC_Shipping_Aramex::generate_box_packing_html().
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<tr valign="top" id="packing_options">
	<th scope="row" class="titledesc"><?php _e( 'Box Sizes', 'woocommerce-shipping-aramex' ); ?></th>
	<td class="forminp">
		<style type="text/css">
			.aramex_boxes td, .aramex_boxes th {
				vertical-align: middle;
				padding: 4px 7px;
			}
			.aramex_boxes td input {
				margin-right: 4px;
			}
			.aramex_boxes .check-column {
				vertical-align: middle;
				text-align: left;
				padding: 0 7px;
			}
		</style>
		<table class="aramex_boxes widefat">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" /></th>
					<th><?php _e( 'Name', 'woocommerce-shipping-aramex' ); ?></th>
					<th><?php
						_e( 'L', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'W', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'H', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'Inner L', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'Inner W', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'Inner H', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_dimension_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'Box Weight', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_weight_unit' ) . ')';
						?> </th>
					<th><?php
						_e( 'Max Weight', 'woocommerce-shipping-aramex' );
						echo ' (' . get_option( 'woocommerce_weight_unit' ) . ')';
						?> </th>
	<!--                    <th><?php _e( 'Letter', 'woocommerce-shipping-aramex' ); ?></th>-->
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="3">
						<a href="#" class="button plus insert"><?php _e( 'Add Box', 'woocommerce-shipping-aramex' ); ?></a>
						<a href="#" class="button minus remove"><?php _e( 'Remove selected box(es)', 'woocommerce-shipping-aramex' ); ?></a>
					</th>
					<th colspan="8">
						<small class="description"><?php _e( 'Items will be packed into these boxes based on item dimensions and volume. Outer dimensions will be passed to Aramex, whereas inner dimensions will be used for packing.', 'woocommerce-shipping-aramex' ); ?></small>
					</th>
				</tr>
			</tfoot>
			<tbody id="rates">
				<?php
				if ( ! empty( $this->boxes ) ) {
					foreach ( $this->boxes as $key => $box ) {
						?>
						<tr>
							<td class="check-column"><input type="checkbox" /></td>
							<td><input type="text" size="10" name="aramex_boxes_name[<?php echo $key; ?>]" value="<?php echo isset( $box['name'] ) ? esc_attr( $box['name'] ) : ''; ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_outer_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_length'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_outer_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_width'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_outer_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_height'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_inner_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_length'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_inner_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_width'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_inner_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_height'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_box_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['box_weight'] ); ?>" /></td>
							<td><input type="text" size="5" name="aramex_boxes_max_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['max_weight'] ); ?>" /></td>
							<!--<td><input type="checkbox" name="boxes_is_letter[<?php echo $key; ?>]" <?php checked( isset( $box['is_letter'] ) && $box['is_letter'], true ); ?> /></td>-->
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<script type="text/javascript">

			jQuery(window).load(function () {


				jQuery('.aramex_boxes .insert').click(function () {
					var $tbody = jQuery('.aramex_boxes').find('tbody');
					var size = $tbody.find('tr').size();
					var code = '<tr class="new">\
												<td class="check-column"><input type="checkbox" /></td>\
												<td><input type="text" size="10" name="aramex_boxes_name[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_outer_length[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_outer_width[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_outer_height[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_inner_length[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_inner_width[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_inner_height[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_box_weight[' + size + ']" /></td>\
												<td><input type="text" size="5" name="aramex_boxes_max_weight[' + size + ']" /></td>\
										</tr>';

					$tbody.append(code);

					return false;
				});

				jQuery('.aramex_boxes .remove').click(function () {
					var $tbody = jQuery('.aramex_boxes').find('tbody');

					$tbody.find('.check-column input:checked').each(function () {
						jQuery(this).closest('tr').hide().find('input').val('');
					});

					return false;
				});


				//enable box packing
				if (jQuery('.enable_box_packing').is(':checked')) {
					jQuery('#packing_options').show();
				} else {
					jQuery('#packing_options').hide();
				}
				jQuery('.enable_box_packing').change(function () {
					if (jQuery(this).is(':checked')) {
						jQuery('#packing_options').show();
					} else {
						jQuery('#packing_options').hide();
					}
				});
			});

		</script>
	</td>
</tr>
