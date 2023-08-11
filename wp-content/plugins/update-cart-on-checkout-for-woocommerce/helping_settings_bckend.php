<?php

$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
if ('' == $plgfydc_all_data) {
	$plgfydc_all_data=array(
		
		'enplable'=>'true',
		'plgfyqdp_gst'=>'true',
		'woospca_icons'=>'trash',
		'clrfrtrsh'=>'#000000',
		'woosppo_applied_onc'=>'Products',
		'plgfyqdp_rmvallbtnnn'=>'false',
		'applied_on_ids'=>array(),
		'plgfyqdp_customer_role'=>array()
		

	);
}

if (!isset($plgfydc_all_data['plgfyqdp_rmvallbtnnn'])) {
	$plgfydc_all_data['plgfyqdp_rmvallbtnnn'] = 'false';
}


$woosppo_selected_pro_cat_type='Products';
$woosppo_allowed_products_cat=array();

global $wpdb;
$page1 = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type= %s  AND post_status= %s", 'product', 'publish' ) );




?>
<div id="main_settings_div">
	<input type="hidden" id="plgfydc_savediv">
	<br>
	<div style="width:97%;border-radius:5px;padding:10px;border: 4px solid #ae7b3b;display: inline-flex;" id="parent_div">
		
		<div style="width: 98%;margin-left: 1%;" id="right_div">
			<h1 style="font-family: ver;font-size: 28px;background-color: #dcdcde;padding: 1px 10px;border-radius: 2px;">General Settings</h1>
			<hr>
			<table class="plgfydc_tbl_main" style="width: 100%; ">

				<tr>
					<td style="width:40%;">
						<strong >
							Enable Settings
						</strong>
					</td>
					<td style="width:50%;margin-left: 3%;">
						<label class="switch">
							<input type="checkbox" id="enplable"
							<?php
							if ('true' == $plgfydc_all_data['enplable']) {
								echo filter_var('checked');
							}
							?>
							>
							<span class="slider"></span>
						</label>
					</td>
				</tr>
				<tr>
					<td style="width:40%;">
						<strong >
							Choose Remove Product Icon
						</strong>
					</td>
					<td style="width:50%;margin-left: 3%;">
						<div id="slctiondiv" >
							<select style="width:100%;" id="woospca_icons" class="woospca_select"></select>

						</div>
					</td>
				</tr>
				<tr>
					<td style="width:40%;border-bottom: 1px solid #eee !important;">
						<strong >
							Icon Color
						</strong>
					</td>
					<td style="width:50%;margin-left: 3%;border-bottom: 1px solid #eee !important;">
						<input value="<?php echo filter_var($plgfydc_all_data['clrfrtrsh']); ?>" style="width:100%;" type="color" id="clrfrtrsh">
					</td>
				</tr>
				<tr id="" >
					<td style="width:40%;border-bottom: 1px solid #eee;">					

						<select id="woosppo_applied_onc" style="width: 95%;" name="applied_woosppo_on">
							<option value="Products"
							<?php 
							if ('Products' == $plgfydc_all_data['woosppo_applied_onc']) {
								echo filter_var('selected');
							}
							?>
							>Enable On Specific Products</option>
							<option value="Category"
							<?php 
							if ('Category' == $plgfydc_all_data['woosppo_applied_onc']) {
								echo filter_var('selected');
							}
							?>
							>Select On Specific Categories </option>
							<option value="whole"
							<?php 
							if ('whole' == $plgfydc_all_data['woosppo_applied_onc']) {
								echo filter_var('selected');
							}
							?>
							>Apply To All Products </option>
						</select>
					</td>
					<td style="width:50%;margin-left: 3%;border-bottom: 1px solid #eee;">
						<div id="woosppo_products1c"
						<?php
						if ('Products' != $plgfydc_all_data['woosppo_applied_onc'] && '' !=  $plgfydc_all_data['woosppo_applied_onc'] ) {

							echo filter_var(' style="display:none; "');
						}
						?>
						>
						
						<select name="woosppo_products[]" multiple="multiple"  style="max-width: 100%;width: 100%;font-size: 11px;" class=" woosppo_products" id="woosppo_productsc"   >

							<?php

							foreach ($page1 as $keyloopoi => $valueloopoi) {



								?>
								<option  class="woosppo_option-item" value="<?php echo esc_attr($valueloopoi->ID); ?>"
									<?php 
									if (isset($plgfydc_all_data['applied_on_ids'])) {
										if (in_array($valueloopoi->ID, $plgfydc_all_data['applied_on_ids'] )) {
											echo filter_var('selected');
										}
									}
									
									?>
									>
									<?php
									echo esc_attr(get_the_title($valueloopoi->ID));
									?>

								</option>
								<?php     
							}
							?>
						</select>
					</div>
					<div id="woosppo_selectcat1c" 
					<?php
					if ('Category' !=  $plgfydc_all_data['woosppo_applied_onc'] ) {
						echo filter_var(' style="display:none; "');
					}
					?>
					>

					<select  name="woosppo_selectcat[]" style="max-width: 100%;width: 100%;font-size: 11px;" id="woosppo_selectcatc" class="woosppo_selectcat"   multiple='multiple[]'>
						<?php
						$woosppo_parentid = get_queried_object_id();
						$woosppo_args = array(
							'numberposts' => -1,
							'taxonomy' => 'product_cat',
						);
						$woosppo_terms = get_terms($woosppo_args);
						if ( $woosppo_terms ) {   
							foreach ( $woosppo_terms as $woosppo_term1 ) {
								
								?>
								<option class="woosppo_catopt"
								<?php 
								if (isset($plgfydc_all_data['applied_on_ids'])) {
									if (in_array($woosppo_term1->term_id, $plgfydc_all_data['applied_on_ids'])) {
										echo filter_var('selected');
									}
								}
								
								?>
								value="<?php echo esc_attr($woosppo_term1->term_id); ?> ">
								<?php
								echo esc_attr($woosppo_term1->name);
								?>
							</option>
								<?php
							}          
						}
						?>
				</select>
			</div>
		</td>

	</tr>
	
	
	<tr>
		<td style="width:40%;border-bottom: 1px solid #eee !important;">
			<strong >
				Disable For User Roles
			</strong>
		</td>
		<td style="width:50%;margin-left: 3%;border-bottom: 1px solid #eee !important;">
			<div >
				<?php 
				global $wp_roles;
				$plgfyqdp_all_roles = $wp_roles->get_names();
				?>
				<select class="plgfyqdp_customer_roleclass" id="plgfyqdp_customer_role" multiple="multiple" class="form-control " style="width: 98%;">
					<?php
					foreach ($plgfyqdp_all_roles as $key_role => $value_role) {
						?>
						<option value="<?php echo filter_var($key_role); ?>"
							<?php
							if (isset($plgfydc_all_data['plgfyqdp_customer_role'])) {
								if (in_array($key_role, $plgfydc_all_data['plgfyqdp_customer_role'])) {
									echo filter_var('selected');
								}
							}
							
							?>
							><?php echo filter_var(ucfirst($value_role)); ?></option>
							<?php
					}
					?>

					</select>
					<br><i style="color: green;">(Leave empty to allow all roles as selected)</i>

				</div>
			</td>
		</tr>
		<tr>
			<td style="width:40%;">
				<strong >
					Enable For Guests
				</strong>
			</td>
			<td style="width:50%;margin-left: 3%;">
				<label class="switch">
					<input type="checkbox" id="plgfyqdp_gst"
					<?php
					if ('true' == $plgfydc_all_data['plgfyqdp_gst']) {
						echo filter_var('checked');
					}
					?>
					>
					<span class="slider"></span>
				</label>
			</td>
		</tr>

		<tr>
			<td style="width:40%;">
				<strong >
					Show Remove All Products Button
				</strong>
			</td>
			<td style="width:50%;margin-left: 3%;">
				<label class="switch">
					<input type="checkbox" id="plgfyqdp_rmvallbtnnn"
					<?php
					if ('true' == $plgfydc_all_data['plgfyqdp_rmvallbtnnn']) {
						echo filter_var('checked');
					}
					?>
					>
					<span class="slider"></span>
				</label>
			</td>
		</tr>	

		<tr>
			<td colspan="2">
				<button type="button" class="button-primary plgfydc_save_gnrl_set" style="background: #ae7b3b; border-color: #ae7b3b;background-color: #ae7b3b;float: right;right: 5px;">Save Settings</button>
			</td>

		</tr>

	</table>
