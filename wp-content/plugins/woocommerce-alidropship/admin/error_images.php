<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Error_Images {
	protected static $settings;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_init', array( $this, 'create_table' ) );
		add_action( 'admin_init', array( $this, 'empty_list' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 17 );
		add_action( 'wp_ajax_vi_wad_download_error_product_images', array( $this, 'download_error_product_images' ) );
		add_action( 'wp_ajax_vi_wad_delete_error_product_images', array( $this, 'delete_error_product_images' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_head', array( $this, 'menu_product_count' ), 999 );
		add_action( 'wp_ajax_wad_search_product_failed_images', array( $this, 'search_product' ) );
	}

	public function search_product() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-error-images' ) ) ) {
			return;
		}
		$keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';
		if ( empty( $keyword ) ) {
			die();
		}
		$found_products = array();
		$product_ids    = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_products_ids( $keyword );
		foreach ( $product_ids as $product_id ) {
			$found_products[] = array(
				'id'   => $product_id,
				'text' => "(#{$product_id}) " . get_the_title( $product_id )
			);
		}
		wp_send_json( $found_products );
	}

	public function empty_list() {
		global $wpdb;
		$page = isset( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : '';
		if ( ! empty( $_GET['vi_wad_empty_error_images'] ) && $page === 'woocommerce-alidropship-error-images' ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '';
			if ( wp_verify_nonce( $nonce ) ) {
				$wpdb->query( "DELETE from {$wpdb->prefix}vi_wad_error_product_images" );
				wp_safe_redirect( admin_url( "admin.php?page={$page}" ) );
				exit();
			}
		}
	}

	public function menu_product_count() {
		global $submenu;
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();//show_menu_count may be changed after saving settings
		if ( isset( $submenu['woocommerce-alidropship'] ) && in_array( 'failed_images', self::$settings->get_params( 'show_menu_count' ) ) ) {
			// Add count if user has access.
			if ( apply_filters( 'woo_aliexpress_dropship_error_images_count_in_menu', true ) || current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-error-images' ) ) ) {
				$product_count = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_rows( 0, 0, true );
				foreach ( $submenu['woocommerce-alidropship'] as $key => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Failed Images', 'Admin menu name', 'woocommerce-alidropship' ) ) ) {
						$submenu['woocommerce-alidropship'][ $key ][0] .= ' <span class="update-plugins count-' . esc_attr( $product_count ) . '"><span class="' . self::set( 'error-images-count' ) . '">' . number_format_i18n( $product_count ) . '</span></span>'; // WPCS: override ok.
						break;
					}
				}
			}
		}
	}

	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	public function create_table() {
		if ( ! get_option( 'vi_wad_create_table_of_error_product_images' ) ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::create_table();
			update_option( 'vi_wad_create_table_of_error_product_images', time() );
		}
	}

	public function download_error_product_images() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-error-images' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$id       = isset( $_POST['item_id'] ) ? wp_slash( $_POST['item_id'] ) : '';
		$response = array(
			'status'  => 'error',
			'message' => 'Error',
		);
		if ( $id ) {
			$data = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_row( $id );
			if ( count( $data ) ) {
				$product_id = $data['product_id'];
				$post       = get_post( $product_id );
				if ( $post && $post->post_type === 'product' ) {
					if ( $data['set_gallery'] != 2 || ( ! self::$settings->get_params( 'use_external_image' ) && self::$settings->get_params( 'download_description_images' ) ) ) {
						$thumb_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::download_image( $image_id, html_entity_decode( $data['image_src'] ), $product_id );
						if ( $thumb_id && ! is_wp_error( $thumb_id ) ) {
							if ( $data['set_gallery'] == 2 ) {
								$downloaded_url = wp_get_attachment_url( $thumb_id );
								$description    = html_entity_decode( $post->post_content, ENT_QUOTES | ENT_XML1, 'UTF-8' );
								$description    = preg_replace( '/[^"]{0,}' . preg_quote( $image_id, '/' ) . '[^"]{0,}/U', $downloaded_url, $description );
								$description    = str_replace( $data['image_src'], $downloaded_url, $description );
								wp_update_post( array( 'ID' => $product_id, 'post_content' => $description ) );
							} else {
								if ( $data['product_ids'] ) {
									$product_ids = explode( ',', $data['product_ids'] );
									foreach ( $product_ids as $v_id ) {
										if ( in_array( get_post_type( $v_id ), array(
											'product',
											'product_variation'
										) ) ) {
											update_post_meta( $v_id, '_thumbnail_id', $thumb_id );
										}
									}
								}

								if ( 1 == $data['set_gallery'] ) {
									$gallery = get_post_meta( $product_id, '_product_image_gallery', true );
									if ( $gallery ) {
										$gallery_array = explode( ',', $gallery );
									} else {
										$gallery_array = array();
									}
									$gallery_array[] = $thumb_id;
									update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_unique( $gallery_array ) ) );
								}
							}
							$response['status']  = 'success';
							$response['message'] = 'Success';
							VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::delete( $id );
						} else {
							$response['message'] = $thumb_id->get_error_message();
						}
					} else {
						$response['message'] = esc_html__( 'Please disable "Use external links for images" and enable "Import description images"', 'woocommerce-alidropship' );
					}
				} else {
					$response['message'] = esc_html__( 'Product does not exist', 'woocommerce-alidropship' );
				}
			} else {
				$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
			}
		}
		wp_send_json( $response );
	}

	public function delete_error_product_images() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-error-images' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$id       = isset( $_POST['item_id'] ) ? wp_slash( $_POST['item_id'] ) : '';
		$response = array(
			'status'  => 'error',
			'message' => 'Error',
		);
		if ( $id ) {
			$delete = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::delete( $id );
			if ( $delete ) {
				$response['status'] = 'success';
			} else {
				$response['message'] = esc_html__( 'Can not remove image from list', 'woocommerce-alidropship' );
			}
		} else {
			$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
		}
		wp_send_json( $response );
	}

	public function save_screen_options( $status, $option, $value ) {
		if ( 'vi_wad_error_images_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Add Screen Options
	 */
	public function screen_options_page() {
		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Number of items per page', 'wp-admin' ),
			'default' => 10,
			'option'  => 'vi_wad_error_images_per_page'
		) );
	}

	public function admin_menu() {
		$menu_slug   = 'woocommerce-alidropship-error-images';
		$import_list = add_submenu_page( 'woocommerce-alidropship', esc_html__( 'Failed Images', 'woocommerce-alidropship' ), esc_html__( 'Failed Images', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ), $menu_slug, array(
			$this,
			'page_callback'
		) );
		add_action( "load-$import_list", array( $this, 'screen_options_page' ) );
	}

	public function page_callback() {
		$user     = get_current_user_id();
		$screen   = get_current_screen();
		$option   = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );
		if ( empty ( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'All failed images', 'woocommerce-alidropship' ) ?></h2>
			<?php
			$vi_wad_search_product_id  = isset( $_GET['vi_wad_search_product_id'] ) ? sanitize_text_field( $_GET['vi_wad_search_product_id'] ) : '';
			$vi_wad_filter_by_used_for = isset( $_GET['vi_wad_filter_by_used_for'] ) ? sanitize_text_field( $_GET['vi_wad_filter_by_used_for'] ) : '';
			$count                     = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_rows( 0, 0, true, $vi_wad_search_product_id, $vi_wad_filter_by_used_for );
			$results                   = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_rows( $per_page, ( $paged - 1 ) * $per_page, false, $vi_wad_search_product_id, $vi_wad_filter_by_used_for );
			ob_start();
			?>
            <form method="get" action="">
                <input type="hidden" name="page" value="woocommerce-alidropship-error-images">
                <div class="tablenav top">
                    <div class="<?php echo esc_attr( self::set( 'button-all-container' ) ) ?>">
						<?php
						if ( count( $results ) ) {
							?>
                            <span class="vi-ui mini button positive <?php echo esc_attr( self::set( 'action-download-all' ) ) ?>"
                                  title="<?php esc_attr_e( 'Import all failed images of current page', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Import All', 'woocommerce-alidropship' ) ?></span>
                            <span class="vi-ui mini button negative <?php echo esc_attr( self::set( 'action-delete-all' ) ) ?>"
                                  title="<?php esc_attr_e( 'Remove all failed images of current page', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Delete All', 'woocommerce-alidropship' ) ?></span>
                            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'vi_wad_empty_error_images', 1 ) ) ) ?>"
                               class="vi-ui mini button negative <?php echo esc_attr( self::set( 'action-empty-error-images' ) ) ?>"
                               title="<?php esc_attr_e( 'Remove all failed images from database', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Empty List', 'woocommerce-alidropship' ) ?></a>
							<?php
						}
						?>
                    </div>
                    <div class="tablenav-pages">
                        <div class="pagination-links">
							<?php
							$total_page = ceil( $count / $per_page );
							/*Previous button*/
							if ( $per_page * $paged > $per_page ) {
								$p_paged = $paged - 1;
							} else {
								$p_paged = 0;
							}
							if ( $p_paged ) {
								$p_url = add_query_arg(
									array(
										'page'                     => 'woocommerce-alidropship-error-images',
										'paged'                    => $p_paged,
										'vi_wad_search_product_id' => $vi_wad_search_product_id,
									), admin_url( 'admin.php' )
								);
								?>
                                <a class="prev-page button" href="<?php echo esc_url( $p_url ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'Previous Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">‹</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
								<?php
							}
							?>
                            <span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'woocommerce-alidropship' ) ?></span>
                            <span id="table-paging" class="paging-input">
                                    <span class="tablenav-paging-text">
                                        <input class="current-page" type="text" name="paged" size="1"
                                               value="<?php echo esc_attr( $paged ) ?>"><span
                                                class="tablenav-paging-text"><?php esc_html_e( ' of ', 'woocommerce-alidropship' ) ?>
                                            <span
                                                    class="total-pages"><?php echo esc_html( $total_page ) ?></span>
                                        </span>
                                    </span>
                                </span>
							<?php /*Next button*/
							if ( $per_page * $paged < $count ) {
								$n_paged = $paged + 1;
							} else {
								$n_paged = 0;
							}
							if ( $n_paged ) {
								$n_url = add_query_arg(
									array(
										'page'                     => 'woocommerce-alidropship-error-images',
										'paged'                    => $n_paged,
										'vi_wad_search_product_id' => $vi_wad_search_product_id,
									), admin_url( 'admin.php' )
								); ?>
                                <a class="next-page button" href="<?php echo esc_url( $n_url ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'Next Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">›</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
								<?php
							}
							?>
                        </div>
                    </div>
                    <p class="search-box">
						<?php
						$products = VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::get_products_ids();
						if ( count( $products ) < 100 ) {
							if ( count( $products ) > 1 ) {
								ob_start();
								foreach ( $products as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( $product ) {
										?>
                                        <option value="<?php echo esc_attr( $product_id ) ?>" <?php selected( $product_id, $vi_wad_search_product_id ) ?>><?php echo esc_html( "(#{$product_id}){$product->get_title()}" ) ?></option>
										<?php
									}
								}
								$filter_product_html = ob_get_clean();
								if ( $filter_product_html ) {
									?>

                                    <select name="vi_wad_search_product_id" class="vi-wad-search-product-id">
                                        <option value=""><?php esc_html_e( 'Filter by product', 'woocommerce-alidropship' ) ?></option>
										<?php
										echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $filter_product_html );
										?>
                                    </select>

									<?php
								}
							}
						} else {
							?>
                            <select name="vi_wad_search_product_id" class="vi-wad-search-product-id-ajax">
								<?php
								if ( $vi_wad_search_product_id ) {
									$product = wc_get_product( $vi_wad_search_product_id );
									if ( $product ) {
										?>
                                        <option value="<?php echo esc_attr( $vi_wad_search_product_id ) ?>"
                                                selected><?php echo esc_html( "(#{$vi_wad_search_product_id}){$product->get_title()}" ) ?></option>
										<?php
									}
								}
								?>
                            </select>
							<?php
						}
						?>
                        <select name="vi_wad_filter_by_used_for">
                            <option value=""><?php esc_html_e( 'Filter by Used for', 'woocommerce-alidropship' ) ?></option>
                            <option value="gallery" <?php selected( 'gallery', $vi_wad_filter_by_used_for ) ?>><?php esc_html_e( 'Gallery', 'woocommerce-alidropship' ) ?></option>
                            <option value="featured_image" <?php selected( 'featured_image', $vi_wad_filter_by_used_for ) ?>><?php esc_html_e( 'Product/variation image', 'woocommerce-alidropship' ) ?></option>
                            <option value="description" <?php selected( 'description', $vi_wad_filter_by_used_for ) ?>><?php esc_html_e( 'Description', 'woocommerce-alidropship' ) ?></option>
                        </select>
                    </p>
                </div>
            </form>
			<?php
			$pagination_html = ob_get_clean();
			$image_list      = '';
			if ( count( $results ) ) {
				if ( self::$settings->get_params( 'use_external_image' ) || ! self::$settings->get_params( 'download_description_images' ) ) {
					?>
                    <div class="vi-ui negative message">
                        <div><?php esc_html_e( 'Please disable "Use external links for images" and enable "Import description images" to make Import button available for Description images', 'woocommerce-alidropship' ); ?></div>
                    </div>
					<?php
				}
				ob_start();
				?>
                <form class="vi-ui form" action="" method="get">
                    <table class="vi-ui celled table">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Index', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Product ID', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Product Title', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Product/Variation IDs', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Image url', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Used for', 'woocommerce-alidropship' ) ?></th>
                            <th><?php esc_html_e( 'Actions', 'woocommerce-alidropship' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						foreach ( $results as $key => $result ) {
							$product = wc_get_product( $result['product_id'] );
							if ( ! $product ) {
								?>
                                <tr>
                                    <td>
                                        <span class="<?php echo esc_attr( self::set( 'index' ) ) ?>"><?php echo esc_html( $key + 1 ) ?></span>
                                    </td>

									<?php
									foreach ( $result as $result_k => $result_v ) {
										if ( $result_k === 'id' ) {
											continue;
										}
										?>
                                        <td>
										<span>
                                            <?php
                                            switch ( $result_k ) {
	                                            case 'image_src':
		                                            ?>
                                                    <img width="48" height="48"
                                                         src="<?php echo esc_attr( $result_v ) ?>">
		                                            <?php
		                                            break;
	                                            case 'product_ids':
		                                            echo str_replace( ',', ', ', $result_v );
		                                            break;
	                                            case 'set_gallery':
		                                            if ( $result_v == 2 ) {
			                                            esc_html_e( 'Description', 'woocommerce-alidropship' );
		                                            } elseif ( $result_v == 1 ) {
			                                            esc_html_e( 'Gallery', 'woocommerce-alidropship' );
		                                            } else {
			                                            esc_html_e( 'Product/variation image', 'woocommerce-alidropship' );
		                                            }
		                                            break;
	                                            default:
		                                            echo esc_html( $result_v );
                                            }
                                            ?>
                                        </span>
                                        </td>
										<?php
										if ( $result_k === 'product_id' ) {
											?>
                                            <td>-
                                            </td>
											<?php
										}
									}
									?>
                                    <td>
                                        <div class="<?php echo esc_attr( self::set( 'actions-container' ) ) ?>">
                                            <span><?php esc_html_e( 'The product this image belongs to was deleted so this image is now removed from list', 'woocommerce-alidropship' ) ?></span>
                                        </div>
                                    </td>
                                </tr>
								<?php
								VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::delete( $result['id'] );
								continue;
							} else {
								?>
                                <tr>
                                    <td>
                                        <span class="<?php echo esc_attr( self::set( 'index' ) ) ?>"><?php echo esc_html( $key + 1 ) ?></span>
                                    </td>
									<?php
									$hide_import_button = false;
									foreach ( $result as $result_k => $result_v ) {
										if ( $result_k === 'id' ) {
											continue;
										}
										?>
                                        <td>
										<span>
                                            <?php
                                            switch ( $result_k ) {
	                                            case 'image_src':
		                                            ?>
                                                    <img width="48" height="48"
                                                         src="<?php echo esc_attr( $result_v ) ?>">
		                                            <?php
		                                            break;
	                                            case 'product_ids':
		                                            echo str_replace( ',', ', ', $result_v );
		                                            break;
	                                            case 'set_gallery':
		                                            if ( $result_v == 2 ) {
			                                            esc_html_e( 'Description', 'woocommerce-alidropship' );
			                                            if ( self::$settings->get_params( 'use_external_image' ) || ! self::$settings->get_params( 'download_description_images' ) ) {
				                                            $hide_import_button = true;
			                                            }
		                                            } elseif ( $result_v == 1 ) {
			                                            esc_html_e( 'Gallery', 'woocommerce-alidropship' );
		                                            } else {
			                                            esc_html_e( 'Product/variation image', 'woocommerce-alidropship' );
		                                            }
		                                            break;
	                                            default:
		                                            echo esc_html( $result_v );
                                            }
                                            ?>
                                        </span>
                                        </td>
										<?php
										if ( $result_k === 'product_id' ) {
											?>
                                            <td><a class="<?php echo esc_attr( self::set( 'product-title' ) ) ?>"
                                                   target="_blank"
                                                   href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $result['product_id'] ) ) ?>"><?php echo esc_html( $product->get_title() ) ?></a>
                                            </td>
											<?php
										}
									}
									?>
                                    <td>
                                        <div class="<?php echo esc_attr( self::set( 'actions-container' ) ) ?>">
											<?php
											if ( ! $hide_import_button ) {
												?>
                                                <span class="vi-ui positive button <?php echo esc_attr( self::set( 'action-download' ) ) ?>"
                                                      data-item_id="<?php echo esc_attr( $result['id'] ) ?>"><?php esc_html_e( 'Import', 'woocommerce-alidropship' ) ?></span>
												<?php
											}
											?>
                                            <span class="vi-ui negative button <?php echo esc_attr( self::set( 'action-delete' ) ) ?>"
                                                  data-item_id="<?php echo esc_attr( $result['id'] ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?></span>
                                        </div>
                                    </td>
                                </tr>
								<?php
							}
						}
						?>
                        </tbody>
                    </table>
                </form>
				<?php
				$image_list = ob_get_clean();
			}
			echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
			echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $image_list );
			echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
			wp_reset_postdata();
			?>
        </div>
		<?php
	}

	public function enqueue_semantic() {
		wp_dequeue_script( 'select-js' );//Causes select2 error, from ThemeHunk MegaMenu Plus plugin
		wp_dequeue_style( 'eopa-admin-css' );
		/*Stylesheet*/
		wp_enqueue_style( 'vi-woocommerce-alidropship-form', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'form.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-table', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'table.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-icon', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'icon.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-segment', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'segment.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-button', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'button.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-message', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'message.min.css' );
		wp_enqueue_style( 'select2', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'select2.min.css' );
		wp_enqueue_script( 'select2-v4', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'select2.js', array( 'jquery' ), '4.0.3' );
	}

	public function admin_enqueue_scripts() {
		global $pagenow;
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['page'] ) ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-error-images' ) {
			$this->enqueue_semantic();
			wp_enqueue_style( 'woocommerce-alidropship-error-images', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'error-images.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'woocommerce-alidropship-error-images', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'error-images.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_localize_script( 'woocommerce-alidropship-error-images', 'vi_wad_params_admin_error_images', array(
				'url'                     => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce'      => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
				'i18n_confirm_delete'     => esc_html__( 'Are you sure you want to delete this item?', 'woocommerce-alidropship' ),
				'i18n_confirm_delete_all' => esc_html__( 'Are you sure you want to delete all item(s) on this page?', 'woocommerce-alidropship' ),
			) );
		}
	}
}