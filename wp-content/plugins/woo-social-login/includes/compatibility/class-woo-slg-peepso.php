<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * PeepSo Compability Class
 * 
 * Handles PeepSo Compability
 * 
 * @package WooCommerce - Social Login
 * @since 2.2.1
 */
class WOO_Slg_PeepSo {

	public $render, $model;

	public function __construct(){
		global $woo_slg_render, $woo_slg_model;

		$this->render = $woo_slg_render;
		$this->model = $woo_slg_model;
	}

	/**
	 * Save Settings
	 */
	public function woo_slg_save_peepso_settings( $settings ) {

		$woo_slg_enable_peepso_login_page = ( isset($_POST['woo_slg_enable_peepso_login_page']) ) ? 'yes' : 'no';
		$woo_slg_enable_peepso_register_page = ( isset($_POST['woo_slg_enable_peepso_register_page']) ) ? 'yes' : 'no';
		$woo_slg_allow_peepso_avatar = ( isset($_POST['woo_slg_allow_peepso_avatar']) ) ? 'yes' : 'no';
		$woo_slg_allow_peepso_cover = ( isset($_POST['woo_slg_allow_peepso_cover']) ) ? 'yes' : 'no';
		$woo_slg_display_link_peepso_acc_detail = ( isset($_POST['woo_slg_display_link_peepso_acc_detail']) ) ? 'yes' : 'no';

		// Peepso avatar each time
		$woo_slg_peepso_avatar_each_time = ( isset($_POST['woo_slg_peepso_avatar_each_time']) ) ? 'yes' : 'no';
		// Peepso cover each time
		$woo_slg_peepso_cover_each_time = ( isset($_POST['woo_slg_peepso_cover_each_time']) ) ? 'yes' : 'no';

		$settings['woo_slg_enable_peepso_login_page'] = $woo_slg_enable_peepso_login_page;
		$settings['woo_slg_enable_peepso_register_page'] = $woo_slg_enable_peepso_register_page;
		$settings['woo_slg_allow_peepso_avatar'] = $woo_slg_allow_peepso_avatar;
		$settings['woo_slg_allow_peepso_cover'] = $woo_slg_allow_peepso_cover;
		$settings['woo_slg_display_link_peepso_acc_detail'] = $woo_slg_display_link_peepso_acc_detail;
		$settings['woo_slg_peepso_avatar_each_time'] = $woo_slg_peepso_avatar_each_time;
		$settings['woo_slg_peepso_cover_each_time'] = $woo_slg_peepso_cover_each_time;

		return $settings;
	}

