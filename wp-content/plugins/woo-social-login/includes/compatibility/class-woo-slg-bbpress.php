<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * bbPress Compability Class
 * 
 * Handles bbPress Compability
 * 
 * @package WooCommerce - Social Login
 * @since 2.2.1
 */
class WOO_Slg_bbPress {
	public $render, $model;

	public function __construct(){
		global $woo_slg_render, $woo_slg_model;

		$this->render = $woo_slg_render;
		$this->model = $woo_slg_model;
	}

	/**
	 * Save bbpress settings
	 */
	public function woo_slg_save_bbpress_settings( $settings ) {

		$woo_slg_enable_bbpress_login_page = ( isset($_POST['woo_slg_enable_bbpress_login_page']) ) ? 'yes' : 'no';
		$woo_slg_enable_bbpress_register_page = ( isset($_POST['woo_slg_enable_bbpress_register_page']) ) ? 'yes' : 'no';

		$settings['woo_slg_enable_bbpress_login_page'] = $woo_slg_enable_bbpress_login_page;
		$settings['woo_slg_enable_bbpress_register_page'] = $woo_slg_enable_bbpress_register_page;

		return $settings;
	}

	/**
	 * Display bbPress settings
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_display_bbpress_setting( $woo_slg_options ) {

		$enable_login_page = isset( $woo_slg_options['woo_slg_enable_bbpress_login_page'] ) ? $woo_slg_options['woo_slg_enable_bbpress_login_page'] : '';

		$enable_register_page = isset( $woo_slg_options['woo_slg_enable_bbpress_register_page'] ) ? $woo_slg_options['woo_slg_enable_bbpress_register_page'] : ''; ?>

		<tr class="woo-slg-setting-seperator">
			<td colspan="2">
				<strong><?php esc_html_e( 'bbPress Settings', 'wpwfp' ); ?></strong>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label><?php esc_html_e( 'Display Social Login buttons on:', 'wooslg' ); ?></label>
			</th>
			<td>
				<ul>
					<li class="wooslg-settings-meta-box">
						<label>
							<input type="checkbox" name="woo_slg_enable_bbpress_login_page" value="1" <?php echo ($enable_login_page == 'yes') ? 'checked="checked"' : ''; ?>/>
							<?php echo esc_html__( 'Check this box to add social login on bbPress login.', 'wooslg' ); ?>
						</label>
					</li>
					<li class="wooslg-settings-meta-box">
						<label>
							<input type="checkbox" name="woo_slg_enable_bbpress_register_page" value="1" <?php echo ($enable_register_page == 'yes') ? 'checked="checked"' : ''; ?>/>
							<?php echo esc_html__( 'Check this box to add social login on bbPress Registration page.', 'wooslg' ); ?>
						</label>
					</li>
				</ul>
			</td>
		</tr>
	<?php
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the bbPress compability.
	 * 
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function add_hooks() {

		global $woo_slg_options;

		/*** admin file ***/
		add_filter( 'woo_slg_save_settings_array', array($this, 'woo_slg_save_bbpress_settings') );
		add_action( 'woo_slg_after_display_setting', array($this, 'woo_slg_display_bbpress_setting') );

		// check enable bbPress registration from settings
		if( ! empty($woo_slg_options['woo_slg_enable_bbpress_register_page']) && $woo_slg_options['woo_slg_enable_bbpress_register_page'] == "yes" ) {

			// remove default wordpress register action hook
			remove_action('login_form_register', array($this, 'woo_slg_social_login_buttons_on_wp_register'));

			add_action('register_form', array($this->render, 'woo_slg_social_login_buttons_on_login'), 999);
		}

		// check enable bbPress login from settings
		if( ! empty($woo_slg_options['woo_slg_enable_bbpress_login_page']) && $woo_slg_options['woo_slg_enable_bbpress_login_page'] == "yes" ) {

			// remove default wordpress register action hook
			remove_action('login_form_login', array($this, 'woo_slg_social_login_buttons_on_wp_login'));

			add_action('login_form', array($this->render, 'woo_slg_social_login_buttons_on_login'), 999);
		}
	}
}