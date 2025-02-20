<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * BuddyPress Compability Class
 * 
 * Handles BuddyPress Compability
 * 
 * @package WooCommerce - Social Login
 * @since 2.2.1
 */
class WOO_Slg_BuddyPress{
	public $render, $model;

	public function __construct(){
		global $woo_slg_render, $woo_slg_model;

		$this->render = $woo_slg_render;
		$this->model = $woo_slg_model;
	}

	/**
	 * Save BuddyPress settings
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_save_buddypress_settings( $settings ) {

		$woo_slg_enable_buddypress_login_page = ( isset($_POST['woo_slg_enable_buddypress_login_page']) ) ? 'yes' : 'no';
		$woo_slg_enable_buddypress_register_page = ( isset($_POST['woo_slg_enable_buddypress_register_page']) ) ? 'yes' : 'no';

		$settings['woo_slg_enable_buddypress_login_page'] = $woo_slg_enable_buddypress_login_page;
		$settings['woo_slg_enable_buddypress_register_page'] = $woo_slg_enable_buddypress_register_page;

		return $settings;
	}

	/**
	 * Display BuddyPress settings
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_display_buddypress_setting( $woo_slg_options ) {

		$enable_login_page = isset( $woo_slg_options['woo_slg_enable_buddypress_login_page'] ) ? $woo_slg_options['woo_slg_enable_buddypress_login_page'] : '';

		$enable_register_page = isset( $woo_slg_options['woo_slg_enable_buddypress_register_page'] ) ? $woo_slg_options['woo_slg_enable_buddypress_register_page'] : ''; ?>

		<tr class="woo-slg-setting-seperator"><td colspan="2">
			<strong><?php esc_html_e( 'BuddyPress Settings', 'wpwfp' ); ?></strong>
		</td></tr>
		<tr>
			<th scope="row">
				<label><?php esc_html_e( 'Display Social Login buttons on:', 'wooslg' ); ?></label>
			</th>
			<td><ul>
				<li class="wooslg-settings-meta-box">
					<label>
						<input type="checkbox" name="woo_slg_enable_buddypress_login_page" value="1" <?php echo ($enable_login_page=='yes') ? 'checked="checked"' : ''; ?>/>
						<?php echo esc_html__( 'Check this box to add social login on BuddyPress login.', 'wooslg' ); ?>
					</label>
				</li>
				<li class="wooslg-settings-meta-box">
					<label>
						<input type="checkbox" name="woo_slg_enable_buddypress_register_page" value="1" <?php echo ($enable_register_page=='yes') ? 'checked="checked"' : ''; ?>/>
						<?php echo esc_html__( 'Check this box to add social login on BuddyPress Registration page.', 'wooslg' ); ?>
					</label>
				</li>
			</ul></td>
		</tr>
	<?php
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the BuddyPress compability.
	 * 
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function add_hooks() {

		global $woo_slg_options;

		/*** admin file ***/
		add_filter( 'woo_slg_save_settings_array', array($this, 'woo_slg_save_buddypress_settings') );
		add_action( 'woo_slg_after_display_setting', array($this, 'woo_slg_display_buddypress_setting') );

		// check enable BUddyPress login from settings
		if( ! empty($woo_slg_options['woo_slg_enable_buddypress_login_page']) && $woo_slg_options['woo_slg_enable_buddypress_login_page'] == "yes" ) {

            /**
             * Check if Buttons position is top
             * Display buttons to the top of login and register form
             * @since 1.8.1
             */
            if( $woo_slg_options['woo_slg_social_btn_position'] == 'top' ) {
                //add social login buttons on BuddyPress login.
                add_action( 'bp_before_login_widget_loggedout', array($this->render, 'woo_slg_social_login_buttons_on_login') );
            } else {
                //add social login buttons on BuddyPress login.
                add_action( 'bp_after_login_widget_loggedout', array($this->render, 'woo_slg_social_login_buttons_on_login') );
            }
        }

        // check enable BUddyPress registration from settings
        if( ! empty($woo_slg_options['woo_slg_enable_buddypress_register_page']) && $woo_slg_options['woo_slg_enable_buddypress_register_page'] == "yes" ) {

            /**
             * Check if Buttons position is top
             * Display buttons to the top of login and register form
             * @since 1.8.1
             */
            if( $woo_slg_options['woo_slg_social_btn_position'] == 'top' ) {
                //add social login buttons on BuddyPress registration.
                add_action( 'bp_before_register_page', array($this->render, 'woo_slg_social_login_buttons_on_login') );
            } else {
                //add social login buttons on BuddyPress registration.
                add_action( 'bp_after_register_page', array($this->render, 'woo_slg_social_login_buttons_on_login') );
            }
        }
	}
}