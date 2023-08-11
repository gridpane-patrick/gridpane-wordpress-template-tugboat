<?php  
	/**
	 * The template created for displaying blog panel
	 *
	 * @version 0.0.1
	 * @since 6.0.0
	 */
	
	// panel blog
	// Kirki::add_panel( 'blog', array(
	//     'title'       => esc_html__( 'Blog', 'xstore' ),
	//     'icon'		  => 'dashicons-editor-table',
	//     'priority' => $priorities['blog']
	// 	) );

	add_filter( 'et/customizer/add/panels', function($panels) use($priorities){

		$args = array(
			'blog'	 => array(
				'id'          => 'blog',
				'title'       => esc_html__( 'Blog', 'xstore' ),
				'icon'		  => 'dashicons-editor-table',
				'priority'    => $priorities['blog']
			)
		);

		return array_merge( $panels, $args );
	});

?>