	/**
	 * Display peepso settings
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_display_peepso_setting( $woo_slg_options ) {

		$woo_slg_enable_peepso_login_page = isset( $woo_slg_options['woo_slg_enable_peepso_login_page'] ) ? $woo_slg_options['woo_slg_enable_peepso_login_page'] : '';

		$woo_slg_enable_peepso_register_page = isset( $woo_slg_options['woo_slg_enable_peepso_register_page'] ) ? $woo_slg_options['woo_slg_enable_peepso_register_page'] : '';

		$woo_slg_allow_peepso_avatar = isset( $woo_slg_options['woo_slg_allow_peepso_avatar'] ) ? $woo_slg_options['woo_slg_allow_peepso_avatar'] : '';

		$woo_slg_peepso_avatar_each_time = isset( $woo_slg_options['woo_slg_peepso_avatar_each_time'] ) ? $woo_slg_options['woo_slg_peepso_avatar_each_time'] : '';

		$woo_slg_allow_peepso_cover = isset( $woo_slg_options['woo_slg_allow_peepso_cover'] ) ? $woo_slg_options['woo_slg_allow_peepso_cover'] : '';

		$woo_slg_peepso_cover_each_time = isset( $woo_slg_options['woo_slg_peepso_cover_each_time'] ) ? $woo_slg_options['woo_slg_peepso_cover_each_time'] : '';

		$woo_slg_display_link_peepso_acc_detail = isset( $woo_slg_options['woo_slg_display_link_peepso_acc_detail'] ) ? $woo_slg_options['woo_slg_display_link_peepso_acc_detail'] : '';

		$peepso_avatar_each_class = ( $woo_slg_allow_peepso_avatar != 'yes') ? ' woo-slg-hide-section': '';
		$peepso_cover_each_class = ( $woo_slg_allow_peepso_cover != 'yes') ? ' woo-slg-hide-section': ''; ?>

		<tr class="woo-slg-setting-seperator"><td colspan="2">
			<strong><?php esc_html_e( 'PeepSo Settings', 'wpwfp' ); ?></strong>
		</td></tr>
		<tr>
			<th scope="row"><label>
				<?php esc_html_e( 'Display Social Login buttons on:', 'wooslg' ); ?>
			</label></th>
			<td><ul>
				<li class="wooslg-settings-meta-box">
					<label>
						<input type="checkbox" name="woo_slg_enable_peepso_login_page" value="1" <?php echo ($woo_slg_enable_peepso_login_page == 'yes') ? 'checked="checked"' : ''; ?>/>
						<?php echo esc_html__( 'Check this box to add social login on PeepSo login.', 'wooslg' ); ?>
					</label>
				</li>
				<li class="wooslg-settings-meta-box">
					<label>
						<input type="checkbox" name="woo_slg_enable_peepso_register_page" value="1" <?php echo ($woo_slg_enable_peepso_register_page=='yes') ? 'checked="checked"' : ''; ?>/>
						<?php echo esc_html__( 'Check this box to add social login on PeepSo Registration page.', 'wooslg' ); ?>
					</label>
				</li>
			</ul></td>
		</tr>

		<tr>
			<th scope="row">
				<label for="woo_slg_allow_peepso_avatar"><?php esc_html_e( 'Set Avatar on PeepSo user profile:', 'wooslg' );?></label>
			</th>
			<td>
				<input type="checkbox" id="woo_slg_allow_peepso_avatar" class="allow_peepso_avatar" name="woo_slg_allow_peepso_avatar" value="1" <?php echo ($woo_slg_allow_peepso_avatar=='yes') ? 'checked="checked"' : ''; ?>/>
				<label for="woo_slg_allow_peepso_avatar"><?php echo esc_html__( ' Check this box if you want to set social media avatar to PeepSo user profile.','wooslg' ); ?></label>
			</td>
		</tr>

		<tr id="peepso_avatar_each_time" class="<?php print $peepso_avatar_each_class;?>">
			<th scope="row">
				<label for="woo_slg_peepso_avatar_each_time"><?php esc_html_e( 'Set Avatar on PeepSo user profile every time:', 'wooslg' );?></label>
			</th>
			<td>
				<input type="checkbox" id="woo_slg_peepso_avatar_each_time" class="allow_peepso_avatar" name="woo_slg_peepso_avatar_each_time" value="1" <?php echo ($woo_slg_peepso_avatar_each_time=='yes') ? 'checked="checked"' : ''; ?>/>
				<label for="woo_slg_peepso_avatar_each_time"><?php echo esc_html__( ' Check this box if you want to set social media avatar every time.','wooslg' ); ?></label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="woo_slg_allow_peepso_cover"><?php esc_html_e( 'Set Cover Photo on PeepSo user profile:', 'wooslg' );?></label>
			</th>
			<td>
				<input type="checkbox" id="woo_slg_allow_peepso_cover" class="allow_peepso_cover" name="woo_slg_allow_peepso_cover" value="1" <?php echo ($woo_slg_allow_peepso_cover == 'yes') ? 'checked="checked"' : ''; ?>/>
				<label for="woo_slg_allow_peepso_cover"><?php echo esc_html__( ' Check this box if you want to set social media cover to PeepSo user profile.','wooslg' ); ?></label>
				<div class="woo-slg-error info grey"><ul><li><?php print esc_html__( 'Note: It will set PeepSo user cover photo if user logged in via Twitter only as other social networks not providing cover photo','wooslg' );?></li></ul></div>
			</td>
		</tr>

		<tr id="peepso_cover_each_time" class="<?php print $peepso_cover_each_class;?>">
			<th scope="row">
				<label for="woo_slg_peepso_cover_each_time"><?php esc_html_e( 'Set Cover Photo on PeepSo user profile every time:', 'wooslg' );?></label>
			</th>
			<td>
				<input type="checkbox" id="woo_slg_peepso_cover_each_time" class="allow_peepso_cover" name="woo_slg_peepso_cover_each_time" value="1" <?php echo ($woo_slg_peepso_cover_each_time == 'yes') ? 'checked="checked"' : ''; ?>/>
				<label for="woo_slg_peepso_cover_each_time"><?php echo esc_html__( ' Check this box if you want to set social media cover every time.','wooslg' ); ?></label>
			</td>
		</tr>

		<tr>
			<th scope="row"><label for="woo_slg_display_link_peepso">
				<?php esc_html_e( 'Display "Link Your Account" button on:', 'wooslg' ); ?>
			</label></th>
			<td>
				<ul>
					<li>
						<input type="checkbox" id="woo_slg_display_link_peepso_acc_detail" name="woo_slg_display_link_peepso_acc_detail" value="1" <?php echo (empty($woo_slg_display_link_peepso_acc_detail) || $woo_slg_display_link_peepso_acc_detail=='yes') ? 'checked="checked"' : ''; ?>/>
						<label for="woo_slg_display_link_peepso_acc_detail"><?php echo esc_html__( ' Check this box to allow customers to link their social account on the Peepso profile page.','wooslg' ); ?></label>
					</li>
				</ul>
			</td>
		</tr>
	<?php
	}

	/**
	 * Manage peepso data on update customer profile
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_peepso_update_customer_profile( $wp_id, $wp_user_data, $new_customer = false ) {

		//type of social account
        $type = $wp_user_data['type'];

        // If option to set avtar photo is checked
		$woo_slg_peepso_avatar = get_option( 'woo_slg_allow_peepso_avatar' );

		// If option to set cover photo is checked
        $woo_slg_peepso_cover = get_option( 'woo_slg_allow_peepso_cover' );

        // If option to set avtar photo is each time
        $peepso_avatar_each_time = get_option( 'woo_slg_peepso_avatar_each_time' );

        // If option to set cover photo each time
        $peepso_cover_each_time = get_option( 'woo_slg_peepso_cover_each_time' );

        $avatar_image = get_user_meta( $wp_id, 'woo_slg_social_avatar_img', true );
        $cover_image = get_user_meta( $wp_id, 'woo_slg_social_cover_img', true );

        // check if peepso avatar is enabled
        if( !empty($woo_slg_peepso_avatar) && $woo_slg_peepso_avatar == 'yes' ) {

            // If avatar image is empty or avatar every time option enabled
            if( empty($avatar_image) || $peepso_avatar_each_time == 'yes' ) {

                // Get profile image
                $avatar_image = '';
                if( $type == 'facebook' && !empty($wp_user_data['all']['picture']) ) {
                    $avatar_image = $wp_user_data['all']['picture'];
                } elseif( $type == 'googleplus' && !empty($wp_user_data['all']['image']['url']) ) {
                    $avatar_image = $wp_user_data['all']['image']['url'];
                } elseif( $type == 'linkedin' && !empty($wp_user_data['all']['pictureUrl']) ) {
                    $avatar_image = $wp_user_data['all']['pictureUrl'];
                } elseif( $type == 'twitter' && !empty($wp_user_data['all']->profile_image_url) ) {
                    $avatar_image = $wp_user_data['all']->profile_image_url;
                } elseif( $type == 'yahoo' && !empty($wp_user_data['all']->image->imageUrl) ) {
                    $avatar_image = $wp_user_data['all']['image']['imageUrl'];
                } elseif( $type == 'foursquare' && !empty($wp_user_data['all']->photo->prefix) ) {
                    $image_url = $wp_user_data['all']->photo->prefix . '200x200' . $wp_user_data['all']->photo->suffix;
                    $avatar_image = $image_url;
                } elseif( $type == 'vk' && !empty($wp_user_data['all']['photo_big']) ) {
                    $avatar_image = $wp_user_data['all']['photo_big'];
                } elseif( $type == 'line' && !empty($wp_user_data['all']['picture']) ) {
                    $avatar_image = $wp_user_data['all']['picture'];
                }

                // Update meta for avatar image
                update_user_meta( $wp_id, 'woo_slg_social_avatar_img', $avatar_image );

                // set avatar to peepso avatar
                if( !empty($avatar_image) ) {
                    $this->woo_slg_set_peepso_avatar_img( $wp_id, $avatar_image );
                }
            }
        }

        if( !empty($woo_slg_peepso_cover) && $woo_slg_peepso_cover == 'yes' ) {

            // If Cover image is empty or cover image every time option enabled
            if( empty($cover_image) || $peepso_cover_each_time == 'yes') {

                $cover_image = '';
                if( $type == 'facebook' && !empty($wp_user_data['all']['cover']) ) {
                    $cover_image = $wp_user_data['all']['cover'];
                } elseif( $type == 'twitter' && !empty($wp_user_data['all']->profile_banner_url) ) {
                    $cover_image = $wp_user_data['all']->profile_banner_url;
                }

                // Update user meta for cover image
                update_user_meta( $wp_id, 'woo_slg_social_cover_img', $cover_image );

                // set cover image to peepso cover
                if( !empty($cover_image) ) {
                    $this->woo_slg_set_peepso_cover_img( $wp_id, $cover_image );
                }
            }
        }
	}

	/**
	 * Set Profile cover image
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_set_peepso_cover_img( $user_id, $cover_url ) {
        $PeepSoUser = PeepSoUser::get_instance( $user_id );
        $PeepSoUser->move_cover_file( $cover_url );
    }

    /**
     * Set avtar image
     *
     * @package WooCommerce - Social Login
 	 * @since 2.2.1
     */
    function woo_slg_set_peepso_avatar_img( $user_id, $image_url ) {
        $PeepSoUser = PeepSoUser::get_instance( $user_id );
        $PeepSoUser->move_avatar_file( $image_url );
        $PeepSoUser->finalize_move_avatar_file();
    }

