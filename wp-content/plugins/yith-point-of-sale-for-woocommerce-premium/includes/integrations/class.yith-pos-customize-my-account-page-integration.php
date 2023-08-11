<?php
!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

/**
 * Class YITH_POS_Customize_My_Account_Page_Integration
 *
 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
 * @since   1.0.6
 */
class YITH_POS_Customize_My_Account_Page_Integration extends YITH_POS_Integration {
    /** @var YITH_POS_Customize_My_Account_Page_Integration */
    protected static $_instance;

    /**
     * Constructor
     *
     * @param bool $plugin_active
     * @access protected
     */
    protected function __construct( $plugin_active ) {
        parent::__construct( $plugin_active);

		add_filter('ywcmap_skip_verification', function($verification){
			if (( defined( 'REST_REQUEST' ) && REST_REQUEST ) && isset($_GET['yith_pos_add_customer']) && $_GET['yith_pos_add_customer'] ){
				$verification = 'yes';
			}
			return $verification;
		});
    }

}