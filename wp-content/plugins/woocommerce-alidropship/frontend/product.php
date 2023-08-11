<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Frontend_Product
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Frontend_Product {
	private static $settings;
	private $video;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'init', array( $this, 'shortcode_init' ) );
		if ( self::$settings->get_params( 'enable' ) ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'show_video_tab' ) );
		}
	}

	/**
	 * Init shortcode
	 */
	public function shortcode_init() {
		add_shortcode( 'ald_product_video', array( $this, 'shortcode_product_video' ) );
	}

	/**
	 * Shortcode that displays video of a product
	 */
	public function shortcode_product_video( $atts ) {
		global $product;
		$args = shortcode_atts(
			array(
				'product_id' => '',
				'poster'     => '',
				'loop'       => '',
				'autoplay'   => '',
				'preload'    => 'metadata',
				'height'     => false,
				'width'      => false,
				'class'      => 'wp-video-shortcode ald-product-video-shortcode',
			), $atts
		);
		if ( $args['height'] === false ) {
			unset( $args['height'] );
		}
		if ( $args['width'] === false ) {
			unset( $args['width'] );
		}
		if ( ! $args['product_id'] ) {
			if ( $product ) {
				$args['product_id'] = $product->get_id();
			}
		}
		if ( $args['product_id'] ) {
			$video = get_post_meta( $args['product_id'], '_vi_wad_product_video', true );
			if ( $video ) {
				unset( $args['product_id'] );
				$shortcode_atts = array();
				foreach ( $args as $key => $value ) {
					$shortcode_atts[] = $key . '="' . $value . '"';
				}

				return do_shortcode( '[video src="' . $video . '" ' . implode( ' ', $shortcode_atts ) . ']' );
			}
		}

		return '';
	}

	/**
	 * Filter WooCommerce tabs to show video tab when available and enabled
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function show_video_tab( $tabs ) {
		global $product;
		$product_id        = $product->get_id();
		$product_video_tab = get_post_meta( $product_id, '_vi_wad_show_product_video_tab', true );
		if ( ! $product_video_tab ) {
			if ( ! self::$settings->get_params( 'show_product_video_tab' ) ) {
				return $tabs;
			}
		} else {
			if ( $product_video_tab === 'hide' ) {
				return $tabs;
			}
		}

		if ( $product ) {
			$this->video = get_post_meta( $product_id, '_vi_wad_product_video', true );
			if ( $this->video ) {
				$tabs['vi_wad_video_tab'] = array(
					'title'    => __( 'Video', 'woocommerce-alidropship' ),
					'priority' => self::$settings->get_params( 'product_video_tab_priority' ),
					'callback' => array( $this, 'show_video' )
				);
			}
		}

		return $tabs;
	}

	/**
	 * Callback function of video tab
	 */
	public function show_video() {
		if ( self::$settings->get_params( 'product_video_full_tab' ) ) {
			echo do_shortcode( '[video src="' . $this->video . '" width=""]' );
		} else {
			echo do_shortcode( '[video src="' . $this->video . '"]' );
		}
	}
}
