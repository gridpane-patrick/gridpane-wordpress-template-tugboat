<?php
/**
 * Plugin Name: TW WhatsApp Chat Rotator
 * Plugin URI: https://www.themewarrior.com
 * Description: WhatsApp chat chat plugin for WordPress and WooCommerce.
 * Version: 1.1.1
 * Author: ThemeWarrior
 * Author URI: https://www.themewarrior.com
 * Domain Path: /languages
 * @package twwr-whatsapp-chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'TWWR_Agents' ) ) {
	// load agents post type module
	include( dirname( __FILE__ ) . '/includes/agents.php' );
}

if ( !class_exists( 'TWWR_Post_Type' ) ) {
	// load chat post type module
	include( dirname( __FILE__ ) . '/includes/post-type.php' );
}

if ( !class_exists( 'TWWR_Database' ) ) {
	// load DB module
	include( dirname( __FILE__ ) . '/includes/database.php' );
}

if ( !class_exists( 'TWWR_Report' ) ) {
	// load Report module
	include( dirname( __FILE__ ) . '/includes/report.php' );
}

require dirname( __FILE__ ) . '/library/BrowserDetection.php';

class TWWR_Whatsapp_Chat {
    public function __construct(){
        $this->pluginPath = dirname(__FILE__);

        $this->chat_db = new TWWR_Database;

        $this->pluginUrl = plugin_dir_url( __FILE__ );
        $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array() ;
        $btn_wc = isset($settings['woocommerce-id']) ? $settings['woocommerce-id'] : false;

        if ( $btn_wc ) {
            $position = isset($settings['wc-position']) ? $settings['wc-position'] : 'after';
            $loop = isset($settings['wc-loop']) ? $settings['wc-loop'] : false;
            if( $position == 'before' ){
                add_action( 'woocommerce_after_add_to_cart_quantity', array($this, 'twwr_whatsapp_render_standard_chat'), 40 );
                if( $loop )
                    add_action( 'woocommerce_after_shop_loop_item', array($this, 'twwr_whatsapp_render_standard_chat'), 10 );
            } else {
                add_action( 'woocommerce_after_add_to_cart_button', array($this, 'twwr_whatsapp_render_standard_chat'), 40 );
                if ( $loop )
                    add_action( 'woocommerce_after_shop_loop_item', array($this, 'twwr_whatsapp_render_standard_chat'), 100 );
            }
        }

        add_action( 'wp_enqueue_scripts', array($this, 'twwr_whatsapp_enqueue') );
        add_action( 'admin_enqueue_scripts', array($this, 'twwr_whatsapp_enqueue_admin') );
        add_action( 'init', array($this, 'twwr_whatsapp_load_languages') );
        add_action( 'after_setup_theme', array($this, 'twwr_whatsapp_add_img_size') );
        add_action( 'add_meta_boxes', array($this, 'twwr_whatsapp_meta_box') );
        add_action( 'save_post', array($this, 'twwr_whatsapp_save_meta_box'));
        add_action( 'wp_footer', array($this, 'twwr_whatsapp_render_chat'));
        add_action( 'wp_head', array($this, 'twwr_whatsapp_check_FBPixel'), 99);
        add_action( 'admin_init', array($this, 'twwr_whatsapp_register_settings') );
        add_action( 'admin_notices', array($this, 'twwr_whatsapp_admin_notices'));
        add_action( 'admin_menu', array($this, 'twwr_whatsapp_admin_menu') );
        add_action( 'wp_ajax_twwr-whatsapp-chat-count-click', array($this, 'twwr_whatsapp_chat_counter'));
        add_action( 'wp_ajax_nopriv_twwr-whatsapp-chat-count-click', array($this, 'twwr_whatsapp_chat_counter'));
        add_action( 'wp_ajax_twwr-whatsapp-chat-agent-count-click', array($this, 'twwr_whatsapp_chat_agent_counter'));
        add_action( 'wp_ajax_nopriv_twwr-whatsapp-chat-agent-count-click', array($this, 'twwr_whatsapp_chat_agent_counter'));
        add_filter( 'template_include', array($this, 'twwr_whatsapp_chat_template'));
        add_shortcode( 'twwr-whatsapp-chat', array($this, 'twwr_whatsapp_shortcode'));
        add_action( 'twwr_whatsapp_chat_report_cron', array($this, 'twwr_whatsapp_chat_report_do_cron') );
        add_action( 'current_screen', array($this, 'twwr_whatsapp_redirect') );

    }

    function twwr_whatsapp_chat_report_do_cron() {
        $to_utc = get_option('gmt_offset') * -1;
        $time = current_time( 'mysql' );

        $start = date('Y-m-d 00:00:00', strtotime($time.'-1 days'));
        $end = date('Y-m-d 23:59:59', strtotime($time.'-1 days'));

        $chats = get_posts( array('post_type' => 'twwr_whatsapp_chat'));
        $all_agent = get_posts( array('post_type' => 'twwr_whatsapp_agent'));

        $chat = array();
        $agent = array();
        $count_agent = array();

        if( is_array($chats) && count($chats) > 0 ){
            foreach( $chats as $rot ){
                $view = $this->chat_db->twwr_whatsapp_chat_total_by_id( $rot->ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
                $click = $this->chat_db->twwr_whatsapp_chat_total_by_id( $rot->ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
                $buttons = get_post_meta($rot->ID, '_twwr_whatsapp_button', true);

                $chat[] = array(
                    'name' => $rot->post_title,
                    'view' => $view,
                    'click' => $click,
                    'percent' => ( $view == 0 ) ? 0 : ($click / $view) * 100
                );

                if( is_array($buttons) && count($buttons) > 0 ){
                    foreach( $buttons as $button ){
                        if( isset($count_agent[$button['agent']] ) )
                            $count_agent[$button['agent']] = $count_agent[$button['agent']] + 1;
                        else
                            $count_agent[$button['agent']] = 1;
                    }
                }
            }
        }

        if( is_array($all_agent) && count($all_agent) > 0 ){
            foreach( $all_agent as $temp_agent ){
                $view = $this->chat_db->twwr_whatsapp_agent_total_by_id( $temp_agent->ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
                $click = $this->chat_db->twwr_whatsapp_agent_total_by_id( $temp_agent->ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );

                $agent[] = array(
                    'name' => $temp_agent->post_title,
                    'img' => get_the_post_thumbnail_url( $temp_agent->ID, 'twwr-whatsapp-chat-thumb' ),
                    'count_chat' => (isset( $count_agent[$temp_agent->ID] )) ? $count_agent[$temp_agent->ID] : 0,
                    'view' => $view,
                    'click' => $click,
                    'percent' => ( $view == 0 ) ? 0 : ($click / $view) * 100
                );
            }
        }

        // get custom logo image
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo_image = wp_get_attachment_image_src( $custom_logo_id , 'large' );

        $data = array(
            'web_name' => get_bloginfo('name'),
            'web_link' => get_bloginfo('url'),
            'logo' => !empty($custom_logo_id) ? $logo_image[0] : plugin_dir_url( __FILE__ ).'/images/whatsapp-icon.png',
            'logo-width' => !empty($custom_logo_id) ? $logo_image[1] : '100',
            'logo-height' => !empty($custom_logo_id) ? $logo_image[2] : '100',
            'chat' => $chat,
            'agent' => $agent
        );

        ob_start();
        include( __DIR__ . '/includes/email-reports.php' );
        $email_content = ob_get_contents();
        ob_end_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( get_bloginfo('admin_email'), get_bloginfo('name') .' '. esc_html__('Agent Daily Report', 'twwr'), $email_content, $headers);
    }

    public function twwr_whatsapp_add_img_size(){
        add_image_size( 'twwr-whatsapp-chat-thumb', 60, 60, true );
    }

    public function twwr_whatsapp_chat_counter(){
        $geo_data = json_decode( stripslashes($_POST['geo']) );
        $ip = isset($geo_data->IPv4) ? $geo_data->IPv4 : '';
        if( !empty($geo_data->city) && !empty($geo_data->country_name) ){
            $location = $geo_data->city.', '.$geo_data->country_name;
        }
        else if( empty($geo_data->city) && !empty($geo_data->country_name) ){
            $location = $geo_data->country_name;
        }
        else{
            $location = __('Unknown', 'twwr');
        }
        
        $browser = $this->twwr_whatsapp_check_browser();
        $os = $browser['os'];
        $browser = $browser['browser'];
        $chat_id = $_POST['chat'];
        $type = $_POST['type'];
        $ref = isset($_POST['ref']) ? $_POST['ref'] : '';

        $data = serialize(array('geo_data' => $geo_data, 'link' => $ref ));
        
        $this->chat_db->twwr_whatsapp_chat_log_insert( $chat_id, $ip, $type, $data, $location, $browser, $os );
    }
    
    public function twwr_whatsapp_chat_agent_counter(){
        $type = $_POST['type'];
        $geo_data = json_decode( stripslashes($_POST['geo']) );
        $ip = isset($geo_data->IPv4) ? $geo_data->IPv4 : '';
        if( !empty($geo_data->city) && !empty($geo_data->country_name) ){
            $location = $geo_data->city.', '.$geo_data->country_name;
        }
        else if( empty($geo_data->city) && !empty($geo_data->country_name) ){
            $location = $geo_data->country_name;
        }
        else{
            $location = __('Unknown', 'twwr');
        }
        $browser = $this->twwr_whatsapp_check_browser();
        $os = $browser['os'];
        $browser = $browser['browser'];
        $agents = $_POST['agent'];
        $agents = explode(',', $agents);
        $numbers = $_POST['number'];
        $numbers = explode(',', $numbers);
        $chats = $_POST['chat'];
        $chats = explode(',', $chats);
        $ref = isset($_POST['ref']) ? $_POST['ref'] : '';

        $data = serialize(array('geo_data' => $geo_data, 'link' => $ref ));
        
        if( is_array($agents) && count($agents) > 1 ){
            foreach( $agents as $key => $agent ){
                $this->chat_db->twwr_whatsapp_agent_log_insert( $agent, $ip, $chats[$key], $type, $numbers[$key], $data, $location, $browser, $os );
            }
            $this->chat_db->twwr_whatsapp_chat_log_insert( $chats[0], $ip, $type, $data, $location, $browser, $os );
        }
        else{
            $this->chat_db->twwr_whatsapp_agent_log_insert( $agents[0], $ip, $chats[0], $type, $numbers[0], $data, $location, $browser, $os );
            $this->chat_db->twwr_whatsapp_chat_log_insert( $chats[0], $ip, $type, $data, $location, $browser, $os );
        }
    }

    public function twwr_whatsapp_check_browser(){
        $browser = new foroco\BrowserDetection();
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $result = $browser->getAll($useragent);
        
        return array( 'os' => $result['os_title'], 'browser' => $result['browser_name'] );
    }

    public function twwr_whatsapp_register_settings(){
        register_setting( 'twwr', 'twwr-whatsapp-chat-setting' );
    }

    public function twwr_whatsapp_check_FBPixel(){
        global $post;

        $status_fb_pixel = false;
        $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array();

        if( get_post_type() == 'product'){
            $twwr_whatsapp_id = get_post_meta( $post->ID, '_twwr_whatsapp_chat_id', true );
            $status_fb_pixel = true;

            if($twwr_whatsapp_id == 'global'){
                $twwr_whatsapp_id = isset($settings['global-id']) ? $settings['global-id'] : '';
            }
        }

        if( get_post_type() == 'twwr_whatsapp_chat' ){
            $status_fb_pixel = true;
            $twwr_whatsapp_id = get_the_ID();
        }

        $pattern = get_shortcode_regex();

        if ( isset($post->post_content) && preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'twwr', $matches[2] ) ) {
            if (preg_match('/id="(.*?)"/', $matches[3][0], $match) == 1) {
                $twwr_whatsapp_id = intval($match[1]);
                $status_fb_pixel = true;
            }
        }
        
        if( isset($settings['global-id']) && !empty($settings['global-id']) && $settings['global-id'] != 'none' ){
            $status_fb_pixel = true;
        }

        if( $status_fb_pixel ): ?>
        <style>
            .twwr-container.twwr-std a{
                background-color: <?php echo (isset($settings['std-bg-color']) && !empty($settings['std-bg-color'])) ? $settings['std-bg-color'] : '#2CD952'; ?>
            }

            .twwr-wa-button{
                background: <?php echo (isset($settings['std-bg-color']) && !empty($settings['std-bg-color'])) ? $settings['std-bg-color'] : '#2CD952'; ?>
            }

            .twwr-wa-button span.agent-detail-fig{
                color: <?php echo (isset($settings['std-text-color']) && !empty($settings['std-text-color'])) ? $settings['std-text-color'] : '#fff'; ?>
            }

            .twwr-wa-button span.agent-name{
                color: <?php echo (isset($settings['std-agent-name-color']) && !empty($settings['std-agent-name-color'])) ? $settings['std-agent-name-color'] : '#fff'; ?>
            }

            .twwr-wa-button.Online span.agent-label span.status{
                color: <?php echo (isset($settings['std-online-color']) && !empty($settings['std-online-color'])) ? $settings['std-online-color'] : '#ffef9f'; ?>
            }

            .twwr-wa-button.Online span.agent-label span.status:before {
                background-color: <?php echo (isset($settings['std-online-color']) && !empty($settings['std-online-color'])) ? $settings['std-online-color'] : '#ffef9f'; ?>
            }
            

            .twwr-container.twwr-floating ul.twwr-whatsapp-content li.available a.twwr-whatsapp-button{
                background-color: <?php echo (isset($settings['agent-box-color']) && !empty($settings['agent-box-color'])) ? $settings['agent-box-color'] : '#2CD952'; ?>  !important;
                color : <?php echo (isset($settings['agent-box-text-color']) && !empty($settings['agent-box-text-color'])) ? $settings['agent-box-text-color'] : '#fff'; ?>
            }
            
            .twwr-container.twwr-floating ul.twwr-whatsapp-content li a.twwr-whatsapp-button span.twwr-whatsapp-text{
                color: <?php echo (isset($settings['agent-box-name-color']) && !empty($settings['agent-box-name-color'])) ? $settings['agent-box-name-color'] : '#fff'; ?>
            }
            
            .twwr-container.twwr-floating ul.twwr-whatsapp-content li.available a.twwr-whatsapp-button span.twwr-whatsapp-text span.twwr-whatsapp-label span.status{
                color: <?php echo (isset($settings['agent-box-online-color']) && !empty($settings['agent-box-online-color'])) ? $settings['agent-box-online-color'] : '#fff'; ?>
            }

            .twwr-container.twwr-floating ul.twwr-whatsapp-content li.unavailable a.twwr-whatsapp-button span.twwr-whatsapp-text span.twwr-whatsapp-label span.status{
                color: <?php echo (isset($settings['agent-box-offline-color']) && !empty($settings['agent-box-offline-color'])) ? $settings['agent-box-offline-color'] : '#bababa'; ?>
            }

            .twwr-container.twwr-floating ul.twwr-whatsapp-content li.twwr-content-header{
                background: <?php echo (isset($settings['floating-background-header-color']) && !empty($settings['floating-background-header-color'])) ? $settings['floating-background-header-color'] : '#03cc0b'; ?>
            }

            .twwr-container.twwr-floating ul.twwr-whatsapp-content li.twwr-content-header h5{
                color: <?php echo (isset($settings['floating-text-header-color']) && !empty($settings['floating-text-header-color'])) ? $settings['floating-text-header-color'] : '#ffffff'; ?>
            }

            .twwr-container span#contact-trigger{
                background: <?php echo (isset($settings['floating-button-color']) && !empty($settings['floating-button-color'])) ? $settings['floating-button-color'] : '#03cc0b'; ?>;
                color: <?php echo (isset($settings['floating-button-text-color']) && !empty($settings['floating-button-text-color'])) ? $settings['floating-button-text-color'] : '#ffffff'; ?>;
            }
        </style>
        <?php endif;

        if( $status_fb_pixel && !empty( $twwr_whatsapp_id ) ) {
            $fb_pix_ids = get_post_meta( $twwr_whatsapp_id, '_twwr_whatsapp_fb_id', true );
            ?>
                <!-- Facebook Pixel Code -->
                <script>
                !function(f,b,e,v,n,t,s) {if (f.fbq)return;n=f.fbq=function() {n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if (!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
                <?php if (isset($fb_pix_ids) && !empty($fb_pix_ids) ) : foreach ($fb_pix_ids as $pixel_id) : if( !empty($pixel_id) ) :?>
                    fbq('init', '<?php echo esc_attr($pixel_id); ?>'); // Insert your pixel ID here.
                <?php endif; endforeach; endif; ?>
                    fbq('track', 'PageView');
                </script><noscript>
                <?php if (isset($fb_pix_ids) && !empty($fb_pix_ids) ) : foreach ($fb_pix_ids as $pixel_id) : if( !empty($pixel_id) ) :?>
                    <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1" />
                <?php endif; endforeach; endif; ?>
                </noscript>
                <!-- DO NOT MODIFY -->
                <!-- End Facebook Pixel Code -->
            <?php
        }


    }

    public function twwr_whatsapp_enqueue() {
        global $post;

        $twwr_whatsapp_id = get_post_meta(get_the_ID(), '_twwr_whatsapp_chat_id', true);
        $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : '' ;

        if( (empty($twwr_whatsapp_id) || $twwr_whatsapp_id == 'global') && get_post_type($post) == 'product' ){
            $twwr_whatsapp_id = isset($settings['woocommerce-id']) ? $settings['woocommerce-id'] : false;
        }

        if( (empty($twwr_whatsapp_id) || $twwr_whatsapp_id == 'global') && get_post_type($post) != 'product' ){
            $twwr_whatsapp_id = isset($settings['global-id']) ? $settings['global-id'] : false;
        }
        
        if ( $twwr_whatsapp_id == 'none' ) {
            $twwr_whatsapp_id = false;
        }
        
        if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'twwr-whatsapp-chat') )
            $twwr_whatsapp_id = true;

        if ( $twwr_whatsapp_id ) {
            wp_enqueue_style( 'twwr-chat-agents', $this->pluginUrl .'css/style.css', array(), false, 'all' );
            wp_enqueue_style( 'twwr-chat-redirect', $this->pluginUrl .'css/wa-redirect.css', array(), false, 'all' );

            wp_enqueue_script( 'twwr-whatsapp-functions', $this->pluginUrl .'js/functions.js', array('jquery'), null, true );
            wp_localize_script('twwr-whatsapp-functions', 'twwr_whatsapp_chat', array(
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
    }

    public function twwr_whatsapp_enqueue_admin() {
        if ( is_admin() &&
        ( (isset($_GET['post_type']) && ( $_GET['post_type'] == 'twwr_whatsapp_chat' || $_GET['post_type'] == 'twwr_whatsapp_agent' )) ||
        ( get_post_type() == 'twwr_whatsapp_agent' || get_post_type() == 'twwr_whatsapp_chat' ) ) ) {
            wp_enqueue_style('thickbox');
            wp_enqueue_style( 'slider', $this->pluginUrl .'/css/ion.rangeSlider.min.css', array(), false, 'all' );
            wp_enqueue_style( 'admin', $this->pluginUrl .'/css/style-admin.css', array(), false, 'all' );

            wp_enqueue_script('thickbox');
            wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'moment', $this->pluginUrl .'/js/moment-with-locales.min.js', array('jquery'), null, true );
			wp_enqueue_script( 'slider', $this->pluginUrl .'/js/ion.rangeSlider.min.js', array('jquery'), null, true );
            wp_enqueue_script( 'jquery.date-dropdowns', $this->pluginUrl .'/js/jquery.date-dropdowns.min.js', array('jquery'), null, true );
            wp_enqueue_style( 'wickedpicker.style', $this->pluginUrl .'/css/wickedpicker.min.css', array(), false, 'all' );
            wp_enqueue_script( 'wickedpicker.script', $this->pluginUrl .'/js/wickedpicker.min.js', array('jquery'), null, true );
            wp_enqueue_script( 'twwr-whatsapp-admin-functions', $this->pluginUrl .'/js/admin-functions.js', array('jquery', 'wp-color-picker'), null, true );
            wp_localize_script('twwr-whatsapp-admin-functions', 'twwr_whatsapp_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'delete_success' => __('Your log has been deleted', 'twwr'),
                'delete_failed' => __('Delete action failed', 'twwr'),
            ));
        }
    }

    public function twwr_whatsapp_chat_template($template) {
        global $post, $wp;

        if ( is_single() && $post->post_type == 'twwr_whatsapp_chat' ) {
            $template = plugin_dir_path( __FILE__ ) . '/includes/template-chat.php';
        }

        return $template;
    }

    public function twwr_whatsapp_chat_get_city_from_ip($ip='') {
        if ( !empty($ip) && $ip != '127.0.0.1' ) {
            $resp = wp_remote_post( 'https://geolocation-db.com/json/'.$ip );

            $body = wp_remote_retrieve_body( $resp );

            if ( empty( $body ) ) {
				return new WP_Error( 'twwr_whatsapp_chat_empty_ip', __( 'Error on request geo data by IP.', 'twwr' ) );
			}

			$data = json_decode( $body );
            return $data;
        } else if ( $ip == '127.0.0.1' ) {
            return (object) array(
                'city' => 'localhost',
            );
        } else {
            return false;
        }
    }

    public function twwr_whatsapp_chat_update_counter( $post_id, $meta ) {
        if ( !empty($post_id) && !empty($meta) ) {
            $count = get_post_meta( $post_id, $meta, true );
            
            if ( ! $count ) {
                $count = 0;  // if the meta value isn't set, use 0 as a default
            }
            
            $count++;
            update_post_meta( $post_id, $meta, $count );
        }
    }

    public function twwr_whatsapp_chat_update_log( $post_id, $meta, $data ) {
        if ( !empty($post_id) && !empty($meta) && !empty($data) ) {
            $log = get_post_meta( $post_id, $meta, true );
            $log = json_decode($log);

            if ( empty($log) ) {
                $log = array();
            }
            
            $log[] = $data;
            $encode_log = json_encode($log);
            update_post_meta( $post_id, $meta, $encode_log );
        }
    }

    public function twwr_whatsapp_shortcode( $atts ) {
        global $wp;

        $twwr_atts = shortcode_atts( array(
            'id' => 3,
            'style' => 'none'
        ), $atts);

        $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array() ;
        $twwr_whatsapp_id = $twwr_atts['id'];
        $twwr_whatsapp_inline_style = $twwr_atts['style'];
        $twwr_whatsapp_style = 'twwr-woocommerce';
        $dis = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_display', true);
        $buttons = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_button', true);
        $information = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_information', true);
        $msg = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_message', true);
        $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events', true);
        $buttons = $this->twwr_whatsapp_check_available( $buttons, $dis, $twwr_whatsapp_id );

        $label = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_label', true);
        $header = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header', true);
        $header_desc = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header_description', true);
        $link = home_url( $wp->request ); // get current url

        if( $fb_pix == 'Custom')
            $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events_custom', true);

        if($twwr_whatsapp_inline_style == 'block'){
            $twwr_whatsapp_style .= ' single-row';
        }
        
        ob_start();
        ?>

        <?php if ( $twwr_whatsapp_style == 'twwr-floating' ) :
            $twwr_whatsapp_position = isset($settings['general-position']) ? $settings['general-position'] : 'bottom-right';
            if( wp_is_mobile() ){
                $twwr_whatsapp_position_edge = isset($settings['general-mobile-edge']) ? $settings['general-mobile-edge'] : '30';
                $twwr_whatsapp_position_bottom = isset($settings['general-mobile-bottom']) ? $settings['general-mobile-bottom'] : '30';
            }
            else{
                $twwr_whatsapp_position_edge = isset($settings['general-desktop-edge']) ? $settings['general-desktop-edge'] : '30';
                $twwr_whatsapp_position_bottom = isset($settings['general-desktop-bottom']) ? $settings['general-desktop-bottom'] : '30';
            }

            $pos = '';

            if ( !empty($twwr_whatsapp_position_edge) || !empty($twwr_whatsapp_position_bottom) ) {
                $pos = 'bottom:'.$twwr_whatsapp_position_bottom.'px;';
                if ( $twwr_whatsapp_position == 'bottom-right' ) {
                    $pos .= 'right:'.$twwr_whatsapp_position_edge.'px;';
                } elseif ( $twwr_whatsapp_position == 'bottom-left' ) {
                    $pos .= 'left:'.$twwr_whatsapp_position_edge.'px;';
                }
            }

            usort($buttons, function($a, $b) {
                return $a['priority'] + $b['priority'];
            });

            $keys = array();
            for ($i = 0; $i < count($buttons); $i++) {
                for ($u = $i; $u < count($buttons); $u++) {
                    $keys[] = $i;
                }
            }

            shuffle($keys);
            $buttons = array( $buttons[ $keys[ rand(0, count($keys) - 1) ] ] );
            ?>
            <div class="twwr-container <?php esc_attr_e($twwr_whatsapp_style.' '.$twwr_whatsapp_position) ?>" style="<?php esc_attr_e( $pos ) ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id );?>">
                <span id="contact-trigger"><img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg'); ?>"><?php esc_html_e ( $label ); ?></span>
                <ul class="twwr-whatsapp-content">
                    <li class="twwr-content-header">
                        <a class="close-chat" title="Close Support"><?php _e('Close', 'twwr'); ?></a>
                        <img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg' ); ?>"> <h5><?php esc_html_e ( $header ); ?> <span><?php esc_html_e ( $header_desc ); ?></span></h5>
                    </li>
                    <?php if( is_array($buttons) && count($buttons) > 0 ) :
                    foreach( $buttons as $wa ) : ?>
                        <li class="available">
                            <a class="twwr-whatsapp-button" href="<?php echo get_permalink($twwr_whatsapp_id).'?agent='.$wa['agent'].'&number='.$wa['number'].'&ref='.$link; ?>" target="_blank" data-agent="<?php esc_attr_e( $wa['agent'] ); ?>" data-number="<?php esc_attr_e( $wa['number'] ); ?>" data-chat="<?php esc_attr_e( $twwr_whatsapp_id ); ?>" rel="nofollow">
                                <?php echo get_the_post_thumbnail($wa['agent'], 'twwr-whatsapp-chat-thumb', array('class' => 'twwr-whatsapp-avatar')); ?>
                                <span class="twwr-whatsapp-text">
                                    <span class="twwr-whatsapp-label">
                                        <?php
                                        $departments = wp_get_post_terms($wa['agent'], 'twwr_agent_department');
                                        if ( $departments ) {
                                            $output = array();
                                            foreach ($departments as $department) {
                                                $output[] = $department->name;
                                            }
                                            echo join( ', ', $output );
                                            echo ' - ';
                                        };
                                        ?>
                                        <span class="status"><?php esc_html_e('Online', 'twwr'); ?></span>
                                    </span>

                                    <?php echo get_the_title($wa['agent']); ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach;
                    endif; ?>

                    <li class="twwr-content-footer">
                        <p><?php echo esc_html_e( $information ); ?></p>
                    </li>
                </ul>
            </div>
        <?php else : ?>
            <div class="twwr-container twwr-chat-std <?php esc_attr_e( $twwr_whatsapp_style ); ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id );?>">
                <?php if ($buttons ) :
                    if ( $dis == 'all' ) {
                        foreach( $buttons as $button ) {
                            $this->twwr_whatsapp_render_button($twwr_whatsapp_id, $button, $twwr_whatsapp_style);
                        }
                    } else {
                        usort($buttons, function($a, $b) {
                            return $a['priority'] + $b['priority'];
                        });

                        $keys = array();
                        for ($i = 0; $i < count($buttons); $i++) {
                            for ($u = $i; $u < count($buttons); $u++) {
                                $keys[] = $i;
                            }
                        }
                        shuffle($keys);

                        $this->twwr_whatsapp_render_button($twwr_whatsapp_id, $buttons[ $keys[ rand(0, count($keys) - 1) ] ], $twwr_whatsapp_style);
                    }
                    ?>
                <?php endif; ?>
            </div>
        <?php endif;
        return ob_get_clean();
    }

    function twwr_whatsapp_render_standard_chat() {
        global $post, $wp;

        if( is_single() && $post->post_type == 'twwr_whatsapp_chat' )
            return false;

        $twwr_whatsapp_id = get_post_meta(get_the_ID(), '_twwr_whatsapp_chat_id', true);
        
        if( empty($twwr_whatsapp_id) || $twwr_whatsapp_id == 'global' ){
            $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : '' ;
            $twwr_whatsapp_id = isset($settings['woocommerce-id']) ? $settings['woocommerce-id'] : false;
        }
        
        if ( $twwr_whatsapp_id == 'none' ) {
            return false;
        }
        
        if ( $twwr_whatsapp_id ) {
            $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array() ;
            $twwr_whatsapp_style = 'twwr-woocommerce';
            $label = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_label', true);
            $header = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header', true);
            $header_desc = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header_description', true);
            $dis = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_display', true);
            $buttons = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_button', true);
            $information = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_information', true);
            $msg = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_message', true);
            $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events', true);
            $buttons = $this->twwr_whatsapp_check_available( $buttons, $dis, $twwr_whatsapp_id );
            $link = home_url( $wp->request );
            $product = ( function_exists('wc_get_product') ) ? wc_get_product() : false;
            if( $product ){
                $link = get_permalink();
            }

            if ( $fb_pix == 'Custom' )
                $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events_custom', true);

            if ( $twwr_whatsapp_style == 'twwr-floating' ) :
                $twwr_whatsapp_position = isset($settings['general-position']) ? $settings['general-position'] : 'bottom-right';
                if( wp_is_mobile() ){
                    $twwr_whatsapp_position_edge = isset($settings['general-mobile-edge']) ? $settings['general-mobile-edge'] : '30';
                    $twwr_whatsapp_position_bottom = isset($settings['general-mobile-bottom']) ? $settings['general-mobile-bottom'] : '30';
                }
                else{
                    $twwr_whatsapp_position_edge = isset($settings['general-desktop-edge']) ? $settings['general-desktop-edge'] : '30';
                    $twwr_whatsapp_position_bottom = isset($settings['general-desktop-bottom']) ? $settings['general-desktop-bottom'] : '30';
                }

                $pos = '';

                if ( !empty($twwr_whatsapp_position_edge) || !empty($twwr_whatsapp_position_bottom) ) {
                    $pos = 'bottom:'.$twwr_whatsapp_position_bottom.'px;';
                    if ( $twwr_whatsapp_position == 'bottom-right' ) {
                        $pos .= 'right:'.$twwr_whatsapp_position_edge.'px;';
                    } elseif ( $twwr_whatsapp_position == 'bottom-left' ) {
                        $pos .= 'left:'.$twwr_whatsapp_position_edge.'px;';
                    }
                }
                ?>
                <div class="twwr-container  <?php esc_attr_e( $twwr_whatsapp_style.' '.$twwr_whatsapp_position ); ?>" style="<?php esc_attr_e( $pos ); ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id );?>">
                    <span id="contact-trigger"><img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg'); ?>"><?php esc_html_e ( $label ); ?></span>
                    <ul class="twwr-whatsapp-content">
						<li class="twwr-content-header">
							<a class="close-chat" title="Close Support"><?php _e('Close', 'twwr'); ?></a>
							<img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg' ); ?>"> <h5><?php esc_html_e ( $header ); ?> <span><?php esc_html_e ( $header_desc ); ?></span></h5>
                        </li>
                        <?php if( is_array($buttons) && count($buttons) > 0 ) :
                        foreach( $buttons as $wa ) : 
                        ?>
                            <li class="<?php echo ($wa['available']) ? 'available' : 'unavailable'; ?>">
                                <a class="twwr-whatsapp-button" href="<?php echo get_permalink($twwr_whatsapp_id).'?agent='.$wa['agent'].'&number='.$wa['number'].'&ref='.$link; ?>" target="_blank" data-agent="<?php esc_attr_e( $wa['agent'] ); ?>"  data-number="<?php esc_attr_e( $wa['number'] ); ?>" data-chat="<?php esc_attr_e( $twwr_whatsapp_id ); ?>" rel="nofollow">
                                    <?php echo get_the_post_thumbnail($wa['agent'], 'twwr-whatsapp-chat-thumb', array('class' => 'twwr-whatsapp-avatar')); ?>
                                    <span class="twwr-whatsapp-text">
                                        <span class="twwr-whatsapp-label">
                                            <?php
                                            $departments = wp_get_post_terms($wa['agent'], 'twwr_agent_department');
                                            if ( $departments ) {
                                                $output = array();
                                                foreach ($departments as $department) {
                                                    $output[] = $department->name;
                                                }
                                                echo join( ', ', $output ) .' - ';
                                            };
                                            $status = __('Online', 'twwr');
                                            if( $wa['online'] == false )
                                                $status = __('Offline', 'twwr');
                                            else if ($wa['available'] == false)
                                                $status = __('Be back soon', 'twwr');
                                            ?>
                                            <span class="status"><?php esc_html_e ( $status ); ?></span>
                                        </span>

                                        <?php echo get_the_title($wa['agent']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach;
                        endif; ?>

						<li class="twwr-content-footer">
							<p><?php esc_html_e ( $information ); ?></p>
						</li>
                    </ul>
                </div>
            <?php else : ?>
                <div class="twwr-container twwr-std <?php esc_attr_e( $twwr_whatsapp_style ); ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id );?>">
                    <?php if ($buttons ) : ?>
                        <?php if( $information ) : ?>
                            <p class="contact-message"><?php esc_html_e ( $information ); ?></p>
                        <?php endif; ?>

                        <?php
                        foreach( $buttons as $button ) {
                            $this->twwr_whatsapp_render_button($twwr_whatsapp_id, $button, $twwr_whatsapp_style);
                        }
                        ?>
                    <?php endif; ?>
                </div>
            <?php endif;
        }
    }

    function twwr_whatsapp_render_chat() {
        global $post, $wp;

        if( is_single() && ($post->post_type == 'twwr_whatsapp_chat' || $post->post_type == 'product') )
            return false;

        $twwr_whatsapp_style = 'twwr-woocommerce';
        $twwr_whatsapp_id = get_post_meta(get_the_ID(), '_twwr_whatsapp_chat_id', true);
        
        if( empty($twwr_whatsapp_id) || $twwr_whatsapp_id == 'global' ){
            $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : '' ;
            $twwr_whatsapp_id = isset($settings['global-id']) ? $settings['global-id'] : false;
            $twwr_whatsapp_style = 'twwr-floating';
        }
        
        if ( $twwr_whatsapp_id == 'none' ) {
            return false;
        }
        
        if ( $twwr_whatsapp_id ) {
            $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array() ;
            $label = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_label', true);
            $header = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header', true);
            $header_desc = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header_description', true);
            $dis = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_display', true);
            $dis_type = isset($settings['twwr_whatsapp_display_type']) ? $settings['twwr_whatsapp_display_type'] : 'icon-text';
            $buttons = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_button', true);
            $information = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_information', true);
            $msg = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_message', true);
            $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events', true);
            $buttons = $this->twwr_whatsapp_check_available( $buttons, $dis, $twwr_whatsapp_id );

            if ( $fb_pix == 'Custom' )
                $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events_custom', true);

            if ( $twwr_whatsapp_style == 'twwr-floating' ) :
                $twwr_whatsapp_position = isset($settings['general-position']) ? $settings['general-position'] : 'bottom-right';
                if( wp_is_mobile() ){
                    $twwr_whatsapp_position_edge = isset($settings['general-mobile-edge']) ? $settings['general-mobile-edge'] : '30';
                    $twwr_whatsapp_position_bottom = isset($settings['general-mobile-bottom']) ? $settings['general-mobile-bottom'] : '30';
                }
                else{
                    $twwr_whatsapp_position_edge = isset($settings['general-desktop-edge']) ? $settings['general-desktop-edge'] : '30';
                    $twwr_whatsapp_position_bottom = isset($settings['general-desktop-bottom']) ? $settings['general-desktop-bottom'] : '30';
                }

                $pos = '';

                if ( !empty($twwr_whatsapp_position_edge) || !empty($twwr_whatsapp_position_bottom) ) {
                    $pos = 'bottom:'.$twwr_whatsapp_position_bottom.'px;';
                    if ( $twwr_whatsapp_position == 'bottom-right' ) {
                        $pos .= 'right:'.$twwr_whatsapp_position_edge.'px;';
                    } elseif ( $twwr_whatsapp_position == 'bottom-left' ) {
                        $pos .= 'left:'.$twwr_whatsapp_position_edge.'px;';
                    }
                }
                ?>

                <div class="twwr-container twwr-floating <?php esc_attr_e( $twwr_whatsapp_style.' '.$twwr_whatsapp_position ); ?>" style="<?= $pos ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id );?>">

                    <?php if( $dis_type == 'icon-text' ) : ?>
                        <span id="contact-trigger"><img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg' ); ?>"><?php esc_html_e ( $label ); ?></span>
                    <?php else : ?>
                        <span id="contact-trigger" class="twwr-whatsapp-icon-only"><img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg' ); ?>"></span>
                    <?php endif; ?>

                    <div id="notification-badge">1</div>

                    <ul class="twwr-whatsapp-content">
						<li class="twwr-content-header">
							<a class="close-chat" title="Close Support"><?php _e('Close', 'twwr'); ?></a>
							<img class="icon" src="<?php echo esc_url( $this->pluginUrl.'/images/whatsapp-icon-a.svg' ); ?>"> <h5><?php esc_html_e ( $header ); ?> <span><?php esc_html_e ( $header_desc ); ?></span></h5>
                        </li>
                        <?php if( is_array($buttons) && count($buttons) > 0 ) :
                        foreach( $buttons as $wa ) : 
                        $link = home_url( $wp->request ); // get current url
                        ?>
                            <li class="<?php echo ($wa['available']) ? 'available' : 'unavailable'; ?>">
                                <a class="twwr-whatsapp-button" href="<?php echo get_permalink($twwr_whatsapp_id).'?agent='.$wa['agent'].'&number='.$wa['number'].'&ref='.$link; ?>" target="_blank" data-agent="<?php esc_attr_e( $wa['agent'] ); ?>"  data-number="<?php esc_attr_e( $wa['number'] ); ?>" data-chat="<?php esc_attr_e( $twwr_whatsapp_id ); ?>" rel="nofollow">
                                    <?php echo get_the_post_thumbnail($wa['agent'], 'twwr-whatsapp-chat-thumb', array('class' => 'twwr-whatsapp-avatar')); ?>
                                    <span class="twwr-whatsapp-text">
                                        <span class="twwr-whatsapp-label">
                                            <?php
                                            $departments = wp_get_post_terms($wa['agent'], 'twwr_agent_department');
                                            if ( $departments ) {
                                                $output = array();
                                                foreach ($departments as $department) {
                                                    $output[] = $department->name;
                                                }
                                                echo join( ', ', $output ) .' - ';
                                            };
                                            $status = __('Online', 'twwr');
                                            if( $wa['online'] == false )
                                                $status = __('Offline', 'twwr');
                                            else if ($wa['available'] == false)
                                                $status = __('Be back soon', 'twwr');
                                            ?>
                                            <span class="status"><?php esc_html_e( $status ); ?></span>
                                        </span>

                                        <?php echo get_the_title($wa['agent']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach;
                        endif; ?>

						<li class="twwr-content-footer">
							<p><?php esc_html_e( $information ); ?></p>
						</li>
                    </ul>
                    <audio id="twwr-whatsapp-audio" preload="auto">
                        <source src="<?php echo plugin_dir_url( __FILE__ ); ?>audio-files/alert.mp3" type="audio/mpeg" />
                    </audio>
                    <script type="text/javascript">
                        setTimeout(function() {
                          document.getElementById('twwr-whatsapp-audio').play();
                        }, 3000)
                    </script>

                </div>
            <?php endif;
        }
    }

    function twwr_whatsapp_render_button( $chat_id, $button, $type ) {
        global $wp;

        $link = home_url( $wp->request );
        $product = ( function_exists('wc_get_product') ) ? wc_get_product() : false;
        if( $product ){
            $link = get_permalink();
        }

        $departments = wp_get_post_terms($button['agent'], 'twwr_agent_department');

        if ( $departments ) {
            $output = array();
            foreach ($departments as $department) {
                $output[] = $department->name;
            }
            $departments_join = join( ', ', $output );
        };

        $status = esc_html__('Online', 'twwr');
        $link = get_permalink($chat_id).'?agent='.$button['agent'].'&number='.$button['number'].'&ref='.$link;

        if ( $button['online'] == false ) {
            $status = esc_html__('Offline', 'twwr');
            $link = '';
        } else if ($button['available'] == false) {
            $status = esc_html__('Be back soon', 'twwr');
        }
        ?>

        <div class="agent-list-item">

            <a href="<?php echo esc_url( $link ); ?>"" class="twwr-wa-button <?php esc_attr_e( $status ); ?>" rel="nofollow" title="<?php esc_attr_e( $status ); ?> | Hubungi : <?php esc_html_e ( $button['number'] ); ?> (<?php echo get_the_title($button['agent']); ?>)" data-agent="<?php esc_attr_e( $button['agent'] ); ?>"  data-number="<?php esc_attr_e( $button['number'] ); ?>" data-chat="<?php esc_attr_e( $chat_id ); ?>">
                <span class="agent-avatar-fig">
                    <?php echo get_the_post_thumbnail($button['agent'], 'twwr-whatsapp-chat-thumb', array('class' => 'twwr-whatsapp-avatar')); ?>
                </span>
                <span class="agent-detail-fig">
                    <span class="agent-label">
                        <span class="agent-name"><?php echo get_the_title($button['agent']); ?></span>
                        <span class="department"><?php esc_html_e( $departments_join ); ?></span>
                        <span class="status"><?php esc_html_e( $status ); ?></span>
                    </span>
                    
                    <span class="agent-message"><?php esc_html_e( 'Need Help? Chat us via Whatsapp', 'twwr' ); ?></span>
                    <span class="agent-number"><?php esc_html_e( 'WA :', 'twwr' ); ?> <?php esc_html_e( $button['number'] ); ?></span>
                </span>
                <span class="chat"><?php esc_html_e( 'Chat', 'twwr' ); ?></span>
            </a>
        </div>
        <?php
    }

    function twwr_whatsapp_check_available( $buttons, $display = 'all', $twwr_whatsapp_id ){
        if( !empty($buttons) && is_array($buttons) ){
            $new_buttons = array();
            foreach( $buttons as $but ){
                $available = false;
                $include = false;
                $allday = get_post_meta($but['agent'], '_twwr_whatsapp_working_day_allday_status', true );
                
                if( $allday == '1' ){
                    $available = true;
                    $include = true;
                }
                else{
                    $day_key = '_twwr_whatsapp_working_day_'.strtolower(current_time('l')).'_status';
                    $status_day = get_post_meta($but['agent'], $day_key, true );
                    
                    if( $status_day == '1' ){
                        $time_now = current_time('H:i');
                        $day_start = get_post_meta($but['agent'], '_twwr_whatsapp_working_day_'.strtolower(date('l')).'_start', true );
                        $day_break = get_post_meta($but['agent'], '_twwr_whatsapp_working_day_'.strtolower(date('l')).'_break', true );
                        $day_break_end = get_post_meta($but['agent'], '_twwr_whatsapp_working_day_'.strtolower(date('l')).'_break_end', true );
                        $day_end = get_post_meta($but['agent'], '_twwr_whatsapp_working_day_'.strtolower(date('l')).'_end', true );

                        if( $day_start < $time_now && $day_end > $time_now ){
                            $include = true;
                            if( !empty($day_break) && $time_now < $day_break || $time_now > $day_break_end ){
                                $available = true;
                            }
                        }

                    }
                }

                $but['online'] = $include;
                $but['available'] = $available;
                $new_buttons[] = $but;
            }

            if ( $display == 'random' ) {
                $priority = get_option('twwa_random_priority_'.$twwr_whatsapp_id.'_'.count($buttons));
                
                if( $priority == false || empty($priority) ){
                    $priority = array();
                    foreach( $new_buttons as $key => $new ){
                        if( empty($new['priority']) )
                            $new['priority'] = 1;
                            
                        for( $i=0; $i < $new['priority']; $i++ )
                            $priority[] = $key;
                    }
                    shuffle($priority);
                }
                $priority = array_values($priority);
                $key = rand(0, count($priority) - 1);
                $new_buttons = array( $new_buttons[ $priority[$key] ] );
                unset($priority[$key]);

                update_option( 'twwa_random_priority_'.$twwr_whatsapp_id.'_'.count($buttons), $priority );
            }

            return $new_buttons;
        }
    }

    function twwr_whatsapp_load_languages() {
        $domain = 'twwr';
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    function twwr_whatsapp_redirect(){
        if ( ! is_admin() )
            return;
        
        $screen = get_current_screen();
        if( $screen->id == 'twwr_whatsapp_chat_page_twwr-welcome-page' || $screen->id == 'twwr_whatsapp_chat_page_twwr-setting-page' || $screen->action == 'add' )
            return;
        
        if( is_object($screen) && $screen->post_type == 'twwr_whatsapp_chat'){
            $raw = (array)wp_count_posts('twwr_whatsapp_chat');
            unset($raw['auto-draft']);
            $chats = array_sum( $raw );
            
            if( $chats <= 0  && ( !isset( $_POST['post_type'] ) )){
                wp_safe_redirect( admin_url('/edit.php?post_type=twwr_whatsapp_chat&page=twwr-welcome-page') );
                die();
            }
        }
        
        if( is_object($screen) && $screen->post_type == 'twwr_whatsapp_agent'){
            $raw = (array)wp_count_posts('twwr_whatsapp_agent');
            unset($raw['auto-draft']);
            $agents = array_sum( $raw );
            
            if( $agents <= 0  && ( !isset( $_POST['post_type'] ) )){
                wp_safe_redirect( admin_url('/edit.php?post_type=twwr_whatsapp_chat&page=twwr-welcome-page') );
                die();
            }
        }
    }

    function twwr_whatsapp_admin_menu() {
        add_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', __('WhatsApp Chat Rotator Settings', 'twwr'), 'Settings', 'manage_options', 'twwr-setting-page', array($this, 'twwr_whatsapp_setting_page') );
        add_submenu_page( null, __('Welcome to TW WhatsApp Chat Rotator', 'twwr'),  __('Welcome to TW WhatsApp Chat Rotator', 'twwr'), 'manage_options', 'twwr-welcome-page', array($this, 'twwr_whatsapp_welcome') );

        // Remove Add New sub menus
        remove_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', 'post-new.php?post_type=twwr_whatsapp_chat' );
        remove_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', 'post-new.php?post_type=twwr_whatsapp_agent' );
    }

    function twwr_whatsapp_welcome(){
        ?>
        <div class="twwr-welcome-panel">
            <div class="twwr-welcome-panel-content">
                <h1 class="twwr-welcome"><?php esc_html_e( 'Welcome to TW WhatsApp Chat Rotator', 'twwr' ); ?></h1>
                <p><?php esc_html_e( 'To start using this plugin please add an agent, then create a new WhatsApp Chat', 'twwr' ); ?></p>

                <div class="twwr-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=twwr_whatsapp_agent'); ?>" class="button button-primary button-hero"><i class="dashicons dashicons-businessman"></i> <?php esc_html_e( 'Add Agent', 'twwr' ); ?></a>

                    <a href="<?php echo admin_url('post-new.php?post_type=twwr_whatsapp_chat'); ?>" class="button button-primary button-hero"><i class="dashicons dashicons-phone"></i> <?php esc_html_e( 'Add WhatsApp Chat', 'twwr' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    function twwr_whatsapp_save_meta_box( $post_id ) {
        if ( array_key_exists( 'twwr_whatsapp_id', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twwr_whatsapp_chat_id',
                $_POST['twwr_whatsapp_id']
            );
        }
    }

    function twwr_whatsapp_meta_box() {
        add_meta_box(
            'twwr_whatsapp_post_meta_box', // Unique ID
            esc_html__('Contact Button Rotator', 'twwr'), // Box title
            array($this, 'twwr_whatsapp_meta_post_box_html'), // Content callback, must be of type callable
            array('product'), // Post type
            'normal',
            'high'
        );
    }

    function twwr_whatsapp_meta_post_box_html( $post ) {
        if( false ) { ?>
            <div class="form-field">
                <p class="twwr-error"><?php echo esc_html__('Please activate plugin ThemeWarrior WA Rotator first.', 'twwr'); ?></p>
            </div>
        <?php }
        else{
            $id = get_post_meta($post->ID, '_twwr_whatsapp_chat_id', true);
            $args = array(
                'post_type'             => 'twwr_whatsapp_chat',
                'post_status'           => 'publish'
            );

            $wp_query = new WP_Query();
            $wp_query->query( $args );
            ?>

            <?php if ( $wp_query->have_posts() ) : ?>
                <div class="form-field">
                    <label for="twwr_whatsapp_id"><?php echo esc_html__('Choose Button Contact Rotator :', 'twwr'); ?></label>
                    <select name="twwr_whatsapp_id" class="twwr_whatsapp_id">
                        <option value="global" <?php echo ($id == 'global')? 'selected="selected"' : ''; ?>><?php echo esc_html__('Global Setting', 'twwr'); ?></option>
                        <option value="none" <?php echo ($id == 'none')? 'selected="selected"' : ''; ?>><?php echo esc_html__('Non-active', 'twwr'); ?></option>
                        <?php while( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
                        <option value="<?php the_ID(); ?>" <?php echo ($id == get_the_ID())? 'selected="selected"' : ''; ?>><?php the_title(); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php else : ?>
                <div class="form-field">
                    <label class="error"><?php echo sprintf(__('Please add a <a href="%s">Whatsapp Chat</a> first', 'twwr'), admin_url('edit.php?post_type=twwr_whatsapp_chat') ); ?></label>
                </div>
            <?php endif;
        }
    }

    function twwr_whatsapp_setting_page(){
        settings_errors();
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WhatsApp Chat Rotator Settings', 'twwr'); ?></h1>
        </div>

        <h2 class="nav-tab-wrapper">
			<a href="?post_type=twwr_whatsapp_chat&page=twwr-setting-page&tab=general" class="nav-tab <?php esc_attr_e( $active_tab == 'general' ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'General Settings', 'twwr' ) ?></a>
			<a href="?post_type=twwr_whatsapp_chat&page=twwr-setting-page&tab=appearance" class="nav-tab <?php esc_attr_e( $active_tab == 'appearance' ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Appearance', 'twwr' ) ?></a>
		</h2>

        <form method="post" action="options.php" class="twwr-settings-form">
            <?php
            settings_fields( 'twwr' );
            do_settings_sections( 'twwr' );
            $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array() ;
            $timestamp = wp_next_scheduled( 'twwr_whatsapp_chat_report_cron' );
            wp_unschedule_event( $timestamp, 'twwr_whatsapp_chat_report_cron' );

            $id = @$settings['global-id'];
            $wc_id = @$settings['woocommerce-id'];
            $cron = (isset($settings['daily-cron']) && !empty($settings['daily-cron']) ) ? $settings['daily-cron'] : '10:00 AM';
            $cron = date("H:i", strtotime($cron));
            $slug = (isset($settings['slug']) && !empty($settings['slug']) ) ? $settings['slug'] : 'wa';
            if( empty($settings) )
                flush_rewrite_rules();

            $args = array(
                'post_type'             => 'twwr_whatsapp_chat',
                'post_status'           => 'publish'
            );

            $wp_query = new WP_Query();
            $wp_query->query( $args );
            ?>
            <table class="form-table twwr-settings-general" style=<?php esc_attr_e( $active_tab == 'general' ? '' : 'display:none' ); ?>>
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_information"><?php echo esc_html__('Date Time Server', 'twwr'); ?></label>
                        </th>
                        <td>
                            <code><?php echo current_time('r'); ?></code>.
                            <?php
                            // get time zone
                            esc_html_e( 'Timezone: ', 'twwr' );

                            if ( get_option('gmt_offset') > 0 ) {
                                $utc = 'UTC+'.get_option('gmt_offset');
                            } else {
                                $utc = 'UTC-'.get_option('gmt_offset');
                            }

                            echo '<code>'. $utc .'</code>';

                            if ( get_option( 'timezone_string' ) ) {
                                echo '<code>'. get_option( 'timezone_string' ) .'</code>';
                            }
                            ?>
                            <span class="description"><?php echo sprintf(esc_html__( 'If the timezone doesn\'t match your current location, you can change it from %s', 'twwr' ), make_clickable(admin_url('/options-general.php#timezone_string'))); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'WhatsApp Chat on All Pages', 'twwr' ); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_information"><?php echo esc_html__('Default WhatsApp Chat on All pages', 'twwr'); ?></label>
                        </th>
                        <td>
                            <select name="twwr-whatsapp-chat-setting[global-id]" class="twwr_whatsapp_display">
                                <option value="none"><?php _e('Choose WhatsApp a chat...', 'twwr'); ?></option>
                                <?php while( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
                                    <option value="<?php the_ID(); ?>" <?php echo ($id == get_the_ID())? 'selected="selected"' : ''; ?>><?php the_title(); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span class="description"><?php esc_html_e( 'Set default WhatsApp chat for all pages, only floating WhatsApp chat will be displayed.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_position"><?php echo esc_html__('Button Position', 'twwr'); ?></label>
                        </th>
                        <td>
                            <select name="twwr-whatsapp-chat-setting[general-position]" class="twwr_wa_position">
                                <option value="bottom-right" <?php echo (isset($settings['general-position']) && $settings['general-position'] == 'bottom-right')? 'selected="selected"' : ''; ?>><?php echo esc_html__('Bottom right', 'twwr'); ?></option>
                                <option value="bottom-left" <?php echo (isset($settings['general-position']) && $settings['general-position'] == 'bottom-left')? 'selected="selected"' : ''; ?>><?php echo esc_html__('Bottom left', 'twwr'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_position"><?php echo esc_html__('Margin from edge (Desktop)', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="twwr-whatsapp-chat-setting[general-desktop-edge]" value="<?php echo ( isset($settings['general-desktop-edge']) && !empty($settings['general-desktop-edge']) ) ? $settings['general-desktop-edge'] : 30; ?>" min="0" class="small-text" /> <code>px</code>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_position"><?php echo esc_html__('Margin from bottom (Desktop)', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="twwr-whatsapp-chat-setting[general-desktop-bottom]" value="<?php echo ( isset($settings['general-desktop-bottom']) && !empty($settings['general-desktop-bottom']) ) ? $settings['general-desktop-bottom'] : 30; ?>" min="0" class="small-text" /> <code>px</code>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_position"><?php echo esc_html__('Margin from edge (Mobile)', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="twwr-whatsapp-chat-setting[general-mobile-edge]" value="<?php echo ( isset($settings['general-mobile-edge']) && !empty($settings['general-mobile-edge']) ) ? $settings['general-mobile-edge'] : 30; ?>" min="0" class="small-text" /> <code>px</code>
                        </td>
                    </tr>
                                    
                    <tr>
                        <th>
                            <label for="twwr_wa_position"><?php echo esc_html__('Margin from bottom (Mobile)', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="twwr-whatsapp-chat-setting[general-mobile-bottom]" value="<?php echo ( isset($settings['general-mobile-bottom']) && !empty($settings['general-mobile-bottom']) ) ? $settings['general-mobile-bottom'] : 30; ?>" min="0" class="small-text" /> <code>px</code>
                        </td>
                    </tr>

                    <?php if ( class_exists('WooCommerce') ) : ?>
                        <tr>
                            <th scope="row" colspan="2">
                                <h3><?php esc_html_e( 'WhatsApp Chat for WooCommerce Products', 'twwr' ); ?></h3>
                            </th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="twwr_whatsapp_information"><?php echo esc_html__('Default WhatsApp Chat for WooCommerce Products', 'twwr'); ?></label>
                            </th>
                            <td>
                                <select name="twwr-whatsapp-chat-setting[woocommerce-id]" class="twwr_whatsapp_display">
                                    <option value="none"><?php _e('Choose WhatsApp a chat...', 'twwr'); ?></option>
                                    <?php while( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
                                        <option value="<?php the_ID(); ?>" <?php echo ($wc_id == get_the_ID())? 'selected="selected"' : ''; ?>><?php the_title(); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <span class="description"><?php esc_html_e( 'Default WhatsApp chat on WooCommerce product page.', 'twwr' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="twwr-whatsapp-wc-position"><?php echo esc_html__('Chat Button Position', 'twwr'); ?></label>
                            </th>
                            <td>
                                <select name="twwr-whatsapp-chat-setting[wc-position]" class="twwr-whatsapp-wc-position">
                                    <option value="after" <?php echo (isset($settings['wc-position']) && $settings['wc-position'] == 'after')? 'selected="selected"' : ''; ?>><?php _e('After Add to cart button', 'twwr'); ?></option>
                                    <option value="before" <?php echo (isset($settings['wc-position']) && $settings['wc-position'] == 'before')? 'selected="selected"' : ''; ?>><?php _e('Before Add to cart button', 'twwr'); ?></option>
                                </select>
                                <span class="description"><?php esc_html_e( 'Set WhatsApp chat button on woocomerce pages.', 'twwr' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="twwr-whatsapp-wc-loop"><?php echo esc_html__('Display Chat Button on Product Loop', 'twwr'); ?></label>
                            </th>
                            <td>
                                <input id="twwr-whatsapp-wc-loop" type="checkbox" name="twwr-whatsapp-chat-setting[wc-loop]" value="1" <?php echo (isset($settings['wc-loop']) && $settings['wc-loop'] == '1')? 'checked' : ''; ?>> <?php esc_html_e('Yes', 'twwr'); ?>
                                <span class="description"><?php esc_html_e( 'Set the hour to send daily email report containing chat performance.', 'twwr' ); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'WhatsApp Chat Performance Report', 'twwr' ); ?></h3>
                            <span class="description"><?php echo esc_html__('System will automatically send daily email report. ', 'twwr'); ?></span>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_information"><?php echo esc_html__('Time to Send Email Report', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[daily-cron]" value="<?php esc_attr_e( $cron ); ?>" class="twwr-whatsapp-chat-timepicker">
                            <span class="description"><?php esc_html_e( 'Set the hour to send daily email report containing chat performance.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'Chat Slug', 'twwr' ); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_information"><?php echo esc_html__('WhatsApp Chat Slug', 'twwr'); ?></label>
                        </th>
                        <td>
                            <code><?php echo get_home_url(); ?>/</code><input type="text" name="twwr-whatsapp-chat-setting[slug]" class="small-text code slug" value="<?php esc_attr_e( $slug ); ?>" required/> <code>/chat-name/</code>
                            <span class="description"><?php esc_html_e( 'Url slug in WhatsApp chat permalink.', 'twwr' ); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table twwr-settings-appearance" role="presentation" style=<?php esc_attr_e( $active_tab == 'appearance' ? '' : 'display:none' ); ?>>
                <tbody>
                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'Redirect Page', 'twwr' ); ?></h3>
                        </th>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Page Background Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[page-bg]" value="<?php echo ( isset($settings['page-bg']) && !empty($settings['page-bg']) ) ? $settings['page-bg'] : '#f1f1f1'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_loading_text"><?php echo esc_html__('Loading Text', 'twwr'); ?></label>
                        </th>
                        <td>
                            <textarea name="twwr-whatsapp-chat-setting[loading-text]" placeholder="<?php _e('Please wait, you will immediately connected to one of our agents.', 'twwr'); ?>" rows="5" style="width:50%;"><?php echo ( isset($settings['loading-text']) ) ? $settings['loading-text'] : ''; ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Heading Text', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[heading-text]" value="<?php echo ( isset($settings['heading-text']) ) ? $settings['heading-text'] : 'Choose an Agent'; ?>" min="0" class="regular-text" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Heading Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[heading-color]" value="<?php echo ( isset($settings['heading-color']) && !empty($settings['heading-color']) ) ? $settings['heading-color'] : '#333'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Sub Heading Text', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[sub-heading-text]" value="<?php echo ( isset($settings['sub-heading-text']) ) ? $settings['sub-heading-text'] : 'Choose one of of the agent to initiate chat in WhatsApp.'; ?>" min="0" class="regular-text" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Sub Heading Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[sub-heading-color]" value="<?php echo ( isset($settings['sub-heading-color']) && !empty($settings['sub-heading-color']) ) ? $settings['sub-heading-color'] : '#7e7e7e'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Chat Button Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[chat-button-color]" value="<?php echo ( isset($settings['chat-button-color']) && !empty($settings['chat-button-color']) ) ? $settings['chat-button-color'] : '#03cc0b'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Chat Button Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[chat-button-text-color]" value="<?php echo ( isset($settings['chat-button-text-color']) && !empty($settings['chat-button-text-color']) ) ? $settings['chat-button-text-color'] : '#fff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'Floating Button', 'twwr' ); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'Floating Chat Widget', 'twwr' ); ?></h3>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_label"><?php echo esc_html__('Button Type', 'twwr'); ?></label>
                        </th>
                        <td>
                            <select name="twwr-whatsapp-chat-setting[twwr_whatsapp_display_type]" class="twwr-whatsapp-button-type">
                                <option value="icon-text" <?php echo (isset($settings['twwr_whatsapp_display_type']) && $settings['twwr_whatsapp_display_type'] == 'icon-text') ? 'selected' : ''; ?>><?php echo esc_html_e( 'Icon and text', 'twwr' ); ?></option>
                                <option value="icon" <?php echo (isset($settings['twwr_whatsapp_display_type']) && $settings['twwr_whatsapp_display_type'] == 'icon') ? 'selected' : ''; ?>><?php echo esc_html_e( 'Icon', 'twwr' ); ?></option>
                            </select>
                            <span class="description"><?php esc_html_e( 'Choose button type.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_floating_bg"><?php echo esc_html__('Floating Background Header Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[floating-background-header-color]" value="<?php echo ( isset($settings['floating-background-header-color']) && !empty($settings['floating-background-header-color']) ) ? $settings['floating-background-header-color'] : '#03cc0b'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_floating_color"><?php echo esc_html__('Floating Text Header Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[floating-text-header-color]" value="<?php echo ( isset($settings['floating-text-header-color']) && !empty($settings['floating-text-header-color']) ) ? $settings['floating-text-header-color'] : '#ffffff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_floating_button_bg"><?php echo esc_html__('Floating Button Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[floating-button-color]" value="<?php echo ( isset($settings['floating-button-color']) && !empty($settings['floating-button-color']) ) ? $settings['floating-button-color'] : '#03cc0b'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_floating_button_color"><?php echo esc_html__('Floating Button Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[floating-button-text-color]" value="<?php echo ( isset($settings['floating-button-text-color']) && !empty($settings['floating-button-text-color']) ) ? $settings['floating-button-text-color'] : '#ffffff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Agent Box Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-color]" value="<?php echo ( isset($settings['agent-box-color']) && !empty($settings['agent-box-color']) ) ? $settings['agent-box-color'] : '#ffffff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_page_bg"><?php echo esc_html__('Agent Box shadow', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-shadow]" value="<?php echo ( isset($settings['agent-box-shadow']) && !empty($settings['agent-box-shadow']) ) ? $settings['agent-box-shadow'] : '#f1f1f1'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_wa_agent_box_text_color"><?php echo esc_html__('Agent Box Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-text-color]" value="<?php echo ( isset($settings['agent-box-text-color']) && !empty($settings['agent-box-text-color']) ) ? $settings['agent-box-text-color'] : '#000000'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_agent_box_name_color"><?php echo esc_html__('Agent Box Name Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-name-color]" value="<?php echo ( isset($settings['agent-box-name-color']) && !empty($settings['agent-box-name-color']) ) ? $settings['agent-box-name-color'] : '#000000'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_agent_box_online_color"><?php echo esc_html__('Agent Box Online Status Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-online-color]" value="<?php echo ( isset($settings['agent-box-online-color']) && !empty($settings['agent-box-online-color']) ) ? $settings['agent-box-online-color'] : '#03cc0b'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_wa_agent_box_offline_color"><?php echo esc_html__('Agent Box Offline Status Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[agent-box-offline-color]" value="<?php echo ( isset($settings['agent-box-offline-color']) && !empty($settings['agent-box-offline-color']) ) ? $settings['agent-box-offline-color'] : '#bababa'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e( 'Standard Button', 'twwr' ); ?></h3>
                        </th>
                    </tr>
                    
                    <tr>
                        <th>
                            <label for="twwr_std_bg_color"><?php echo esc_html__('Background Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[std-bg-color]" value="<?php echo ( isset($settings['std-bg-color']) && !empty($settings['std-bg-color']) ) ? $settings['std-bg-color'] : '#03cc0b'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_std_text_color"><?php echo esc_html__('Text Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[std-text-color]" value="<?php echo ( isset($settings['std-text-color']) && !empty($settings['std-text-color']) ) ? $settings['std-text-color'] : '#fff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_std_agent_color"><?php echo esc_html__('Agent Name Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[std-agent-name-color]" value="<?php echo ( isset($settings['std-agent-name-color']) && !empty($settings['std-agent-name-color']) ) ? $settings['std-agent-name-color'] : '#fff'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_std_online_color"><?php echo esc_html__('Online Status Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[std-button-online-color]" value="<?php echo ( isset($settings['std-button-online-color']) && !empty($settings['std-button-online-color']) ) ? $settings['std-button-online-color'] : '#ffef9f'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_std_offline_color"><?php echo esc_html__('Offline Status Color', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-chat-setting[std-button-offline-color]" value="<?php echo ( isset($settings['std-button-offline-color']) && !empty($settings['std-button-offline-color']) ) ? $settings['std-button-offline-color'] : '#bababa'; ?>" min="0" class="color-field" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>
        <?php
    }

    function twwr_whatsapp_admin_notices() {

        if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
            switch( $_GET['sl_activation'] ) {
                case 'false':
                    $message = urldecode( $_GET['message'] );
                    ?>
                    <div class="error">
                        <p><?php esc_html_e ( $message ); ?></p>
                    </div>
                    <?php
                    break;
                case 'true':
                default:
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }
}

if ( ! function_exists( 'twwr_check_tablet_desktop' ) ) {
    function twwr_check_tablet_desktop() {
        if( !class_exists( 'Mobile_Detect' ) ) {
            require_once( dirname( __FILE__ ) . '/library/Mobile_Detect.php' );
        }

        $detect = new Mobile_Detect;

        if( !$detect->isMobile() || $detect->isTablet() ){
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'twwr_whatsapp_chat_generate_link' ) ) {
    function twwr_whatsapp_chat_generate_link($chat_id, $number, $agent_id, $prod_id) {
        if( !empty($chat_id) && !empty($number) && !empty($agent_id) ) {
            $agent = get_post($_GET['agent']);
            if ( preg_match('[^\+62]', $number ) ) {
                $twwr_whatsapp_number = str_replace('+62', '62', $number);
            } else if ( $number[0] == '0' ) {
                $twwr_whatsapp_number = ltrim( $number, '0' );
                $twwr_whatsapp_number = '62'. $twwr_whatsapp_number;
            } else if ( $number[0] == '8' ) {
                $twwr_whatsapp_number = '62'. $number;
            } else {
                $twwr_whatsapp_number = $number;
            }

            if ( twwr_check_tablet_desktop() ) {
                $wa_base_url = 'https://web.whatsapp.com/';
            } else {
                $wa_base_url = 'https://api.whatsapp.com/';
            }

            $prod = ( function_exists('wc_get_product') ) ? wc_get_product($prod_id) : false;
            if( $prod ){
                $product_name = $prod->get_name();
                $product_price = strip_tags(wc_price($prod->get_price()));
                $product_url = get_permalink($prod_id);
            }
            else{
                $product_name = '';
                $product_price = '';
                $product_url = '';
            }
            $ref = !empty( $prod_id ) ? get_the_permalink($prod_id) : home_url();

            $msg = get_post_meta($chat_id, '_twwr_whatsapp_message', true);
            $msg = str_replace( "%twwr_chat_page_title%", get_the_title($prod_id), $msg);
            $msg = str_replace( "%twwr_chat_page_url%", $ref, $msg);
            $msg = str_replace( "%twwr_chat_store_name%", get_bloginfo('name'), $msg);
            $msg = str_replace( "%twwr_chat_agent_name%", $agent->post_title, $msg);
            $msg = str_replace( "%twwr_chat_product_name%", $product_name, $msg);
            $msg = str_replace( "%twwr_chat_product_price%", $product_price, $msg);
            $msg = str_replace( "%twwr_chat_product_url%", $product_url, $msg);
            $msg = str_replace( "&nbsp;", '', $msg);
            $msg = urlencode($msg);
            $msg = str_replace( PHP_EOL, '%0A', $msg);
            $msg = str_replace( "\n", '%0A', $msg);
            $msg = str_replace( "#", '%23', $msg);
            $msg = str_replace( "-", '%2D', $msg);
            $msg = str_replace( "&",'%26', $msg);
            
            // echo $wa_base_url.'send?l=id&phone='.$twwr_whatsapp_number.'&text='.$msg; exit();
            return $wa_base_url.'send?l=id&phone='.$twwr_whatsapp_number.'&text='.$msg;
        }
        else{
            return false;
        }
    }
}

if ( ! function_exists( 'twwr_whatsapp_chat_listing_agent' ) ) {
    function twwr_whatsapp_chat_listing_agent() {
        global $post, $wp;
        $twChat = new TWWR_Whatsapp_Chat();

        if( is_single() && $post->post_type != 'twwr_whatsapp_chat' )
            return false;

        $twwr_whatsapp_id = get_the_ID();

        if ( $twwr_whatsapp_id ) {
            $label = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_label', true);
            $header = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header', true);
            $header_desc = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_header_description', true);
            $dis = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_display', true);
            $buttons = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_button', true);
            $information = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_information', true);
            $msg = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_message', true);
            $fb_pix = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_pixel_events', true);
            $buttons = $twChat->twwr_whatsapp_check_available( $buttons, $dis, $twwr_whatsapp_id );
            ?>
            <div class="twwr-container <?php esc_attr_e( $twwr_whatsapp_style ) ?>" data-rot-id="<?php esc_attr_e( $twwr_whatsapp_id ); ?>">
                <?php if ($buttons ) : ?>

                    <?php
                    foreach ( $buttons as $button ) {
                        $twChat->twwr_whatsapp_render_button($twwr_whatsapp_id, $button, $twwr_whatsapp_style);
                    }
                    ?>
                    
                    <?php if( $information ) : ?>
                        <p class="contact-message"><?php esc_html_e ( $information ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php }
    }
}

if ( ! function_exists( 'twwr_whatsapp_chat_random_agent' ) ) {
    function twwr_whatsapp_chat_random_agent() {
        global $post, $wp;
        $twChat = new TWWR_Whatsapp_Chat();

        if( is_single() && $post->post_type != 'twwr_whatsapp_chat' )
            return false;

        $twwr_whatsapp_id = get_the_ID();

        if ( $twwr_whatsapp_id ) {
            $buttons = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_button', true);
            $dis = get_post_meta($twwr_whatsapp_id, '_twwr_whatsapp_display', true);

            $button = $twChat->twwr_whatsapp_check_available( $buttons, $dis, $twwr_whatsapp_id );

            return $button;
        }
    }
}

if ( ! wp_next_scheduled( 'twwr_whatsapp_chat_report_cron' ) ) {
    $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : '' ;
    $time = (isset($settings['daily-cron']) && !empty($settings['daily-cron']) ) ? $settings['daily-cron'] : '10:00';
    $time = date("H:i", strtotime($time));
    $to_utc = get_option('gmt_offset') * -1;
    $time = strtotime($time.$to_utc.' hours +1 days');

    wp_schedule_event( $time, 'daily', 'twwr_whatsapp_chat_report_cron' );
}

$TWWR_Whatsapp_Chat = new TWWR_Whatsapp_Chat();
