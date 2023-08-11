<?php

if ( !class_exists( 'KA_Pro_Size_Charts_Admin' ) ) {



	class KA_Pro_Size_Charts_Admin extends KA_Pro_Size_Charts {



		public function __construct() {

			include_once ABSPATH . 'wp-includes/pluggable.php';

			add_action( 'admin_enqueue_scripts', array( $this, 'KA_Psc_Admin_Scripts' ) );



			add_action( 'admin_menu', array( $this, 'KA_Psc_Custom_Submenu_Admin' ) );

			//Custom meta boxes

			add_action( 'admin_init', array( $this, 'KA_Psc_Register_Metaboxes' ), 10 );



			add_action( 'save_post', array($this, 'KA_Psc_Meta_Box_Save' ));



			add_action('admin_init', array($this, 'KA_Psc_Section_Fields_Reg'));



			add_filter( 'manage_koalaapps_psc_posts_columns', array( $this, 'KA_Psc_Custom_Columns' ) );



			add_action( 'manage_koalaapps_psc_posts_custom_column' , array($this, 'KA_Psc_Populate_Custom_Column'), 10, 2 );



			// AJAX for Product

			add_action('wp_ajax_KA_Psc_Search_Products', array($this, 'KA_Psc_Search_Products'));



			if (isset($_POST['ka_psc_save_settings_btn'])) {

				if (isset($_POST['ks_psc_save_chang_nonce'])) {

					if ( ! wp_verify_nonce( sanitize_text_field($_POST['ks_psc_save_chang_nonce']), 'ka-psc-save-chang-nonce' ) ) {	

						die( 'Security Breaks' );

					}	

				} 

			}



		}



		public function KA_Psc_Admin_Scripts() {


			$in_footer = true;
			wp_enqueue_style( 'kapsc-admincss', plugins_url( '../assets/css/kapsc_admin.css', __FILE__ ), false, '1.0' );



			wp_enqueue_style( 'select2', plugins_url( '../assets/css/select2.css', __FILE__ ), false, '1.0' );



			wp_enqueue_script( 'select2', plugins_url( '../assets/js/select2.js', __FILE__ ), false, '1.0', $in_footer );



			// AJAX JS File

			wp_enqueue_script( 'kapsc-adminj', plugins_url( '../assets/js/kapsc_admin.js', __FILE__ ), false, '1.0', $in_footer );



			// AJAX for Products

			$ka_psc_data = array(

				'admin_url'  => admin_url('admin-ajax.php'),

				'nonce' => wp_create_nonce('ka-psc-ajax-nonce'),

			);



			wp_localize_script( 'kapsc-adminj', 'kapsc_php_vars', $ka_psc_data );

		}



		public function KA_Psc_Custom_Submenu_Admin() {

			add_menu_page(

				esc_html__('KoalaApps Size Chart', 'koalaapps_psc'), // page title

				esc_html__('Size Chart', 'koalaapps_psc'), // menu title

				'manage_options', // capability

				'edit.php?post_type=koalaapps_psc',  // menu-slug

				null ,   // function that will render its output

				plugins_url('../assets/img/set.png', __FILE__),   // link to the icon that will be displayed in the sidebar

				31    // position of the menu option

			);



			add_submenu_page( 'edit.php?post_type=koalaapps_psc', // parent_slug

				esc_html__( 'Size Chart', 'koalaapps_psc' ), // page_title

				esc_html__( 'Add New', 'koalaapps_psc' ), // menu_title

				'manage_options', // capability

				'post-new.php?post_type=koalaapps_psc', // menu_slug

				'' // callback function

			);



			add_submenu_page( 'edit.php?post_type=koalaapps_psc', // parent_slug

				esc_html__( 'Settings', 'koalaapps_psc' ), // page_title

				esc_html__( 'Settings', 'koalaapps_psc' ), // menu_title

				'manage_options', // capability

				'kapsc_settings', // menu_slug

				array($this, 'kapsc_Settings_CB') // callback function

			);

		}



		public function KA_Psc_Register_Metaboxes() {

			add_meta_box( 'kapsc-size-chart-mb', // id

				esc_html__( 'Size Chart', 'koalaapps_psc' ), // title which will be shown at top of metabox

				array( $this, 'kapsc_Pro_Size_Chart_MB_Callback' ), // callback function name

				'koalaapps_psc', //The screen or screens on which to show the box (such as a post type, 'link', or 'comment')

				'normal', // The context within the screen where the boxes should display

				'high' // The priority within the context where the boxes should show ('high', 'low').

			);

		}



		public function kapsc_Pro_Size_Chart_MB_Callback() {

			global $post;

			wp_nonce_field( 'kapsc_fields_nonce', 'kapsc-field-nonce' );

			$kapsc_chart_type = get_post_meta($post->ID, 'kapsc_chart_type', true);

			$kapsc_chart_desc = get_post_meta($post->ID, 'chartDescription', true);

			$kapsc_country = unserialize(get_post_meta( intval($post->ID), 'kapsc_country', true ) );
			
			$kapsc_products = json_decode( get_post_meta( intval($post->ID), 'kapsc_products', true ) );

			$kapsc_categories = json_decode( get_post_meta( intval($post->ID), 'kapsc_categories', true ) );

			$kapsc_chart_data = get_post_meta( $post->ID, 'kapsc_chart_data', true ) ? get_post_meta( $post->ID, 'kapsc_chart_data', true ) : '[[""]]';

			$kapsc_chart_img = get_post_meta( intval($post->ID), 'kapsc_chart_img', true );
			
			$data = json_decode($kapsc_chart_data);
			
			?>



				<div class="kapsc_admin_main">

					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Chart Type', 'koalaapps_psc'); ?></strong></label></div>

					<div class="kapsc_admin_main_right">

						<select name="kapsc_chart_type" id="kapsc_chart_type" onchange="KA_Psc_GetUserRole(this.value);">

							<option value="chart_table" <?php echo selected('chart_table', $kapsc_chart_type); ?>><?php echo esc_html__('Table', 'koalaapps_psc'); ?></option>

							<option value="chart_img" <?php echo selected('chart_img', $kapsc_chart_type); ?>><?php echo esc_html__('Image', 'koalaapps_psc'); ?></option>

						</select>

					</div>

				</div>



				<div class="kapsc_admin_main">

					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Chart Description', 'koalaapps_psc'); ?></strong></label></div>

					<div class="kapsc_admin_main_right">

						<?php

						

						$settings = array(

							'wpautop' => false,

							'tinymce' => true,

							'textarea_rows' => 20,

							'quicktags' => array('buttons' => 'em,strong,link,p,br'),

							'quicktags' => true,

							'tinymce' => true,

						);



						wp_editor($kapsc_chart_desc, 'chartDescription', $settings);

						?>

					</div>

				</div>



				<div class="kapsc_admin_main chart_type" id="ka_psc_table">

					

					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Create Size Chart', 'koalaapps_psc'); ?></strong></label></div>



					<div class="kapsc_admin_main_right table-repsonsive">



						<input id="kapsc_hidden_tab_fld" type="hidden" name="kapsc_chart_data" value='<?php echo esc_attr(str_replace( '\'', '&apos;', $kapsc_chart_data )); ?>'>



						<table class="kapsc_admin_chart_table table-bordered">

							<thead>

								<tr>

									<?php foreach ($data[0] as $colomn) { ?>

										<th>

											<button type="button" class="kapsc_add_col kapsc_add_col_btn kapsc_button">+</button>

											<button type="button" class="kapsc_rem_col kapsc_rem_col_btn kapsc_button">-</button>

										</th>

									<?php } ?>

									<th></th>

								</tr>

							</thead>

							<tbody>

								<?php foreach ($data as $rows) { ?>

									<tr>

										<?php foreach ($rows as $coloum) { ?>

											<td>

												<input name="kapsc_items[]" class="kapsc_table_input" type="text" value="<?php echo esc_attr(str_replace('"', '&quot;', $coloum)); ?>"/>

											</td>

										<?php } ?>

										<td class="kapsc_buttons_in_row">

											<button type="button" class="kapsc_add_row kapsc_add_row_btn kapsc_button">+</button>

											<button type="button" class="kapsc_rem_row kapsc_rem_row_btn kapsc_button">-</button>

										</td>

									</tr>

								<?php } ?>

							</tbody>

						</table>

					</div>

				</div>





				<div class="kapsc_admin_main upload_chart_img" id="ka_psc_img">



					<?php if (!empty($kapsc_chart_img)) { ?>

					<div class="kapsc_admin_main" id="ka_psc_img">

						<div class="kapsc_admin_main_left" id="logodisplay">

						

							

							<label for="kapsc_chart_img"><strong><?php echo esc_html__('Current Chart Image', 'koalaapps_psc'); ?></strong></label>

							<div class="kapsc_admin_main_right">

								<img src="<?php echo esc_url($kapsc_chart_img); ?>" width="200" />

								

							</div>

							

						</div>

					</div>

					<?php } ?>

					



					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Choose Chart Image', 'koalaapps_psc'); ?></strong></label></div>

					<div class="kapsc_admin_main_right">

						<input type="hidden" value="<?php echo esc_url($kapsc_chart_img); ?>" name="kapsc_chart_img" id="kapsc_thumb_url" class="login_title">

						<input onClick="kapsc_image()" type="button" name="upload-btn" id="upload-image-btn" class="button-secondary" value="<?php echo esc_html__('Upload Chart Image', 'koalaapps_psc'); ?>">

						<input onClick="kapsc_clear_image()" type="button" name="upload-btn" id="clear-image-btn" class="button-secondary" value="<?php echo esc_html__('Remove Chart Image', 'koalaapps_psc'); ?>">

					</div>

				</div>





				<div class="kapsc_admin_main">



					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Select Countries', 'koalaapps_psc'); ?></strong></label></div>



					<div class="kapsc_admin_main_right">



						<?php



							global $woocommerce;

							$countries_obj = new WC_Countries();

							$countries = $countries_obj->__get('countries');



						?>



						<select class="select_box wc-enhanced-select kapsc_country" name="kapsc_country[]" id="kapsc_country"  multiple='multiple'>



							<?php foreach ($countries as $key => $value) { ?>

								<option value="<?php echo esc_attr($key); ?>" 

									<?php

									if (!empty($kapsc_country) && in_array($key, $kapsc_country)) {

										echo 'selected';

									}

									?>

									  >

									<?php echo esc_attr($value); ?>

								</option>

							<?php } ?>

						</select>

						<p class="description"><?php echo esc_html__('Leave empty to apply for all countries.', 'koalaapps_psc'); ?></p>

					</div>

				</div>



				<div class="kapsc_admin_main">

					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Apply on All Products', 'koalaapps_psc'); ?></strong></label></div>

					<div class="kapsc_admin_main_right">

						<?php

							$applied_on_all_products = get_post_meta($post->ID, 'kapsc_apply_on_all_products', true);

						?>

						<input type="checkbox" name="kapsc_apply_on_all_products" id="kapsc_apply_on_all_products" value="yes" <?php echo checked('yes', $applied_on_all_products); ?>>

						<p class="csp_msg"><?php echo esc_html__('Check this if you want to apply this chart on all products.', 'koalaapps_psc'); ?></p>

					</div>

				</div>





				<div class="kapsc_admin_main hide_all_pro">



					<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Choose Products', 'koalaapps_psc'); ?></strong></label></div>



					<div class="kapsc_admin_main_right">

						<select class="select_box wc-enhanced-select kapsc_products" name="kapsc_products[]" id="kapsc_products"  multiple='multiple'>

							<?php



							if (!empty($kapsc_products)) {



								foreach ( $kapsc_products as $pro) {



									$prod_post = get_post($pro);



									?>



										<option value="<?php echo intval($pro); ?>" selected="selected"><?php echo esc_attr($prod_post->post_title); ?></option>

									<?php 

								}

							}

							?>

						</select>

					</div>

				</div>



				<div class="kapsc_admin_main hide_all_pro">



				<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Select Categories', 'koalaapps_psc'); ?></strong></label></div>



				<div class="kapsc_admin_main_right">

					<div class="all_cats">

						<ul>

							<?php



							$pre_vals = $kapsc_categories;



							$args = array(

								'taxonomy' => 'product_cat',

								'hide_empty' => false,

								'parent'   => 0

							);



							$product_cat = get_terms( $args );

							foreach ($product_cat as $parent_product_cat) {

								?>

								<li class="par_cat">

									<input type="checkbox" class="parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($parent_product_cat->term_id); ?>" 

									<?php 

									if (!empty($pre_vals) && in_array($parent_product_cat->term_id, $pre_vals)) { 

										echo 'checked';

									}

									?>

									/>

									<?php echo esc_attr($parent_product_cat->name); ?>



									<?php

									$child_args = array(

										'taxonomy' => 'product_cat',

										'hide_empty' => false,

										'parent'   => intval($parent_product_cat->term_id)

									);

									$child_product_cats = get_terms( $child_args );

									if (!empty($child_product_cats)) {

										?>

										<ul>

											<?php foreach ($child_product_cats as $child_product_cat) { ?>

												<li class="child_cat">

													<input type="checkbox" class="child parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat->term_id); ?>" 

													<?php

													if (!empty($pre_vals) &&in_array($child_product_cat->term_id, $pre_vals)) { 

														echo 'checked';

													}

													?>

													/>

													<?php echo esc_attr($child_product_cat->name); ?>



													<?php

													//2nd level

													$child_args2 = array(

														'taxonomy' => 'product_cat',

														'hide_empty' => false,

														'parent'   => intval($child_product_cat->term_id)

													);



													$child_product_cats2 = get_terms( $child_args2 );

													if (!empty($child_product_cats2)) {

														?>



														<ul>

															<?php foreach ($child_product_cats2 as $child_product_cat2) { ?>



																<li class="child_cat">

																	<input type="checkbox" class="child parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat2->term_id); ?>" 

																	<?php

																	if (!empty($pre_vals) &&in_array($child_product_cat2->term_id, $pre_vals)) {

																		echo 'checked';

																	}

																	?>

																	/>

																	<?php echo esc_attr($child_product_cat2->name); ?>





																	<?php

																	//3rd level

																	$child_args3 = array(

																		'taxonomy' => 'product_cat',

																		'hide_empty' => false,

																		'parent'   => intval($child_product_cat2->term_id)

																	);



																	$child_product_cats3 = get_terms( $child_args3 );

																	if (!empty($child_product_cats3)) {

																		?>



																		<ul>

																			<?php foreach ($child_product_cats3 as $child_product_cat3) { ?>



																				<li class="child_cat">

																					<input type="checkbox" class="child parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat3->term_id); ?>" 

																					<?php

																					if (!empty($pre_vals) &&in_array($child_product_cat3->term_id, $pre_vals)) {

																						echo 'checked';

																					}

																					?>

																					/>

																					<?php echo esc_attr($child_product_cat3->name); ?>





																					<?php

																					//4th level

																					$child_args4 = array(

																						'taxonomy' => 'product_cat',

																						'hide_empty' => false,

																						'parent'   => intval($child_product_cat3->term_id)

																					);



																					$child_product_cats4 = get_terms( $child_args4 );

																					if (!empty($child_product_cats4)) {

																						?>



																						<ul>

																							<?php foreach ($child_product_cats4 as $child_product_cat4) { ?>



																								<li class="child_cat">

																									<input type="checkbox" class="child parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat4->term_id); ?>"

																									<?php

																									if (!empty($pre_vals) &&in_array($child_product_cat4->term_id, $pre_vals)) {

																										echo 'checked';

																									}

																									?>

																									/>

																									<?php echo esc_attr($child_product_cat4->name); ?>





																									<?php

																									//5th level

																									$child_args5 = array(

																										'taxonomy' => 'product_cat',

																										'hide_empty' => false,

																										'parent'   => intval($child_product_cat4->term_id)

																									);



																									$child_product_cats5 = get_terms( $child_args5 );

																									if (!empty($child_product_cats5)) {

																										?>



																										<ul>

																											<?php foreach ($child_product_cats5 as $child_product_cat5) { ?>



																												<li class="child_cat">

																												  <input type="checkbox" class="child parent" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat5->term_id); ?>" 

																																																							   <?php

																																																								if (!empty($pre_vals) &&in_array($child_product_cat5->term_id, $pre_vals)) {

																																																									echo 'checked';

																																																								}

																																																								?>

																												/>

																												

																												<?php echo esc_attr($child_product_cat5->name); ?>



																												<?php

																												//6th level

																												$child_args6 = array(

																												 'taxonomy' => 'product_cat',

																												 'hide_empty' => false,

																												 'parent' => intval($child_product_cat5->term_id));

																												 $child_product_cats6 = get_terms( $child_args6 );

																												if (!empty($child_product_cats6)) {

																													?>



																														<ul>

																														   <?php foreach ($child_product_cats6 as $child_product_cat6) { ?>



																																<li class="child_cat">

																																	<input type="checkbox" class="child" name="kapsc_categories[]" id="kapsc_categories" value="<?php echo intval($child_product_cat6->term_id); ?>" 

																																	<?php

																																	if (!empty($pre_vals) &&in_array($child_product_cat6->term_id, $pre_vals)) {

																																		echo 'checked';

																																	}

																																	?>

																																	/>

																																	<?php echo esc_attr($child_product_cat6->name); ?>

																																</li>



																															<?php } ?>

																														</ul>



																													<?php } ?>



																												</li>



																											<?php } ?>

																										</ul>



																									<?php } ?>





																								</li>



																							<?php } ?>

																						</ul>



																					<?php } ?>





																				</li>



																			<?php } ?>

																		</ul>



																	<?php } ?>



																</li>



															<?php } ?>

														</ul>



													<?php } ?>



												</li>

											<?php } ?>

										</ul>

									<?php } ?>



								</li>

								<?php

							}

							?>

						</ul>

					</div>

				</div>

			</div>



			<div class="kapsc_admin_main">

				<?php
				global $post;
				//print_r($wp_query);
				$orderby    = 'name';
				$order      = 'asc';
				$hide_empty = false;
				$cat_args   = array(
				'orderby'    => $orderby,
				'order'      => $order,
				'hide_empty' => $hide_empty,
				);

				$product_brands = get_terms( 'product_brand', $cat_args );
	
				if ( in_array( 'woocommerce-brands/woocommerce-brands.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) : 
					?>

				<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Brands', 'koalaapps_psc'); ?></strong></label></div>

				<div class="kapsc_admin_main_right">
					<?php

					global $post;
					$selected_brand = json_decode( get_post_meta( $post->ID, 'multi_brands', true ) );
					$selected_brand = is_array( $selected_brand ) ? $selected_brand : array();

					?>
			<select name="multi_brands[]" id="product_brands" data-placeholder="Choose Brands..." class="chose_select_brand" multiple="multiple" tabindex="-1" style="width: 100%;">;
					<?php

					foreach ( $product_brands  as $p_brand ) {
						?>
					<option value="<?php echo esc_html( $p_brand->term_id ); ?>"
						<?php
						if ( in_array( (string) $p_brand->term_id, (array) $selected_brand, true ) ) {
							echo 'selected';
						}
						?>
				><?php echo esc_html( $p_brand->name ); ?>
					</option>
						<?php
					}
					?>
				</select>	
					<p class="description"> </p>
				</div>
				<?php endif; ?>

			</div>

			  <div class="kapsc_admin_main">

				<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Tab Priority', 'koalaapps_psc'); ?></strong></label></div>

				<div class="kapsc_admin_main_right">

					<input type="number" name="kapsc_tab_pri" id="kapsc_tab_pri" min="1" max="99" value="<?php echo esc_attr(get_post_meta($post->ID, 'kapsc_tab_pri', true)) ? esc_attr(get_post_meta($post->ID, 'kapsc_tab_pri', true)) : ''; ?>">

					<p class="description"><?php echo esc_html__('Please use the priority between 1 to 99. When empty, the default priority will be 99. Please consider following WooCommerce default priorities - Description = 10, Additional information =  20 and reviews = 30.', 'koalaapps_psc'); ?></p>

				</div>

			</div>

			

			<div class="kapsc_admin_main">

				<div class="kapsc_admin_main_left"><label><strong><?php echo esc_html__('Tab/Button Title', 'koalaapps_psc'); ?></strong></label></div>

				<div class="kapsc_admin_main_right">

					<input type="text" name="kapsc_tab_title" id="kapsc_tab_title" value="<?php echo esc_attr(get_post_meta($post->ID, 'kapsc_tab_title', true)) ? esc_attr(get_post_meta($post->ID, 'kapsc_tab_title', true)) : ''; ?>">

					<p class="description"><?php echo esc_html__('Enter the Tab/Button title. If you will not enter the title then Chart Title will be applied.', 'koalaapps_psc'); ?></p>

				</div>

			</div>

			



			<?php

		}



		public function KA_Psc_Meta_Box_Save( $post_id ) {

			// Nonce for Save options
			$_nonce = isset($_POST['kapsc-fields-nonce-field']) ? sanitize_text_field(wp_unslash($_POST['kapsc-fields-nonce-field'])) : 0;

			if (isset($_POST['kapsc-fields-nonce-field']) && ! wp_verify_nonce($_nonce, 'kapsc_fields_nonce_action') ) {
				die('Failed Security Check');
			}

			// return if we're doing an auto save

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

				return;

			}



			if ( get_post_status( $post_id ) === 'auto-draft' ) {

				return;

			}



			// if our nonce isn't there, or we can't verify it, return

			if ( !isset( $_POST['kapsc-field-nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['kapsc-field-nonce']), 'kapsc_fields_nonce' ) ) {

				return;

			} 



			if ( !empty( $_POST[ 'kapsc_chart_data' ] ) ) {

				$table_meta = sanitize_meta( '', $_POST[ 'kapsc_chart_data' ], '' );



				update_post_meta( $post_id, 'kapsc_chart_data', $table_meta );

			} else {

				update_post_meta( $post_id, 'kapsc_chart_data', '');

			}



			if ( isset( $_POST['kapsc_chart_type'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_chart_type', esc_attr( sanitize_text_field($_POST['kapsc_chart_type']) ) );

			}



			if ( isset( $_POST['chartDescription'] ) ) {

				update_post_meta( intval($post_id), 'chartDescription', sanitize_meta('', $_POST['chartDescription'], ''));

			}



			if ( isset( $_POST['kapsc_country'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_country', serialize(sanitize_meta('kapsc_country', $_POST['kapsc_country'], '') ) );

			} else {

				update_post_meta( intval($post_id), 'kapsc_country', '');

			}



			if ( isset( $_POST['kapsc_products'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_products', wp_json_encode( sanitize_meta( '', wp_unslash( $_POST['kapsc_products'] ), '') ) );

			} else {

				update_post_meta( intval($post_id), 'kapsc_products', wp_json_encode( array() ) );

			}



			if ( isset( $_POST['kapsc_chart_img'] ) ) { 

				update_post_meta( intval($post_id), 'kapsc_chart_img', sanitize_text_field( $_POST['kapsc_chart_img'] ) );

			}


			if ( isset( $_POST['kapsc_categories'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_categories', wp_json_encode( sanitize_meta( '', wp_unslash( $_POST['kapsc_categories'] ), '') ) );

			} else {

				update_post_meta( intval($post_id), 'kapsc_categories', wp_json_encode( array() ) );

			}



			if ( isset( $_POST['kapsc_apply_on_all_products'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_apply_on_all_products', esc_attr( sanitize_text_field($_POST['kapsc_apply_on_all_products']) ) );

			} else {

				update_post_meta( intval($post_id), 'kapsc_apply_on_all_products', 'no' );	

			}



			if ( isset( $_POST['kapsc_tab_pri'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_tab_pri', sanitize_meta('', $_POST['kapsc_tab_pri'], ''));

			}



			if ( isset( $_POST['kapsc_tab_title'] ) ) {

				update_post_meta( intval($post_id), 'kapsc_tab_title', esc_attr( sanitize_text_field($_POST['kapsc_tab_title']) ) );

			}

			if ( isset( $_POST['multi_brands'] ) ) {

				$args = sanitize_meta( '', wp_unslash( $_POST['multi_brands'] ), '' );
			}

			if ( isset( $args ) ) {
				update_post_meta( $post_id, 'multi_brands', wp_json_encode( $args ) );
			} else {
						update_post_meta( $post_id, 'multi_brands', wp_json_encode( array() ) );
			}



		}



		public function KA_Psc_Custom_Columns( $columns) {



			$columns['chart_type'] = esc_html__( 'Type', 'koalaapps_psc' );

			

			return $columns;

		}



		public function KA_Psc_Populate_Custom_Column( $column, $post_id ) {

			$kapsc_post = get_post($post_id);



			$kapsc_type = get_post_meta($post_id, 'kapsc_chart_type', true);

			if ('chart_img' == $kapsc_type) {

				echo 'Image';

			} else {

				echo 'Table';

			}

			

		}







		public function KA_Psc_Search_Products() {



			



			if (isset($_POST['nonce']) && '' != $_POST['nonce']) {



				$nonce = sanitize_text_field( $_POST['nonce'] );

			} else {

				$nonce = 0;

			}



			if (isset($_POST['q']) && '' != $_POST['q']) {



				if ( ! wp_verify_nonce( $nonce, 'ka-psc-ajax-nonce' ) ) {



					die ( 'Failed ajax security check!');

				}

				



				$pro = sanitize_text_field( $_POST['q'] );



			} else {



				$pro = '';



			}





			$data_array = array();

			$args = array(

				'post_type' => 'product',

				'post_status' => 'publish',

				'numberposts' => -1,

				's'	=>  $pro

			);

			$pros = get_posts($args);



			if ( !empty($pros)) {



				foreach ($pros as $proo) {



					$title = ( mb_strlen( $proo->post_title ) > 50 ) ? mb_substr( $proo->post_title, 0, 49 ) . '...' : $proo->post_title;

					$data_array[] = array( $proo->ID, $title ); // array( Post ID, Post Title )

				}

			}

			

			echo wp_json_encode( $data_array );



			die();

		} // end KA_Psc_Search_Products



		public function kapsc_Settings_CB() {

			global $active_tab;
			wp_nonce_field('kapsc_fields_nonce_action', 'kapsc-fields-nonce-field');
			if ( isset( $_GET[ 'tab' ] )) {
				// Nonce for Save options
				$_nonce = isset($_POST['kapsc-fields-nonce-field']) ? sanitize_text_field(wp_unslash($_POST['kapsc-fields-nonce-field'])) : 0;

				if (isset($_POST['kapsc-fields-nonce-field']) && ! wp_verify_nonce($_nonce, 'kapsc_fields_nonce_action') ) {
					die('Failed Security Check');
				}
				 $active_tab = sanitize_text_field( $_GET[ 'tab' ] );

						

			} else {

				$active_tab = 'chart_settings';

			}



			?>

				<div class="wrap">

					<div id="icon-tools" class="icon32"></div>

					<h2> <?php echo esc_html__( 'Size Chart Settings', 'koalaapps_psc' ); ?></h2>

					<?php settings_errors(); ?>

					<h2 class="nav-tab-wrapper">



						<a href="?post_type=koalaapps_psc&page=kapsc_settings&tab=chart_settings" class="nav-tab  <?php echo esc_attr($active_tab) == 'chart_settings' ? ' nav-tab-active' : ''; ?>" > <?php esc_html_e( 'Chart Settings', 'koalaapps_psc' ); ?> </a>

					</h2>

					<form method="post" action="options.php" id="save_options_form">

						<?php


						
						if ('chart_settings' == $active_tab) {

							settings_fields( 'kapsc_chart_settings' );

							do_settings_sections( 'kapsc_chart_settings_page' );

						}

								 

							submit_button(esc_html__('Save Settings', 'koalaapps_psc'), 'primary', 'ka_psc_save_settings_btn');

						?>

						<input type="hidden" name="ks_psc_save_chang_nonce" value="<?php echo esc_html__( wp_create_nonce('ka-psc-save-chang-nonce'), 'koalaapps_psc' ); ?>">

					</form>

				</div>

			<?php

		} // kapsc_Settings_CB



		public function KA_Psc_Section_Fields_Reg() {

			add_settings_section(  

				'kapsc_chart_settings_sec', // ID used to identify this section and with which to register options  

				'',  // Title to be displayed on the administration page  

				array($this, 'kapsc_Chart_Settings_Sec_CB'), // Callback used to render the description of the section  

				'kapsc_chart_settings_page'      // Page on which to add this section of options  

			);



			add_settings_field (   

				'kapsc_chart_as_fld', // ID used to identify the field throughout the theme  

				esc_html__('Chart as:', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Chart_as_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs 				  

			);

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_chart_as_fld'  

			);

			add_settings_field (   

				'kapsc_chart_height_fld', // ID used to identify the field throughout the theme  

				esc_html__('Chart Height:', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Chart_height_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs 				  

			);

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_chart_height_fld'  

			);

			add_settings_field (   

				'kapsc_chart_width_fld', // ID used to identify the field throughout the theme  

				esc_html__('Chart Width:', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Chart_width_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs 				  

			);

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_chart_width_fld'  

			);

			add_settings_field (   

				'kapsc_chart_fonts_clr_fld', // ID used to identify the field throughout the theme  

				esc_html__('Chart & Description Color:', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_chart_fonts_clr_fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs 				  

			);

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_chart_fonts_clr_fld'  

			);



			add_settings_field (   

				'kapsc_btn_pos_fld', // ID used to identify the field throughout the theme  

				esc_html__('Button Place', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Btn_Pos_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec', // The name of the section to which this field belongs

				array('class' => 'kapsc_btn_args')			  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_btn_pos_fld'  

			);



			add_settings_field (   

				'kapsc_btn_clr_fld', // ID used to identify the field throughout the theme  

				esc_html__('Button color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Btn_Clr_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec', // The name of the section to which this field belongs

				array('class' => 'kapsc_btn_args')			  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_btn_clr_fld'  

			);



			add_settings_field (   

				'kapsc_btn_font_fld', // ID used to identify the field throughout the theme  

				esc_html__('Button Font color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'kapsc_Btn_Font_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec', // The name of the section to which this field belongs

				array('class' => 'kapsc_btn_args')			  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_btn_font_fld'  

			);



			add_settings_field (   

				'kapsc_tab_th_clr_fld', // ID used to identify the field throughout the theme  

				esc_html__('Table Header color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'Kapsc_Tab_th_Clr_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs

							  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_tab_th_clr_fld'  

			);



			add_settings_field (   

				'kapsc_tab_col_clr_fld', // ID used to identify the field throughout the theme  

				esc_html__('Table 1st Colomn color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'Kapsc_Tab_Col_Clr_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs

							  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_tab_col_clr_fld'  

			);



			add_settings_field (   

				'kapsc_odd_rows_fld', // ID used to identify the field throughout the theme  

				esc_html__('Odd Rows Color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'Kapsc_Odd_Rows_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs

							  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_odd_rows_fld'  

			);



			add_settings_field (   

				'kapsc_eve_rows_fld', // ID used to identify the field throughout the theme  

				esc_html__('Even Rows Color', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'Kapsc_Eve_Rows_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs

							  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_eve_rows_fld'  

			);



			add_settings_field (   

				'kapsc_tab_border_fld', // ID used to identify the field throughout the theme  

				esc_html__('Table Border', 'koalaapps_psc'), // The label to the left of the option interface element  

				array($this, 'Kapsc_Tab_Border_Fld_Callback'),   // The name of the function responsible for rendering the option interface  

				'kapsc_chart_settings_page', // The page on which this option will be displayed  

				'kapsc_chart_settings_sec' // The name of the section to which this field belongs

							  

			);  

			register_setting(  

				'kapsc_chart_settings',  

				'kapsc_tab_border_fld'  

			);



			



		} // end KA_Psc_Section_Fields_Reg()



		public function kapsc_Chart_Settings_Sec_CB() {

			

		}



		public function kapsc_Chart_as_Fld_Callback() {

			$kapsc_chart_as = get_option('kapsc_chart_as_fld');



			?>

				<select name="kapsc_chart_as_fld" id="kapsc_chart_as" onchange="KA_Psc_GetChart_as(this.value);">

					<option value="chart_tab" <?php echo selected('chart_tab', $kapsc_chart_as); ?>>Tab</option>

					<option value="chart_btn" <?php echo selected('chart_btn', $kapsc_chart_as); ?>>Button</option>

				</select>

				<p class="description"><?php echo esc_html__('Display the Chart as a Tab or Button. By choosing button the chart will show as a popup.', 'koalaapps_psc'); ?></p>

			<?php

		} // end kapsc_Chart_as_Fld_Callback

		public function kapsc_Chart_height_Fld_Callback() {

			?>

				<input type="number" min="0" max="100" name="kapsc_chart_height_fld" id="kapsc_chart_height_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_chart_height_fld')) ? esc_attr(get_option('kapsc_chart_height_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter chart height.This will be considered in % and by default is auto.', 'koalaapps_psc'); ?></p>

			<?php

		}

		public function kapsc_Chart_width_Fld_Callback() {

			?>

				<input type="number" min="0" max="100" name="kapsc_chart_width_fld" id="kapsc_chart_width_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_chart_width_fld')) ? esc_attr(get_option('kapsc_chart_width_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter chart width.This will be considered in % and by default is 70%.', 'koalaapps_psc'); ?></p>

			<?php

		}

		public function kapsc_chart_fonts_clr_fld_Callback() {

			?>

				<input type="text" name="kapsc_chart_fonts_clr_fld" id="kapsc_chart_fonts_clr_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_chart_fonts_clr_fld')) ? esc_attr(get_option('kapsc_chart_fonts_clr_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name to change the Fonts Color in the Chart and Chart Description.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function kapsc_Btn_Pos_Fld_Callback() {

			$kapsc_btn_pos = get_option('kapsc_btn_pos_fld');



			?>

				<select name="kapsc_btn_pos_fld" id="kapsc_btn_pos_fld" class="kapsc_btn_args">

					

					<option value="btn_AP" <?php echo selected('chart_btn', $kapsc_btn_pos); ?>>After Price</option>



					<option value="btn_ASD" <?php echo selected('btn_ASD', $kapsc_btn_pos); ?>>After Short Description</option>



					<option value="btn_AATC" <?php echo selected('btn_AATC', $kapsc_btn_pos); ?>>After Add to Cart</option>

					

					<option value="btn_APM" <?php echo selected('btn_APM', $kapsc_btn_pos); ?>>After Product Meta</option>



				</select>

				<p class="description"><?php echo esc_html__('Choose the postion of button where do you want to display.', 'koalaapps_psc'); ?></p>



			<?php

		} // end kapsc_Btn_Pos_Fld_Callback



		public function kapsc_Btn_Clr_Fld_Callback() {

			?>

				<input type="text" name="kapsc_btn_clr_fld" id="kapsc_btn_clr_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_btn_clr_fld')) ? esc_attr(get_option('kapsc_btn_clr_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function kapsc_Btn_Font_Fld_Callback() {

			?>

				<input type="text" name="kapsc_btn_font_fld" id="kapsc_btn_font_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_btn_font_fld')) ? esc_attr(get_option('kapsc_btn_font_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function Kapsc_Tab_th_Clr_Fld_Callback() {

			?>

				<input type="text" name="kapsc_tab_th_clr_fld" id="kapsc_tab_th_clr_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_tab_th_clr_fld')) ? esc_attr(get_option('kapsc_tab_th_clr_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function Kapsc_Tab_Col_Clr_Fld_Callback() {

			?>

				<input type="text" name="kapsc_tab_col_clr_fld" id="kapsc_tab_col_clr_fld" class="kapsc_field_length" value="<?php echo esc_attr(get_option('kapsc_tab_col_clr_fld')) ? esc_attr(get_option('kapsc_tab_col_clr_fld')) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function Kapsc_Tab_Border_Fld_Callback() {

			$border = get_option('kapsc_tab_border_fld');

			?>

				<input type="checkbox" name="kapsc_tab_border_fld" id="kapsc_tab_border_fld" class="kapsc_field_length" value="yes" <?php echo checked('yes', $border); ?>>

				<p class="description"><?php echo esc_html__('Enable if do you want to use table border as a separator.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function Kapsc_Odd_Rows_Fld_Callback() {

			$odd_rows = get_option('kapsc_odd_rows_fld');

			?>

				<input type="text" name="kapsc_odd_rows_fld" id="kapsc_odd_rows_fld" value="<?php echo esc_attr($odd_rows) ? esc_attr($odd_rows) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}



		public function Kapsc_Eve_Rows_Fld_Callback() {

			$eve_rows = get_option('kapsc_eve_rows_fld');

			?>

				<input type="text" name="kapsc_eve_rows_fld" id="kapsc_eve_rows_fld" value="<?php echo esc_attr($eve_rows) ? esc_attr($eve_rows) : ''; ?>">

				<p class="description"><?php echo esc_html__('Enter either HEX color value or color name.', 'koalaapps_psc'); ?></p>

			<?php

		}

		

		



	} // end class



	new KA_Pro_Size_Charts_Admin();

}
