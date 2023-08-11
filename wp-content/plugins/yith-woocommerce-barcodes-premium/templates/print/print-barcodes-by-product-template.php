<?php
if (!defined('ABSPATH')) {
	exit;
}

if ( isset( $product_id ) && isset( $quantity ) ){ ?>

	<style>
		table {
			border-spacing: 10px;
		}

		td.main-barcode-container{
			padding: 10px;
		}
		.ywbc-barcode-display-container{
			text-align: center;
		}
	</style>

	<table>

<?php

$item_ids = array();

$show_images = get_option ( 'tool_print_barcodes_show_image', 'no' );
$show_name = get_option ( 'tool_print_barcodes_show_name', 'yes' );
$show_price = get_option ( 'tool_print_barcodes_show_price', 'no' );
$show_sku = get_option ( 'tool_print_barcodes_show_sku', 'yes' );
$show_short_description = get_option ( 'tool_print_barcodes_show_short_description', 'no' );
$number_of_columns = get_option ( 'tool_print_barcodes_number_of_columns', '2' );

for ( $i = 0; $i < $quantity; $i++ ){
	$item_ids[$i] = $product_id;
}

		foreach ( array_chunk($item_ids, $number_of_columns) as $row ) { ?>

			<tr>

				<?php  foreach ($row as $product_id ) { ?>

					<?php

					$product = wc_get_product( $product_id );

					if ( is_object($product) ) {
						$upload_dir = wp_upload_dir ();
						$image_path = $product->get_image_id() ? current ( wp_get_attachment_image_src ( $product->get_image_id(),
							'thumbnail' ) ) : wc_placeholder_img_src ( 'thumbnail' );
					}
					else{
						$image_path = wc_placeholder_img_src ( 'thumbnail' );
					}
					?>

					<?php if ( $show_images == 'yes' ): ?>
						<?php if ( $image_path ): ?>
							<td class="image-container" >
								<img class="product-image" src="<?php echo $image_path; ?>" style="width: 50px; height: 50px" />
							</td>
						<?php endif; ?>
					<?php endif; ?>

					<td class="image-container">

						<?php if ( $show_name == 'yes' ): ?>
							<div style="text-align: center; font-size: 12px"><?php echo $product->get_name() ?></div>
						<?php endif; ?>

						<?php if ( $show_sku == 'yes' ): ?>
							<div style="text-align: center;font-size: 12px"><?php echo $product->get_sku() ?></div>
						<?php endif; ?>

						<?php if ( $show_price == 'yes' ): ?>
							<div style="text-align: center;font-size: 12px"><?php echo $product->get_price_html() ?></div>
						<?php endif; ?>

						<?php if ( $show_short_description == 'yes' ): ?>
							<div style="text-align: center;font-size: 12px"><?php echo $product->get_short_description() ?></div>
						<?php endif; ?>

					</td>

					<td  class="main-barcode-container">
						<?php YITH_YWBC()->show_barcode( $product_id, '1', '', '' ); ?>
					</td>


				<?php } ?>

			</tr>

		<?php } ?></table>

	<?php

}





