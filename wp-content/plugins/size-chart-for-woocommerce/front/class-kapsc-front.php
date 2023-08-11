<?php

if (!class_exists('KA_Pro_Size_Charts_Front')) {

	

	class KA_Pro_Size_Charts_Front extends KA_Pro_Size_Charts {

		

		public function __construct() {

			add_action('wp_footer' , array($this , 'KA_Psc_Front_Assets') );
			


			if ('chart_tab' == get_option('kapsc_chart_as_fld')) {

				// add tab on products

				add_filter( 'woocommerce_product_tabs', array( $this, 'KA_Psc_Product_Tabs' ) );	



			} elseif ('chart_btn' == get_option('kapsc_chart_as_fld')) {

				if ('btn_AP' == get_option('kapsc_btn_pos_fld')) {

					add_action('woocommerce_single_product_summary', array($this,'KA_Psc_Custom_Button'), 11);

				} elseif ('btn_ASD' == get_option('kapsc_btn_pos_fld')) {

					add_action('woocommerce_single_product_summary', array($this,'KA_Psc_Custom_Button'), 30);

				} elseif ('btn_AATC' == get_option('kapsc_btn_pos_fld')) {

					add_action('woocommerce_after_add_to_cart_button', array($this,'KA_Psc_Custom_Button'));

				} elseif ('btn_APM' == get_option('kapsc_btn_pos_fld')) {

					add_action('woocommerce_product_meta_end', array($this,'KA_Psc_Custom_Button'));

				}

				

			} else {

				add_filter( 'woocommerce_product_tabs', array( $this, 'KA_Psc_Product_Tabs' ) );

			}

			



		} // end Construct



		public function KA_Psc_Front_Assets() {



			// Upload Font-Awesome 4

			wp_enqueue_style( 'Font-Awesome', plugins_url( '../assets/css/fontawesome.css', __FILE__ ), false, '1.0' );



			wp_enqueue_style( 'kapsc_front', plugins_url( '../assets/css/kapsc_front.css', __FILE__ ), false, '1.0' );





			wp_enqueue_script('kapsc_front' , plugins_url('../assets/js/kapsc_front.js', __FILE__) , array('jquery') , '1.0', false);

		}



		public function kapsc_Get_Chart_PT( $kapsc_charts = '') {

			$args = array(

				'post_per_page' => -1,

				'numberposts'	=> -1,

				'post_type'     => 'koalaapps_psc',

				'post_status'   => 'publish',

				'orderby' => 'menu_order',

				'order' => 'ASC'

			);

			$kapsc_charts = get_posts( $args );

			return $kapsc_charts;

		}



		public function KA_Psc_Product_Tabs( $tabs ) {

		

			global $post, $product;

			$kapsc_charts = $this->kapsc_Get_Chart_PT();



			if ( count( $kapsc_charts ) > 0 ) {

				foreach ( $kapsc_charts as $chart ) {



					$kapsc_chart_type = get_post_meta(intval($chart->ID), 'kapsc_chart_type', true);





					$kapsc_chart_data = get_post_meta(intval($chart->ID), 'kapsc_chart_data', true );



					$kapsc_countries = unserialize(get_post_meta(intval($chart->ID), 'kapsc_country', true));



					$applied_on_all_products = get_post_meta($chart->ID, 'kapsc_apply_on_all_products', true);



					$kapsc_products = json_decode( get_post_meta( $chart->ID, 'kapsc_products', true ) );

					

					$kapsc_categories = json_decode( get_post_meta( $chart->ID, 'kapsc_categories', true ) );

					$kapsc_slected_brands = json_decode( get_post_meta( $chart->ID, 'multi_brands', true ) );

					//print_r($kapsc_kapsc_slected_brands);

					$kapsc_chart_img = get_post_meta(intval($chart->ID), 'kapsc_chart_img', true);



					$kapsc_tab_pri = get_post_meta(intval($chart->ID), 'kapsc_tab_pri', true);



					$kapsc_tab_title = get_post_meta(intval($chart->ID), 'kapsc_tab_title', true);



					//country

					if (!empty($_SERVER['REMOTE_ADDR'])) {

						$ip = sanitize_meta('', $_SERVER['REMOTE_ADDR'], '');

					} else {

						$ip = '';

					}

					$location = WC_Geolocation::geolocate_ip();
					$country  = $location['country'];

					if (!empty($country)) {

						$curr_country = $country;

					} else {

						$country = '';

						$curr_country = $country;

					}

					



					$istrue = false;

					$iscountry = false;



					if (!empty($kapsc_countries) && in_array($curr_country, $kapsc_countries)) {

						$iscountry = true;



					} elseif (empty($kapsc_countries)) {



						$iscountry = true;



					} else {



						$iscountry = false;



					}



					if ( 'yes' == $applied_on_all_products) {



						$istrue = true;



					} elseif (is_array($kapsc_products) && in_array($product->get_id(), $kapsc_products)) {



						$istrue = true;



					}

					//Products

					if ($istrue && $iscountry) {



						$tabs[ 'kapsc-chart-id-' . $chart->ID ] = array(

						'title'         => $kapsc_tab_title ? $kapsc_tab_title : $chart->post_title,

						'priority'      => $kapsc_tab_pri ? $kapsc_tab_pri : 99,

						'callback'      => array( $this, 'kapsc_Tab_Content' ),

						'kapsc_chart_id' => $chart->ID

						);

					}

					//Brands Match

	

					if (!empty($kapsc_slected_brands) && !$istrue && $iscountry) {



						foreach ($kapsc_slected_brands as $cslected_brandsat) {



							if ( has_term( $cslected_brandsat , 'product_brand', $product->get_id() ) ) {

								$tabs[ 'kapsc-chart-' . $chart->ID ] = array(

									'title'		=> $kapsc_tab_title ? $kapsc_tab_title : $chart->post_title,

									'priority'	=> $kapsc_tab_pri ? $kapsc_tab_pri : 99,

									'callback'	=> array( $this, 'kapsc_Tab_Content' ),

									'kapsc_chart_id' => $chart->ID

								);

							}



						}

					}

					// Categories

					if (!empty($kapsc_categories) && !$istrue && $iscountry) {



						foreach ($kapsc_categories as $cat) {



							if ( has_term( $cat , 'product_cat', $product->get_id() ) ) {

								$tabs[ 'kapsc-chart-' . $chart->ID ] = array(

									'title'		=> $kapsc_tab_title ? $kapsc_tab_title : $chart->post_title,

									'priority'	=> $kapsc_tab_pri ? $kapsc_tab_pri : 99,

									'callback'	=> array( $this, 'kapsc_Tab_Content' ),

									'kapsc_chart_id' => $chart->ID

								);

							}



						}

					}

				} // end foreach

				return $tabs;

			} // end if



			

		} // end KA_Psc_Product_Tabs



		public function kapsc_Tab_Content( $key, $tab) {



			if ( !isset( $tab[ 'kapsc_chart_id' ] ) ) {

				return;

			}



			$c_id = $tab[ 'kapsc_chart_id' ];

			$kapsc_chart_type = get_post_meta( $c_id, 'kapsc_chart_type', true );

			$kapsc_chart_data = get_post_meta( $c_id, 'kapsc_chart_data', true );

			$kapsc_chart_img = get_post_meta( $c_id, 'kapsc_chart_img', true );

			$kapsc_chart_desc = get_post_meta( $c_id, 'chartDescription', true);

			if ('chart_img' == $kapsc_chart_type) {

				if (!empty($kapsc_chart_img)) {

					if (!empty($kapsc_chart_desc)) {

						$this->Ka_Psc_Chart_Desc($kapsc_chart_desc);

					}

					$this->kapsc_Chart_Image_CB($kapsc_chart_img);

				}

			} else {

				if (!empty($kapsc_chart_data)) {

					if (!empty($kapsc_chart_desc)) {

						$this->Ka_Psc_Chart_Desc($kapsc_chart_desc);

					}

					$this->KA_Psc_Product_Table($kapsc_chart_data);

				} 

			}   

			

		}



		public function KA_Psc_Custom_Button() {



			global $post, $product;

			$kapsc_charts = $this->kapsc_Get_Chart_PT();

			$kapsc_btn_clr = get_option('kapsc_btn_clr_fld');

			$kapsc_chart_height_fld = get_option('kapsc_chart_height_fld');
			$kapsc_chart_width_fld  = get_option('kapsc_chart_width_fld');

			$kapsc_btn_font_clr = get_option('kapsc_btn_font_fld');



			$th_clr = get_option('kapsc_tab_th_clr_fld');

			$col_clr = get_option('kapsc_tab_col_clr_fld');

			$chart_fonts_clr = get_option('kapsc_chart_fonts_clr_fld');


			$kapsc_tab_border = get_option('kapsc_tab_border_fld');

			$odd_rows_c = get_option('kapsc_odd_rows_fld');

			$eve_rows_c = get_option('kapsc_eve_rows_fld');

			if ( count( $kapsc_charts ) > 0 ) {

				foreach ( $kapsc_charts as $chart ) {



					$kapsc_chart_type = get_post_meta(intval($chart->ID), 'kapsc_chart_type', true);



					$kapsc_chart_data = get_post_meta(intval($chart->ID), 'kapsc_chart_data', true );



					$kapsc_countries = unserialize(get_post_meta(intval($chart->ID), 'kapsc_country', true));



					$applied_on_all_products = get_post_meta($chart->ID, 'kapsc_apply_on_all_products', true);



					$kapsc_products = json_decode( get_post_meta( $chart->ID, 'kapsc_products', true ) );

					

					$kapsc_categories = json_decode( get_post_meta( $chart->ID, 'kapsc_categories', true ) );

					$kapsc_slected_brands = json_decode( get_post_meta( $chart->ID, 'multi_brands', true ) );

					

					$kapsc_image = get_post_meta(intval($chart->ID), 'kapsc_chart_img', true);



					$kapsc_tab_title = get_post_meta(intval($chart->ID), 'kapsc_tab_title', true);
					
					//country

					$location = WC_Geolocation::geolocate_ip();
					$country  = $location['country'];



					if (!empty($country)) {

						$curr_country = $country;

					} else {

						$country = '';

						$curr_country = $country;

					}

					



					$istrue = false;

					$iscountry = false;



					if (!empty($kapsc_countries) && in_array($curr_country, $kapsc_countries)) {

						$iscountry = true;



					} elseif (empty($kapsc_countries)) {



						$iscountry = true;





					} else {



						$iscountry = false;



					}



					if ( 'yes' == $applied_on_all_products) {



						$istrue = true;



					} elseif (is_array($kapsc_products) && in_array($product->get_id(), $kapsc_products)) {



						$istrue = true;





					}



					// Product

					if ($istrue && $iscountry) {

						

						$kapsc_chart_type = get_post_meta( $chart->ID, 'kapsc_chart_type', true );

						$kapsc_chart_data = get_post_meta( $chart->ID, 'kapsc_chart_data', true );

						$kapsc_chart_img = get_post_meta( $chart->ID, 'kapsc_chart_img', true );

						$kapsc_chart_desc = get_post_meta( $chart->ID, 'chartDescription', true);



						$data = json_decode($kapsc_chart_data);

						

						if ('chart_img' == $kapsc_chart_type) {

							if (!empty($kapsc_chart_img)) {

								if (!empty($kapsc_btn_clr)) {

									$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

									

								}

								if (!empty($kapsc_btn_font_clr)) {



									$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

									

								}

								if (!empty($kapsc_chart_height_fld)) {



									$this->KA_Psc_height_chart($kapsc_chart_height_fld);

									

								} 

								if (!empty($kapsc_chart_width_fld)) {



									$this->KA_Psc_width_chart($kapsc_chart_width_fld);

									

								}

								if (!empty($kapsc_tab_title)) {

									

									echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';



								} else {

									 

									echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

									

									

								}



								echo '<div class="kapsc-popup-overlay kapsc_hid_OL"><div class ="popuptext" id="myPopup-' . intval($chart->ID) . '">';

								?>

									<span class="close">&times;</span>

										<?php if (!empty($kapsc_chart_desc)) { ?>

											<div id="chart_des" class="popup-content">

												<?php echo wp_kses_post($kapsc_chart_desc); ?>

											</div>

										<?php } ?>

										<img class="popup-content" src="<?php echo esc_url($kapsc_chart_img); ?>">
										<!-- end div of popuptext class -->
									</div>
									<!-- end div of kapsc-popup-overlay class -->
								</div>

								<?php

							}

							

						} else {

							if (!empty($kapsc_btn_clr)) {

								$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

									

							}

							if (!empty($kapsc_btn_font_clr)) {



								$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

							}

							if (!empty($kapsc_chart_height_fld)) {



								$this->KA_Psc_height_chart($kapsc_chart_height_fld);

								

							}
							if (!empty($kapsc_chart_width_fld)) {



								$this->KA_Psc_width_chart($kapsc_chart_width_fld);

								

							}

							if (!empty($kapsc_tab_title)) {

								



								echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';

										

							

							} else {

								

								echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

								

							} 

							

			
							
							echo	'<div class="kapsc-popup-overlay kapsc_hid_OL"><div class="popuptext" id="myPopup-' . intval($chart->ID) . '" >';

							if ('yes' == esc_attr($kapsc_tab_border)) {

								

								$this->KA_Psc_Tab_Border();

							}

							if (!empty( esc_attr( $chart_fonts_clr ) ) ) {

								

								$this->KA_Psc_Fonts_Clr( $chart_fonts_clr );

							}

							



							?>

									

									<span class="close">&times;</span>

									<?php if (!empty($kapsc_chart_desc)) { ?>

											<div id="chart_des" class="popup-content">

												<?php echo wp_kses_post($kapsc_chart_desc); ?>

											</div>

										<?php } ?>

									<table class="popup-content tab_bor">

										

										<thead>

											<tr>

												<?php foreach ($data[0] as $col) : ?>

													<th>

														<?php echo esc_attr($col); ?>

													</th>

												<?php endforeach; ?>

											</tr>

											<?php 

											if (!empty($th_clr)) {

												$this->KA_Psc_Tab_1st_Row_Clr($th_clr);

											} 

											?>

										</thead>



										<tbody>

											<?php foreach ($data as $id => $row) : ?>

												<?php 

												if (0 == $id) {

													continue;} 

												?>

												<tr>

													<?php foreach ($row as $col) : ?>

														<td>

															<?php echo esc_attr(str_replace('"', '&quot;', $col)); ?>

														</td>

													<?php endforeach; ?>

												</tr>

											<?php endforeach; ?>

										</tbody>

										<?php

										if (!empty($col_clr)) {

											$this->KA_Psc_Tab_1st_Col_clr($col_clr);

										} 

										?>

									</table>
									<!-- end div popuptext class -->
								</div>
								<!-- end div kapsc-popup-overlay class -->
							</div>

							<?php



							if (!empty($odd_rows_c)) {

								$this->KA_Psc_Tab_Odd_Rows_Clr($odd_rows_c);



							}



							if (!empty($eve_rows_c)) {

								$this->KA_Psc_Tab_Even_Rows_Clr($eve_rows_c);

							}

						}

						

					}

					//Brands Match

					if (!empty($kapsc_slected_brands) && !$istrue && $iscountry) {



						foreach ($kapsc_slected_brands as $slected_brands) {


							if ( has_term( $slected_brands , 'product_brand', $product->get_id() ) ) {

								$kapsc_chart_type = get_post_meta( $chart->ID, 'kapsc_chart_type', true );

								$kapsc_chart_data = get_post_meta( $chart->ID, 'kapsc_chart_data', true );

								$kapsc_chart_img = get_post_meta( $chart->ID, 'kapsc_chart_img', true );

								$kapsc_chart_desc = get_post_meta( $chart->ID, 'chartDescription', true);

								$data = json_decode($kapsc_chart_data);

						

								if ('chart_img' == $kapsc_chart_type) {

									if (!empty($kapsc_chart_img)) {

										if (!empty($kapsc_btn_clr)) {

											$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

												

										}

										if (!empty($kapsc_btn_font_clr)) {

											$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

										}

										if (!empty($kapsc_chart_height_fld)) {

											$this->KA_Psc_height_chart($kapsc_chart_height_fld);

										}

										if (!empty($kapsc_chart_width_fld)) {



											$this->KA_Psc_width_chart($kapsc_chart_width_fld);

											

										}

										if (!empty($kapsc_tab_title)) {

											

											echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';

												

											

										} else {

											

											echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

										}



										echo '<div class="kapsc-popup-overlay kapsc_hid_OL"><div class="popuptext" id="myPopup-' . intval($chart->ID) . '">';

										if ('yes' == esc_attr($kapsc_tab_border)) {

								

											$this->KA_Psc_Tab_Border();

										}

										if (!empty( esc_attr( $chart_fonts_clr ) ) ) {

											
											
											$this->KA_Psc_Fonts_Clr( $chart_fonts_clr );

										}

										?>

												<span class="close">&times;</span>

												<?php if (!empty($kapsc_chart_desc)) { ?>

													<div id="chart_des" class="popup-content">

														<?php echo wp_kses_post($kapsc_chart_desc); ?>

													</div>

												<?php } ?>

												<img class="popup-content" src="<?php echo esc_url($kapsc_chart_img); ?>">
												<!-- end div popuptext class -->
											</div>
											<!-- end div kapsc-popup-overlay class -->
										</div>

										<?php

									}

									

								} else {

									if (!empty($kapsc_btn_clr)) {

										$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

											

									}

									if (!empty($kapsc_btn_font_clr)) {

										$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

									}

									if (!empty($kapsc_chart_height_fld)) {

										$this->KA_Psc_height_chart($kapsc_chart_height_fld);

									}

									if (!empty($kapsc_chart_width_fld)) {



										$this->KA_Psc_width_chart($kapsc_chart_width_fld);

										

									}

									if (!empty($kapsc_tab_title)) {

										



										echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';

									

									} else {

										

										echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

										

									}



									echo '<div class="kapsc-popup-overlay kapsc_hid_OL"><div class="popuptext" id="myPopup-' . intval($chart->ID) . '">';

									?>

											<span class="close">&times;</span>

											<?php if (!empty($kapsc_chart_desc)) { ?>

												<div id="chart_des" class="popup-content">

													<?php echo wp_kses_post($kapsc_chart_desc); ?>

												</div>

											<?php } ?>

											<table class="popup-content tab_bor">

												

												<thead>

													<tr>

														<?php foreach ($data[0] as $col) : ?>

															<th>

																<?php echo esc_attr($col); ?>

															</th>

														<?php endforeach; ?>

													</tr>

													<?php 

													if (!empty($th_clr)) {

														$this->KA_Psc_Tab_1st_Row_Clr($th_clr);

													} 

													?>

												</thead>



												<tbody>

													<?php foreach ($data as $id => $row) : ?>

														<?php 

														if (0 == $id) {

															continue;} 

														?>

														<tr>

															<?php foreach ($row as $col) : ?>

																<td>

																	<?php echo esc_attr(str_replace('"', '&quot;', $col)); ?>

																</td>

															<?php endforeach; ?>

														</tr>

													<?php endforeach; ?>

												</tbody>

												<?php

												if (!empty($col_clr)) {

													$this->KA_Psc_Tab_1st_Col_clr($col_clr);

												} 

												?>

											</table>

										</div>
										<!-- end div popup-overlay -->
									</div>
								

									<?php

									if (!empty($odd_rows_c)) {

										$this->KA_Psc_Tab_Odd_Rows_Clr($odd_rows_c);



									}



									if (!empty($eve_rows_c)) {

										$this->KA_Psc_Tab_Even_Rows_Clr($eve_rows_c);

									}

								}								

							}



						}

					}



					// Categories

					if (!empty($kapsc_categories) && !$istrue && $iscountry) {



						foreach ($kapsc_categories as $cat) {



							if ( has_term( $cat , 'product_cat', $product->get_id() ) ) {

								$kapsc_chart_type = get_post_meta( $chart->ID, 'kapsc_chart_type', true );

								$kapsc_chart_data = get_post_meta( $chart->ID, 'kapsc_chart_data', true );

								$kapsc_chart_img = get_post_meta( $chart->ID, 'kapsc_chart_img', true );

								$kapsc_chart_desc = get_post_meta( $chart->ID, 'chartDescription', true);

								$data = json_decode($kapsc_chart_data);

						

								if ('chart_img' == $kapsc_chart_type) {

									if (!empty($kapsc_chart_img)) {

										if (!empty($kapsc_btn_clr)) {

											$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

												

										}

										if (!empty($kapsc_btn_font_clr)) {

											$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

										}

										if (!empty($kapsc_chart_height_fld)) {

											$this->KA_Psc_height_chart($kapsc_chart_height_fld);

										}

										if (!empty($kapsc_chart_width_fld)) {



											$this->KA_Psc_width_chart($kapsc_chart_width_fld);

											

										}

										if (!empty($kapsc_tab_title)) {

											

											echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';

												

											

										} else {

											

											echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

										}



										echo '<div class="kapsc-popup-overlay kapsc_hid_OL"><div class="popuptext" id="myPopup-' . intval($chart->ID) . '">';

										if ('yes' == esc_attr($kapsc_tab_border)) {

								

											$this->KA_Psc_Tab_Border();

										}

										if (!empty( esc_attr( $chart_fonts_clr ) ) ) {

											
											
											$this->KA_Psc_Fonts_Clr( $chart_fonts_clr );

										}

										?>

												<span class="close">&times;</span>

												<?php if (!empty($kapsc_chart_desc)) { ?>

													<div id="chart_des" class="popup-content">

														<?php echo wp_kses_post($kapsc_chart_desc); ?>

													</div>

												<?php } ?>

												<img class="popup-content" src="<?php echo esc_url($kapsc_chart_img); ?>">
												<!-- end div popuptext class -->
											</div>
											<!-- end div kapsc-popup-overlay class -->
										</div>

										<?php

									}

									

								} else {

									if (!empty($kapsc_btn_clr)) {

										$this->KA_Psc_Btn_BgClr($kapsc_btn_clr);

											

									}

									if (!empty($kapsc_btn_font_clr)) {

										$this->KA_Psc_Btn_FClr($kapsc_btn_font_clr);

									}

									if (!empty($kapsc_chart_height_fld)) {

										$this->KA_Psc_height_chart($kapsc_chart_height_fld);

									}

									if (!empty($kapsc_chart_width_fld)) {



										$this->KA_Psc_width_chart($kapsc_chart_width_fld);

										

									}

									if (!empty($kapsc_tab_title)) {

										



										echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($kapsc_tab_title, 'koalaapps_psc') . '</button>';

									

									} else {

										

										echo '<button name="kapsc_pop_btn" type="button" id="kapsc_pop_btn-' . intval($chart->ID) . '" value="' . intval($chart->ID) . '" class="kapsc_pop_btn button popup" onclick="kapsc_chartpopup(' . intval($chart->ID) . ');">' . esc_html__($chart->post_title, 'koalaapps_psc') . '</button>';

										

									}



									echo '<div class="kapsc-popup-overlay kapsc_hid_OL"><div class="popuptext" id="myPopup-' . intval($chart->ID) . '">';

									?>

											<span class="close">&times;</span>

											<?php if (!empty($kapsc_chart_desc)) { ?>

												<div id="chart_des" class="popup-content">

													<?php echo wp_kses_post($kapsc_chart_desc); ?>

												</div>

											<?php } ?>

											<table class="popup-content tab_bor">

												

												<thead>

													<tr>

														<?php foreach ($data[0] as $col) : ?>

															<th>

																<?php echo esc_attr($col); ?>

															</th>

														<?php endforeach; ?>

													</tr>

													<?php 

													if (!empty($th_clr)) {

														$this->KA_Psc_Tab_1st_Row_Clr($th_clr);

													} 

													?>

												</thead>



												<tbody>

													<?php foreach ($data as $id => $row) : ?>

														<?php 

														if (0 == $id) {

															continue;} 

														?>

														<tr>

															<?php foreach ($row as $col) : ?>

																<td>

																	<?php echo esc_attr(str_replace('"', '&quot;', $col)); ?>

																</td>

															<?php endforeach; ?>

														</tr>

													<?php endforeach; ?>

												</tbody>

												<?php

												if (!empty($col_clr)) {

													$this->KA_Psc_Tab_1st_Col_clr($col_clr);

												} 

												?>

											</table>

										</div>
										<!-- end div popup-overlay -->
									</div>
								

									<?php

									if (!empty($odd_rows_c)) {

										$this->KA_Psc_Tab_Odd_Rows_Clr($odd_rows_c);



									}



									if (!empty($eve_rows_c)) {

										$this->KA_Psc_Tab_Even_Rows_Clr($eve_rows_c);

									}

								}								

							}



						}

					} // end selected CATEGORIES

					

				} // end foreach

				

			}

			

		}



		public function kapsc_Chart_Image_CB( $kapsc_chart_img) {

			$chrt_img = $kapsc_chart_img;

			?>

				<div>

					<img src="<?php echo esc_url($chrt_img); ?>">

				</div>

			<?php

		}



		public function KA_Psc_Product_Table( $kapsc_chart_data) {

			$data = json_decode($kapsc_chart_data);

			$th_clr = get_option('kapsc_tab_th_clr_fld');

			$col_clr = get_option('kapsc_tab_col_clr_fld');

			$odd_rows_c = get_option('kapsc_odd_rows_fld');

			$eve_rows_c = get_option('kapsc_eve_rows_fld');

			$chart_fonts_clr = get_option('kapsc_chart_fonts_clr_fld');



			$tab_border = get_option('kapsc_tab_border_fld');

			?>

				<div class="responsive">

					<?php

					if ('yes' == esc_attr($tab_border)) {

						$this->KA_Psc_Tab_Border();

					}
					if (!empty( esc_attr( $chart_fonts_clr ) ) ) {

								

						$this->KA_Psc_Fonts_Clr( $chart_fonts_clr );

					}



					?>

					<table class="tab_bor">

						<thead>

							<tr>

								<?php foreach ($data[0] as $col) : ?>

									<th>

										<?php echo esc_attr($col); ?>

									</th>

									

								<?php endforeach; ?>

							</tr>

							<?php 

							if (!empty($th_clr)) {

								$this->KA_Psc_Tab_1st_Row_Clr($th_clr);

							} 

							?>

						</thead>



						<tbody>

							<?php foreach ($data as $id => $row) : ?>

								<?php 

								if (0 == $id) {

									continue;} 

								?>

								<tr>

									<?php foreach ($row as $col) : ?>

										<td>

											<?php echo esc_attr(str_replace('"', '&quot;', $col)); ?>

										</td>

									<?php endforeach; ?>

								</tr>

							<?php endforeach; ?>

						</tbody>

						<?php 

						if (!empty($col_clr)) {

							$this->KA_Psc_Tab_1st_Col_clr($col_clr);

						}	

						?>

					</table>



				</div>

			<?php

			if (!empty($odd_rows_c)) {

				$this->KA_Psc_Tab_Odd_Rows_Clr($odd_rows_c);



			}



			if (!empty($eve_rows_c)) {

				$this->KA_Psc_Tab_Even_Rows_Clr($eve_rows_c);

			}

		} //end  KA_Psc_Product_Table



		public function Ka_Psc_Chart_Desc( $kapsc_chart_desc) {

			?>

				<div id="chart_des">

					<?php echo wp_kses_post($kapsc_chart_desc); ?>

				</div>

			<?php



		}



		public function KA_Psc_Tab_1st_Col_clr( $col_clr) {

			?>

				<style>

					table.tab_bor tbody td:nth-child(1) {

						background-color: <?php echo esc_attr($col_clr); ?> !important;



					}

				</style>

			<?php

		}



		public function KA_Psc_Tab_1st_Row_Clr( $th_clr) {

			?>

				<style>

					table.tab_bor thead th {

						background-color: <?php echo esc_attr($th_clr); ?> !important;

					}

				</style>

			<?php

		}



		public function KA_Psc_Tab_Odd_Rows_Clr( $odd_rows_c) {

			?>

				<style>

					table:not( .has-background ) tbody td {

						background-color: initial;

					}

					table.tab_bor tbody tr:nth-child(odd) {

						 background-color: <?php echo esc_attr($odd_rows_c); ?>;

					 }

				</style>

			<?php

		}



		public function KA_Psc_Tab_Even_Rows_Clr( $tr_clr) {

			?>

				<style>

					table:not( .has-background ) tbody tr:nth-child(2n) td {

						background-color: initial;

					}

					table.tab_bor tbody tr:nth-child(even) {

						background-color: <?php echo esc_attr($tr_clr); ?>;

					}

				</style>

			<?php

		}



		public function KA_Psc_Tab_Odd_Cols_Clr( $td_clr) {

			?>

				<style>

					div.responsive table tbody tr td:nth-child(odd) {

						color: <?php echo esc_attr($td_clr); ?> ;

					}

				</style>

			<?php

		}



		public function KA_Psc_Tab_Even_Cols_Clr( $td_clr) {

			?>

				<style>

					div.responsive table tbody tr td:nth-child(even) {

						color: <?php echo esc_attr($td_clr); ?> ;

					}

				</style>

			<?php

		}



		public function KA_Psc_Btn_BgClr( $kapsc_btn_clr) {

			?>

				<style>

					.popup {

						background-color: <?php echo esc_attr($kapsc_btn_clr); ?> !important;

					}

				</style>

			<?php

		}



		public function KA_Psc_Btn_FClr( $kapsc_btn_font_clr) {

			?>

				<style>

					.popup {

						color: <?php echo esc_attr($kapsc_btn_font_clr); ?> !important;

					}

				</style>

			<?php

		}

		public function KA_Psc_height_chart( $height) {
			
			?>
				<style>

					.popuptext {

						height: <?php echo esc_attr($height); ?>% !important;

					}

				</style>
			<?php
		}

		public function KA_Psc_width_chart( $width) {
			
			?>
				<style>

					.popuptext {

						width: <?php echo esc_attr($width); ?>% !important;

					}

				</style>
			<?php
		}

		public function KA_Psc_Fonts_Clr( $fnt_clr ) {
			
			?>
				<style>

					.popup-content {

						color : <?php echo esc_attr($fnt_clr); ?> !important;

					}

				</style>
			<?php
		}

		public function KA_Psc_Tab_Border() {

			?>

				<style>

					div.responsive table.tab_bor th, table.tab_bor td {

						  border: 1px solid #ddd;

					}

					div.popuptext table.tab_bor th, table.tab_bor td {

						  border: 1px solid #ddd;

					}

				</style>

			<?php

		}



	} // end class KA_Pro_Size_Charts_Front





	new KA_Pro_Size_Charts_Front();

}