</div>

</div>
<br>
<br>
</div>


<style type="text/css">

	.switch {
		position: relative;
		display: inline-block;
		width: 50px;
		height: 26px;
	}

	.switch input { 
		opacity: 0;
		width: 0;
		height: 0;
	}

	.slider {
		border-radius: 3px;
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #dcdcde;
		-webkit-transition: .4s;
		transition: .4s;
	}

	.slider:before {
		border-radius: 3px;
		position: absolute;
		content: "";
		height: 18px;
		width: 18px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
	}

	input:checked + .slider {
		background-color: #ae7b3b;
		background-image: linear-gradient(#ae7b3b, #d69323);
	}

	input:focus + .slider {
		box-shadow: 0 0 1px #ae7b3b;

	}

	input:checked + .slider:before {
		-webkit-transform: translateX(26px);
		-ms-transform: translateX(26px);
		transform: translateX(26px);

	}
	#main_settings_div{
		background-color: #FFF;
		border-radius: 5px;
		padding: 5px 20px;
	}
	#main_settings_div strong{
		font-size: 15px;
	}
	.plgfydc_tbl_main {
		font-family: Arial, Helvetica, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}

	.plgfydc_tbl_main td, .plgfydc_tbl_main th {
		padding: 14px;
		/*border-bottom: 1px solid #eee;*/
	}

	.plgfydc_tbl_main11 td, .plgfydc_tbl_main11 th {
		padding: 1px !important;
	}

	.plgfydc_tbl_main th {
		padding-top: 12px;
		padding-bottom: 12px;
		text-align: left;
		background-color: #4CAF50;
		color: white;
	}

	.woocommerce-save-button{
		display: none !important;
	}



