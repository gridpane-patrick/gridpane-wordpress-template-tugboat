<?php  
	/**
	 * The template created for displaying typography panel
	 *
	 * @version 0.0.1
	 * @since 6.0.0
	 */
	
	// panel typography
	// Kirki::add_panel( 'typography', array(
	//     'title'       => esc_html__( 'Typography', 'xstore' ),
	//     'description' => esc_html__( 'If you want to upload the custom font instead of Google Fonts to use throughout the site do it', 'xstore') . ' <a href="'.admin_url('admin.php?page=et-panel-custom-fonts').'" target="_blank">'.esc_html__('here', 'xstore').'</a>',
	//     'icon'		  => 'dashicons-editor-spellcheck',
	//     'priority' => $priorities['typography']
	// 	) );

	add_filter( 'et/customizer/add/panels', function($panels) use($priorities){

		$args = array(
			'typography'	 => array(
				'id'          => 'typography',
				'title'       => esc_html__( 'Typography', 'xstore' ),
				'description' => esc_html__( 'If you want to upload the custom font instead of Google Fonts to use throughout the site do it', 'xstore') . ' <a href="'.admin_url('admin.php?page=et-panel-custom-fonts').'" target="_blank">'.esc_html__('here', 'xstore').'</a>',
				'icon'		  => 'dashicons-editor-spellcheck',
				'priority' => $priorities['typography']
			)
		);

		return array_merge( $panels, $args );
	});
?>