<?php

namespace SEOPressElementorFAQScheme;

// To prevent calling the plugin directly
if ( ! function_exists( 'add_action' ) ) {
	echo 'Please don&rsquo;t call the plugin directly. Thanks :)';
	exit;
}

/**
 * Class Plugin
 *
 * Main Plugin class
 *
 * @since 1.2.0
 */
class FAQ_Schema {
	/**
	 * Structured data value
	 *
	 * @var string
	 */
	private $structured_data_enabled;

	/**
	 * Instance
	 *
	 * @since 1.2.0
	 * @access private
	 * @static
	 *
	 * @var FAQ_Schema The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return FAQ_Schema An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$seopress_get_toggle_rich_snippets_option = get_option( 'seopress_toggle' );
		$this->structured_data_enabled            = isset( $seopress_get_toggle_rich_snippets_option['toggle-rich-snippets'] ) ? $seopress_get_toggle_rich_snippets_option['toggle-rich-snippets'] : '0';

		add_filter( 'elementor/widget/render_content', array( $this, 'add_faq_json_ld_schema' ), 10, 2 );
		add_action( 'elementor/element/before_section_end', array( $this, 'add_faq_enable_option' ), 10, 3 );
	}

	/**
	 * Add FAQ JSON-LD Schema for the Toggle and Accordion Widget
	 *
	 * @param   string                 $content
	 * @param   \Elementor\Widget_Base $widget
	 *
	 * @return  string
	 */
	public function add_faq_json_ld_schema( $content, $widget ) {
		if ( '0' == $this->structured_data_enabled || ( ! $widget instanceof \Elementor\Widget_Toggle && ! $widget instanceof \Elementor\Widget_Accordion ) ) {
			return $content;
		}

		$show_faq = $widget->get_settings( 'show_faq_schema' );

		if ( 'yes' !== $show_faq ) {
			return $content;
		}

		$tabs = $widget->get_settings( 'tabs' );

		if ( ! empty( $tabs ) ) {
			$entities = array();
			foreach ( $tabs as $tab ) {
				$entity     = array(
					'@type'          => 'Question',
					'name'           => $tab['tab_title'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $tab['tab_content'],
					),
				);
				$entities[] = $entity;
			}

			$schema = '<script type="application/ld+json">
				{
				"@context": "https://schema.org",
				"@type": "FAQPage",
				"mainEntity": ' . json_encode( $entities ) . '
				}
			</script>';

			return $content . apply_filters( 'seopress_schemas_faq_html', $schema );
		}

		return $content;
	}

	/**
	 * Extend Toggle and Accordion widget with option to enable / disable option for show FAQ JSON-LD Schema
	 *
	 * @param   \WP_Base $element
	 * @param   string   $section_id
	 * @param   array    $args
	 *
	 * @return  void
	 */
	public function add_faq_enable_option( $element, $section_id, $args ) {
		if ( '0' == $this->structured_data_enabled ) {
			return;
		}

		if (
			( $element->get_name() === 'toggle' && 'section_toggle' === $section_id ) ||
			( $element->get_name() === 'accordion' && 'section_title' === $section_id ) ) {
			$element->add_control(
				'show_faq_schema',
				array(
					'label' => __( 'Enable FAQ Schema', 'wp-seopress-pro' ),
					'type'  => \Elementor\Controls_Manager::SWITCHER,
				)
			);
		}
	}
}
// Instantiate Plugin Class
FAQ_Schema::instance();
