<?php  
	/**
	 * The template created for displaying shop elements panel
	 *
	 * @version 0.0.1
	 * @since 6.0.0
	 */
	
	// panel shop-elements
	// Kirki::add_panel( 'shop-elements', array(
	//     'title'       => esc_html__( 'Shop elements', 'xstore' ),
	//     'icon'		  => 'dashicons-forms',
	//     'panel' => 'woocommerce'
	// 	) );

	add_filter( 'et/customizer/add/panels', function($panels){

		$args = array(
			'shop-elements'	 => array(
				'id'          => 'shop-elements',
				'title'       => esc_html__( 'Shop elements', 'xstore' ),
				'icon'		  => 'dashicons-forms',
				'panel' => 'woocommerce'
			)
		);

		return array_merge( $panels, $args );
	});
?>