    /**
	 * Add Social Login Profile in Peepso Account
	 * Handles to display social link buttons on peepso member page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_peepso_preference_custom_settings( $pref ) {
		global $woo_slg_options;
		if( !empty($woo_slg_options) && $woo_slg_options['woo_slg_display_link_peepso_acc_detail'] == 'yes' ) {
			$custom_settings = array(
				'wb_custom_notification_setting' => array(
					'title' => '',
					'items' =>$this->render->woo_slg_social_profile()
				),
			);
		} else {
			$custom_settings = array();
		}

		$pref = array_merge( $pref, $custom_settings );
		return $pref;
	}

	/**
	 * Setup roles on use add
	 *
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function woo_slg_add_wp_user_peepso_setup_roles() {
		if( class_exists('PeepSoUser') ) {
            $wpuser = PeepSoUser::get_instance( $wp_id );
            $wpuser->set_user_role( 'member' );
        }
	}

	/**
	 * Adding Hooks
	 * Adding proper hooks for the PeepSo compability.
	 * 
	 * @package WooCommerce - Social Login
 	 * @since 2.2.1
	 */
	public function add_hooks() {

		global $woo_slg_options;

		/*** Admin page **/
		add_filter( 'woo_slg_save_settings_array', array($this, 'woo_slg_save_peepso_settings') );
		add_action( 'woo_slg_after_display_setting', array($this, 'woo_slg_display_peepso_setting') );

		/*** Public file code ***/
		add_action( 'woo_slg_after_update_customer_profile', array($this, 'woo_slg_peepso_update_customer_profile'), 10, 3 );

		// Check if Social login buttons enabled for Peepso Regitration page
        if( !empty($woo_slg_options['woo_slg_enable_peepso_register_page']) && $woo_slg_options['woo_slg_enable_peepso_register_page'] == "yes" ) {
            /**
             * Check if Buttons position is top
             * Display buttons to the top of login and register form
             * @since 1.8.1
             */
            if( $woo_slg_options['woo_slg_social_btn_position'] == 'top' ) {
                add_action('peepso_before_registration_form', array($this->render, 'woo_slg_social_login_buttons_on_login'));
            } else {
                add_action('peepso_after_registration_form', array($this->render, 'woo_slg_social_login_buttons_on_login'));
            }
        }

        // Check if Social login buttons enabled for Peepso Login page
        if (!empty($woo_slg_options['woo_slg_enable_peepso_login_page']) && $woo_slg_options['woo_slg_enable_peepso_login_page'] == "yes") {

            // not check position as this only the hook for login
            add_action( 'peepso_action_render_login_form_after', array($this->render, 'woo_slg_social_login_buttons_on_login') );
        }

        // display social account integration in peepso member page.
        add_filter( 'peepso_profile_preferences', array($this, 'woo_slg_peepso_preference_custom_settings'), 15, 1 );

        /*** Model file ***/
		add_action( 'woo_slg_add_wp_user_after_setup_roles', array($this, 'woo_slg_add_wp_user_peepso_setup_roles') );
	}
}