</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery.get('<?php echo filter_var(plugin_dir_url(__FILE__) . 'js/icons.yml'); ?>', function(data) {
			var parsedYaml = jsyaml.load(data);

			var options = new Array();
			jQuery.each(parsedYaml.icons, function(index, icon){
				options.push({
					id: icon.id,
					text: '<i class="fa fa-fw fa-' + icon.id + '"></i> ' + icon.id

				});
			});



			jQuery('.woospca_select').select2({
				data: options,
				escapeMarkup: function(markup) {
					return markup;
				}
			});

		});
		setTimeout(function(){
			jQuery('.plgfydc_tbl_main').find('#slctiondiv').find('.selection').find('.select2-selection__rendered').html('<i class="fa fa-fw fa-<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>"></i> <?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>');
			jQuery('.plgfydc_tbl_main').find('#woospca_icons').val('<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>'); 
		},400);
		setTimeout(function(){
			jQuery('.plgfydc_tbl_main').find('#slctiondiv').find('.selection').find('.select2-selection__rendered').html('<i class="fa fa-fw fa-<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>"></i> <?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>');
			jQuery('.plgfydc_tbl_main').find('#woospca_icons').val('<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>'); 
		},1000);
		setTimeout(function(){
			jQuery('.plgfydc_tbl_main').find('#slctiondiv').find('.selection').find('.select2-selection__rendered').html('<i class="fa fa-fw fa-<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>"></i> <?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>');
			jQuery('.plgfydc_tbl_main').find('#woospca_icons').val('<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>'); 
		},2000);
		setTimeout(function(){
			jQuery('.plgfydc_tbl_main').find('#slctiondiv').find('.selection').find('.select2-selection__rendered').html('<i class="fa fa-fw fa-<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>"></i> <?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>');
			jQuery('.plgfydc_tbl_main').find('#woospca_icons').val('<?php echo filter_var($plgfydc_all_data['woospca_icons']); ?>'); 
		},5000);
		jQuery('body').on('change', '.woospca_select' , function(){
			var icono = jQuery(this).val();

			jQuery('.plgfydc_tbl_main').find('#woospca_icons').val(icono); 

			setTimeout(function(){

				jQuery('.plgfydc_tbl_main').find('#slctiondiv').find('.selection').find('.select2-selection__rendered').html('<i class="fa fa-fw fa-' + icono + '"></i> '+icono)
			},500);


		});

		jQuery('#woosppo_productsc').select2();
		jQuery('#woosppo_selectcatc').select2();
		jQuery('#plgfyqdp_customer_role').select2();
		jQuery('#woosppo_applied_onc').on('change', function(){
			if (jQuery(this).val()=='Products') {
				jQuery('#woosppo_products1c').show();
				jQuery('#woosppo_selectcat1c').hide();
			} else if (jQuery(this).val()=='Category'){
				jQuery('#woosppo_products1c').hide();
				jQuery('#woosppo_selectcat1c').show();
			} else {
				jQuery('#woosppo_products1c').hide();
				jQuery('#woosppo_selectcat1c').hide();
			}
		});
		
		jQuery('.plgfydc_save_gnrl_set').on('click', function(){

			
			
			var woosppo_applied_onc=jQuery('#woosppo_applied_onc').val();
			if ('Products' == woosppo_applied_onc) {
				var applied_on_ids=jQuery('#woosppo_productsc').val();
			}else if ('Category' == woosppo_applied_onc) {
				var applied_on_ids=jQuery('#woosppo_selectcatc').val();
			} else {
				var applied_on_ids='whole';
			}
			
			
			jQuery.ajax ({
				url: '<?php echo filter_var(admin_url('admin-ajax.php')); ?>',
				type:'POST',
				data:{
					action : 'save_gnrl_settings_plgfyrestrictions1',
					
					

					plgfyqdp_gst:jQuery('#plgfyqdp_gst').prop('checked'),
					enplable:jQuery('#enplable').prop('checked'),
					woospca_icons:jQuery('#woospca_icons').val(),
					clrfrtrsh:jQuery('#clrfrtrsh').val(),
					plgfyqdp_rmvallbtnnn:jQuery('#plgfyqdp_rmvallbtnnn').prop('checked'),

					woosppo_applied_onc:woosppo_applied_onc,
					applied_on_ids:applied_on_ids,					
					plgfyqdp_customer_role:jQuery('#plgfyqdp_customer_role').val(),
					
					
				},
				success:function(response) {
					window.onbeforeunload = null;
					jQuery('.plugifyyy').remove();
					jQuery('#plgfydc_savediv').after('<div class="notice notice-success is-dismissible plugifyyy" ><p id="plgfydc_saveeditmsg">Done</p><button type="button" class="notice-dismiss hidedivv"><span class="screen-reader-text">Dismiss this notice.</span></button></div>')

					jQuery('#plgfydc_saveeditmsg').html('Settings have been saved successfully!');;
					jQuery("html, body").animate({ scrollTop: 0 }, "slow");

				}

			});


		});
		jQuery('body').on('click', '.hidedivv' , function(){
			jQuery('.plugifyyy').remove();
		});
		
	});
</script>
