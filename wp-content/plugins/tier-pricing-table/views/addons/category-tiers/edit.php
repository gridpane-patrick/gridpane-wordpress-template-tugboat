<?php if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Category rules
 *
 * @var array $rules
 */

$prefix = 'category';
?>

<tr class="form-field">
	<th scope="row" valign="top">
		<label for="attribute_label">Tiered pricing</label>
	</th>
	<td>

		<p class="form-field" data-tiered-price-type-percentage data-tiered-price-type>

			<span data-price-rules-wrapper>
		<?php if ( ! empty( $rules ) ) : ?>
			<?php foreach ( $rules as $amount => $discount ) : ?>
				<span data-price-rules-container>
					<span data-price-rules-input-wrapper style="display: flex">
						<input type="number" value="<?php echo esc_attr( $amount ); ?>" min="2"
							   style="margin-right: 10px;"
							   placeholder="<?php esc_attr_e( 'Quantity', 'tier-pricing-table' ); ?>"
							   class="price-quantity-rule price-quantity-rule--simple"
							   name="tiered_price_percent_quantity_<?php echo esc_attr( $prefix ); ?>[]">
						<input type="number" value="<?php echo esc_attr( $discount ); ?>" max="99"
							   placeholder="<?php esc_attr_e( 'Percent discount', 'tier-pricing-table' ); ?>"
							   class="price-quantity-rule--simple"
							   name="tiered_price_percent_discount_<?php echo esc_attr( $prefix ); ?>[]"
							   step="any"
						>
						<span class="notice-dismiss remove-price-rule" data-remove-price-rule
							  style="position: relative"></span>
					</span>
					<br>
				</span>

			<?php endforeach; ?>
		<?php endif; ?>

		<span data-price-rules-container>
			<span data-price-rules-input-wrapper style="display: flex">
				<input type="number" style="margin-right: 10px;" min="2"
					   placeholder="<?php esc_attr_e( 'Quantity', 'tier-pricing-table' ); ?>"
					   class="price-quantity-rule price-quantity-rule--simple"
					   name="tiered_price_percent_quantity_<?php echo esc_attr( $prefix ); ?>[]"
				>
				<input type="number" max="99"
					   placeholder="<?php esc_attr_e( 'Percent discount', 'tier-pricing-table' ); ?>"
					   class="price-quantity-rule--simple"
					   name="tiered_price_percent_discount_<?php echo esc_attr( $prefix ); ?>[]" step="any"
				>
				<span class="notice-dismiss remove-price-rule"
					  data-remove-price-rule
					  style="position: relative"></span>
			</span>
			 <br>
		</span>

	<button data-add-new-price-rule class="button">
		<?php esc_attr_e( 'New tier', 'tier-pricing-table' ); ?>
	</button>

			</span>
		</p>

		<br>
		<p class="description">
			<?php esc_attr_e( 'Assign percentage discounts for products that have this category. Rules can be overridden in product.', 'tier-pricing-table' ); ?>
		</p>
	</td>
</tr>


<script>
	var addNewButton = jQuery('[data-add-new-price-rule]');

	addNewButton.on('click', function (e) {
		e.preventDefault();

		var newRuleInputs = jQuery(e.target).parent().find('[data-price-rules-input-wrapper]').first().clone();

		jQuery('<span data-price-rules-container></span>').insertBefore(jQuery(e.target))
			.append(newRuleInputs)
			.append('<span class="notice-dismiss remove-price-rule" data-remove-price-rule style="vertical-align: middle"></span>')
			.append('<br>');

		newRuleInputs.children('input').val('');
	});

	jQuery('body').on('click', '.remove-price-rule', function (e) {

		e.preventDefault();

		var element = jQuery(e.target.parentElement.parentElement);
		var wrapper = element.closest('p');
		var containers = wrapper.find('[data-price-rules-container]');

		if ((containers.length) < 2) {
			containers.find('input').val('');
			return;
		}

		jQuery('.wc_input_price').trigger('change');

		element.remove();
	});
</script>
