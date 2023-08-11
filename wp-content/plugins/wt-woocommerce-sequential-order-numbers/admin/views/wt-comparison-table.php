<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

$no_icon='<span class="dashicons dashicons-dismiss" style="color:#ea1515;"></span>&nbsp;';
$yes_icon='<span class="dashicons dashicons-yes-alt" style="color:#18c01d;"></span>&nbsp;';
$webtoffee_logo='<img src="'.WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/wt_logo.png" style="" />&nbsp;';
global $wp_version;
if(version_compare($wp_version, '5.2.0')<0)
{
 	$yes_icon='<img src="'.WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/tick_icon_green.png" style="float:left;" />&nbsp;';
}

/**
*	Array format
*	First 	: Feature
*	Second 	: Basic availability. Supports: Boolean, Array(Boolean and String values), String
*	Pro 	: Pro availability. Supports: Boolean, Array(Boolean and String values), String
*/
$comparison_data=array(

	array(
		__('Add order number prefix', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Set order number length', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Dynamic preview of order numbers', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Custom starting number for orders', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Keep existing order numbers', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Easy custom order number search', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Order tracking', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		__('Custom suffix for order numbers', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Add order date as suffix', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Auto-reset order numbers', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Separate order number sequence for free orders', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Custom increment for order sequence', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Premium suppport', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		__('Order number templates', 'wt-woocommerce-sequential-order-numbers'),
		__('Limited', 'wt-woocommerce-sequential-order-numbers'),
		true,
	),
);
function wt_seq_free_vs_pro_column_vl($vl, $yes_icon, $no_icon)
{
	if(is_array($vl))
	{
		foreach ($vl as $value)
		{
			if(is_bool($value))
			{
				echo ($value ? $yes_icon : $no_icon);
			}else
			{
				//string only
				echo $value;
			}
		}
	}else
	{
		if(is_bool($vl))
		{
			echo ($vl ? $yes_icon : $no_icon);
		}else
		{
			//string only
			echo $vl;
		}
	}
}
?>
<div class="wt_seq_free_vs_pro">
	<table class="wt_sequential_freevs_pro">
	<tr>
		<td><?php _e('FEATURES', 'wt-woocommerce-sequential-order-numbers'); ?></td>
		<td><?php _e('FREE', 'wt-woocommerce-sequential-order-numbers'); ?></td>
		<td><?php _e('PREMIUM', 'wt-woocommerce-sequential-order-numbers'); ?></td>
	</tr>
	<?php
	foreach ($comparison_data as $val_arr)
	{
		?>
		<tr>
			<td><?php echo $val_arr[0];?></td>
			<td>
				<?php
				wt_seq_free_vs_pro_column_vl($val_arr[1], $yes_icon, $no_icon);
				?>
			</td>
			<td>
				<?php
				wt_seq_free_vs_pro_column_vl($val_arr[2], $yes_icon, $no_icon);
				?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
</div>
<style type="text/css">
	.wt_sequential_freevs_pro{ width:100%; border-collapse:collapse; border-spacing:0px; background-color: #ffffff; }
	.wt_sequential_freevs_pro td{ border:solid 1px #e7eaef; text-align:center; vertical-align:middle; padding:15px 20px;}
	.wt_sequential_freevs_pro tr td:first-child{ background:#f8f9fa; text-align:left;}
	.wt_sequential_freevs_pro tr:first-child td{ font-weight:bold; }
</style>
<script type="text/javascript">
	//hide save settings button in license section
	jQuery(document).ready(function($){
 		$('p.submit').hide();
	});
</script>