<?php 
	// panel woocommerce
	// Kirki::add_panel( 'woocommerce', array(
	//     'title'       => esc_html__( 'WooCommerce (Shop)', 'xstore' ),
	//     'icon'		  => 'dashicons-cart',
	//     'priority' => $priorities['woocommerce']
	// ) );

	add_filter( 'et/customizer/add/panels', function($panels) use($priorities){

		$args = array(
			'woocommerce'	 => array(
				'id'          => 'woocommerce',
				'title'       => esc_html__( 'WooCommerce (Shop)', 'xstore' ),
				'icon'		  => 'dashicons-cart',
				'priority' => $priorities['woocommerce']
			)
		);

		return array_merge( $panels, $args );
	});

?>