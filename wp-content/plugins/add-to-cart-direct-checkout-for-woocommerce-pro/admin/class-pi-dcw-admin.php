<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/admin
 * @author     PI Websolution <sales@piwebsolution.com>
 */
class Pi_Dcw_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->register_menu();		
		add_action('admin_init', array($this,'plugin_redirect'));
	}

	function plugin_redirect(){
		if (get_option('pi_dcw_do_activation_redirect', false)) {
			delete_option('pi_dcw_do_activation_redirect');
			if(!isset($_GET['activate-multi']))
			{
				wp_redirect("admin.php?page=pi-dcw");
			}
		}
	}

	
	public function register_menu(){
		if(is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )){
			$obj =	new Pi_Dcw_Menu($this->plugin_name, $this->version);
			new Class_Pi_Dcw_Option($this->plugin_name);
			
			new Class_Pi_Dcw_Quick_View($this->plugin_name);
			new Class_Pi_Dcw_Buy_Now($this->plugin_name);
			new Class_Pi_Dcw_Checkout($this->plugin_name);
			new pisol_dcw_speed($this->plugin_name);
		}	
		new Class_Pi_Dcw_AddToCart($this->plugin_name);
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pi_Dcw_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pi_Dcw_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(isset($_GET['page']) && $_GET['page'] == 'pi-dcw'){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pi-dcw-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pi_Dcw_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pi_Dcw_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(isset($_GET['page']) && $_GET['page'] == 'pi-dcw'){
			wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'selectWoo' );
        	wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css');
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pi-dcw-admin.js', array( 'jquery' ), $this->version, false );

	}

}
