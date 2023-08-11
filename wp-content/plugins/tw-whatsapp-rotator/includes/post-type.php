<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'TWWR_Database' ) ) {
	// load chat DB
	include( dirname( __FILE__ ) . '/twwr_database.php' );
}

class TWWR_Post_Type {
    public function __construct(){
        $this->pluginUrl = WP_PLUGIN_URL . '/twwr-whatsapp-chat';
        $this->db = new TWWR_Database;

        add_action( 'init', array($this, 'twwr_whatsapp_post_type') );
        add_action( 'save_post', array($this, 'twwr_whatsapp_post_save_meta_box'));
        add_action( 'add_meta_boxes', array($this, 'twwr_whatsapp_post_meta_box') );
        add_action( 'manage_twwr_whatsapp_chat_posts_custom_column', array($this, 'twwr_whatsapp_post_content_columns'), 10, 2);
        add_filter( 'manage_twwr_whatsapp_chat_posts_columns', array($this, 'twwr_whatsapp_post_header_columns'), 10);
        add_filter( 'register_post_type_args', array($this, 'twwr_whatsapp_post_rewrite_setting'), 10, 2 );
        add_action( 'load-edit.php', function() {
            add_filter( 'views_edit-twwr_whatsapp_chat', array($this, 'twwr_whatsapp_chat_list_table_after') ); // talk is my custom post type
        });

        register_activation_hook( 'twwr-whatsapp-rotator/TWWR.php', array($this, 'twwr_whatsapp_chat_flush_rewrite') );
    }

    function twwr_whatsapp_chat_list_table_after($views) {
        ?>
        <div class="message">
            <p><?php echo sprintf( esc_html__('In WhatsApp Chat list page, click is calculated when %s page is loaded for the first time, before it is being redirected to a WhatsApp number. While in Agents list page, click is calculated when %s page has been clicked and the redirect process to WhatsApp number has begun.', 'twwr'), '<code>domainname.com/wa/chat-name</code>', '<code>domainname.com/wa/chat-name</code>' ); ?></p>
        </div>
        <?php
        return $views;
    }

    function twwr_whatsapp_chat_flush_rewrite(){
        flush_rewrite_rules();
    }

    function twwr_whatsapp_post_rewrite_setting( $args, $post_type ) {
        $settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : '' ;
        $slug = (isset($settings['slug']) && !empty($settings['slug']) ) ? $settings['slug'] : 'wa';
        
        if ( 'twwr_whatsapp_chat' === $post_type && $slug != $args['rewrite']['slug'] ) {
            $args['rewrite']['slug'] = $slug;
            $this->twwr_whatsapp_chat_flush_rewrite();
        }
        return $args;
    }

    function twwr_whatsapp_post_type() {
        $labels = array(
            'name'                  => esc_html__( 'WhatsApp Chat', 'twwr' ),
            'singular_name'         => esc_html__( 'WhatsApp Chat', 'twwr' ),
            'add_new'               => esc_html__( 'Add WhatsApp Chat', 'twwr' ),
            'add_new_item'          => esc_html__( 'Add WhatsApp Chat', 'twwr' ),
            'edit_item'             => esc_html__( 'Edit WhatsApp Chat', 'twwr' ),
            'new_item'              => esc_html__( 'New WhatsApp Chat', 'twwr' ),
            'view_item'             => esc_html__( 'View WhatsApp Chat', 'twwr' ),
            'search_items'          => esc_html__( 'Search WhatsApp Chat', 'twwr' ),
            'not_found'             => esc_html( 'No chat found. Make sure to have at least 1 agent before adding a new WhatsApp chat.', 'twwr' ),
            'not_found_in_trash'    => esc_html__( 'Nothing found in Trash', 'twwr' ),
            'parent_item_colon'     => ''
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'wa', 'with_front' => true, 'feed' => false ),
            'menu_icon'             => 'dashicons-phone',
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_position'         => 30,
            'supports'              => array('title', 'author')
        );
        register_post_type( 'twwr_whatsapp_chat', $args );
    }

    function twwr_whatsapp_post_save_meta_box( $post_id ) {
        if( get_post_type() == 'twwr_whatsapp_chat' ){
            if (array_key_exists('twwr_whatsapp_position', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_chat_position',
                    $_POST['twwr_whatsapp_position']
                );
            }

            if (array_key_exists('twwr_whatsapp_position_bottom_desktop', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_chat_position_bottom_desktop',
                    $_POST['twwr_whatsapp_position_bottom_desktop']
                );
            }

            if (array_key_exists('twwr_whatsapp_position_edge_desktop', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_chat_position_edge_desktop',
                    $_POST['twwr_whatsapp_position_edge_desktop']
                );
            }

            if (array_key_exists('twwr_whatsapp_position_bottom_mobile', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_chat_position_bottom_mobile',
                    $_POST['twwr_whatsapp_position_bottom_mobile']
                );
            }

            if (array_key_exists('twwr_whatsapp_position_edge_mobile', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_chat_position_edge_mobile',
                    $_POST['twwr_whatsapp_position_edge_mobile']
                );
            }

            if (array_key_exists('twwr_whatsapp_display', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_display',
                    $_POST['twwr_whatsapp_display']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_fb_id', $_POST ) ) {
                $arr_fb_id = $_POST['twwr_whatsapp_fb_id'];
                array_filter($arr_fb_id);
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_fb_id',
                    $arr_fb_id
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_button_agent', $_POST ) ) {
                $arr_button = array();
                foreach ( $_POST['twwr_whatsapp_button_agent'] as $i => $type ) {
                    if ( !empty( $_POST['twwr_whatsapp_button_agent'][$i] ) ) {
                        $arr_button[] = array(
                            'agent'  => $_POST['twwr_whatsapp_button_agent'][$i],
                            'number'  => $_POST['twwr_whatsapp_button_number'][$i],
                            'label'  => $_POST['twwr_whatsapp_button_label'][$i],
                            'priority'  => $_POST['twwr_whatsapp_button_priority'][$i],
                        );
                    }
                }

                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_button',
                    $arr_button
                );
                delete_option('twwa_random_priority_'.$post_id.'_'.count($_POST['twwr_whatsapp_button_agent']));
            }

            if ( array_key_exists( 'twwr_whatsapp_information', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_information',
                    $_POST['twwr_whatsapp_information']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_label', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_label',
                    $_POST['twwr_whatsapp_label']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_header', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_header',
                    $_POST['twwr_whatsapp_header']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_header_description', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_header_description',
                    $_POST['twwr_whatsapp_header_description']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_message', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_message',
                    $_POST['twwr_whatsapp_message']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_pixel_events', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_pixel_events',
                    $_POST['twwr_whatsapp_pixel_events']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_pixel_events_custom', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_pixel_events_custom',
                    $_POST['twwr_whatsapp_pixel_events_custom']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_gtm_id', $_POST ) ) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_gtm_id',
                    $_POST['twwr_whatsapp_gtm_id']
                );
            }
        }
    }

    function twwr_whatsapp_post_meta_box() {
        add_meta_box(
            'twwr_whatsapp_meta_box', // Unique ID
            esc_html__('Contact Button Settings', 'twwr'), // Box title
            array($this, 'twwr_whatsapp_post_meta_box_html'), // Content callback, must be of type callable
            'twwr_whatsapp_chat', // Post type
            'normal',
            'high'
        );
    }

    function twwr_whatsapp_post_meta_box_html( $post ) {
        $dis = get_post_meta( $post->ID, '_twwr_whatsapp_display', true );
        $label = get_post_meta( $post->ID, '_twwr_whatsapp_label', true );
        $heading = get_post_meta( $post->ID, '_twwr_whatsapp_header', true );
        $heading_desc = get_post_meta( $post->ID, '_twwr_whatsapp_header_description', true );
        $buttons = get_post_meta( $post->ID, '_twwr_whatsapp_button', true );
        $msg = get_post_meta( $post->ID, '_twwr_whatsapp_message', true );
        $information = get_post_meta( $post->ID, '_twwr_whatsapp_information', true );
        $fb_pix_ids = get_post_meta( $post->ID, '_twwr_whatsapp_fb_id', true );
        $fb_pix = get_post_meta( $post->ID, '_twwr_whatsapp_pixel_events', true );
        $fb_pix_custom = get_post_meta( $post->ID, '_twwr_whatsapp_pixel_events_custom', true );
        $gtm_id = get_post_meta( $post->ID, '_twwr_whatsapp_gtm_id', true );
        $style = get_post_meta($post->ID, '_twwr_whatsapp_chat_style', true);
        $position = get_post_meta($post->ID, '_twwr_whatsapp_chat_position', true);
        $position_edge_desktop = get_post_meta($post->ID, '_twwr_whatsapp_chat_position_edge_desktop', true);
        $position_bottom_desktop = get_post_meta($post->ID, '_twwr_whatsapp_chat_position_bottom_desktop', true);
        $position_edge_mobile = get_post_meta($post->ID, '_twwr_whatsapp_chat_position_edge_mobile', true);
        $position_bottom_mobile = get_post_meta($post->ID, '_twwr_whatsapp_chat_position_bottom_mobile', true);

        $label = ( empty($label) && $post->post_status == 'auto-draft' ) ? __('Reach us on WhatsApp', 'twwr') : $label;
        $heading = ( empty($heading) && $post->post_status == 'auto-draft' ) ? __('Hi!', 'twwr') : $heading;
        $heading_desc = ( empty($heading_desc) && $post->post_status == 'auto-draft' ) ? __('Chat with one of our agent.', 'twwr') : $heading_desc;
        $information = ( empty($information) && $post->post_status == 'auto-draft' ) ? __('Use this feature to chat with our agent.', 'twwr') : $information;
        $msg = ( empty($msg) && $post->post_status == 'auto-draft' ) ? __('Hi %twwr_chat_agent_name%, can you help me?', 'twwr') : $msg;
        
        $all_agent = get_posts(array(
            'post_type'     => 'twwr_whatsapp_agent',
            'post_status'   => 'publish',
            'posts_per_page'=> -1,
        ));
        ?>

        <div class="twwr-whatsapp-admin-post-form">
            <table class="form-table">
                <tbody>
                    <tr class="twwr-whatsapp-button-text">
                        <th scope="row">
                            <label for="twwr_whatsapp_label"><?php echo esc_html__('Button Label', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr_whatsapp_label" class="regular-text" value="<?php echo ($label)? $label : '' ?>"/>
                            <span class="description"><?php esc_html_e( 'Text label for the button', 'twwr' ); ?></span>
                        </td>
                    </tr>
                    <tr class="twwr-whatsapp-button-text">
                        <th scope="row">
                            <label for="twwr_whatsapp_header"><?php echo esc_html__('Floating Rotator Heading Text', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr_whatsapp_header" class="regular-text" value="<?php echo ($heading)? $heading : ''; ?>" />
                            <span class="description"><?php esc_html_e( 'Heading text on floating WhatsApp chat.', 'twwr' ); ?></span>
                        </td>
                    </tr>
                    <tr class="twwr-whatsapp-button-text">
                        <th scope="row">
                            <label for="twwr_whatsapp_header_description"><?php echo esc_html__('Floating Rotator  Description', 'twwr'); ?></label>
                        </th>
                        <td>
                            <textarea type="text" name="twwr_whatsapp_header_description" class="regular-text" cols="55" rows="7"><?php echo ($heading_desc)? $heading_desc : ''; ?></textarea>
                            <span class="description"><?php esc_html_e( 'Description text for heading when is used in Floating mode.', 'twwr' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_information"><?php esc_html_e( 'Rotator Description', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <textarea class="large-text" name="twwr_whatsapp_information" class="twwr_whatsapp_information" rows="5"><?php echo ($information)? $information : ''; ?></textarea>
                            <span class="description"><?php esc_html_e( 'Text on button when is used in Standard button mode.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_display"><?php echo esc_html__( 'Display Mode', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <select name="twwr_whatsapp_display" class="twwr_whatsapp_display">
                                <option value="all" <?php echo ( $dis == 'all' )? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Show All Agents', 'twwr' ); ?></option>
                                <option value="random" <?php echo ( $dis == 'random')? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Show Random Agents', 'twwr' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="button_contact"><?php echo esc_html__( 'Choose Agent & WhatsApp Number', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <ul class="parent_button parent-button-chat">
                                <?php if ( $buttons ) : $key = 0;?>
                                    <?php foreach ( $buttons as $button ) : ?>
                                        <li class="button-contact button-contact-chat" data-seq="<?php esc_attr_e( $key ); ?>">
                                            <span class="dashicons dashicons-move" title="<?php esc_html_e('Drag to adjust position', 'twwr'); ?>"></span>

                                            <div class="agent-column">
                                                <span class="heading"><?php esc_html_e('Choose Agent', 'twwr'); ?></span>
                                                <select name="twwr_whatsapp_button_agent[<?php esc_attr_e( $key ); ?>]" class="regular-text button_type" data-selected="<?php esc_attr_e( $button['number'] ); ?>">
                                                    <?php if( is_array($all_agent) && count($all_agent) > 0 ) :
                                                        foreach( $all_agent as $agent ) :
                                                        $numb = get_post_meta( $agent->ID, '_twwr_whatsapp_agent_wa_number', true );

                                                        $numb = json_encode(array_values($numb));
                                                        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
                                                        $numb = strtr(rawurlencode($numb), $revert);
                                                        ?>
                                                        <option value="<?php esc_attr_e( $agent->ID ); ?>" <?php esc_attr_e( ( isset($button['agent']) && $button['agent'] == $agent->ID )? 'selected' : '' ); ?> data-number=<?php esc_attr_e( $numb ); ?>><?php esc_html_e( $agent->post_title ); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <option value="" disabled><?php _e('Add Agent first', 'twwr'); ?></option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>

                                            <div class="agent-column">
                                                <span class="heading"><?php esc_html_e('WhatsApp Number', 'twwr'); ?></span>
                                                <select name="twwr_whatsapp_button_number[<?php esc_attr_e( $key ); ?>]" class="regular-text button_number">
                                                    <option value="" disabled><?php _e('Select an agent', 'twwr'); ?></option>
                                                </select>

                                                <input type="hidden" name="twwr_whatsapp_button_label[<?php esc_attr_e( $key ); ?>]" class="button_label" value="<?php echo ( isset($button['label']) ? $button['label'] : ''); ?>">
                                            </div>

                                            <div class="agent-column">
                                                <span class="heading"><?php esc_html_e('View Percentage', 'twwr'); ?></span>
                                                <input id="numIpt" type="number" name="twwr_whatsapp_button_priority[<?php esc_attr_e( $key ); ?>]" value="<?php esc_attr_e( $button['priority'] ); ?>" class="input-views button_priority" size="3" maxlength="3" min="1" max="100" /> %
                                            </div>

                                            <button class="button_plus button-plus-chat">+</button>
                                            <button class="button_min">-</button>
                                        </li>
                                    <?php $key++; endforeach; ?>
                                <?php else: ?>
                                    <li class="button-contact button-contact-chat" data-seq="<?php esc_attr_e( $key ); ?>">
                                        <span class="dashicons dashicons-move" title="<?php esc_html_e('Drag to reorder', 'twwr'); ?>"></span>

                                        <div class="agent-column">
                                            <span class="heading"><?php esc_html_e('Select an agent', 'twwr'); ?></span>
                                            <select name="twwr_whatsapp_button_agent[<?php esc_attr_e( $key ); ?>]" class="regular-text button_type">
                                                <?php if( is_array($all_agent) && count($all_agent) > 0 ) : ?>
                                                    <option value="" disabled selected><?php esc_html_e('Select an agent', 'twwr'); ?></option>
                                                    <?php foreach( $all_agent as $agent ) :
                                                    $numb = get_post_meta( $agent->ID, '_twwr_whatsapp_agent_wa_number', true );

                                                    $numb = json_encode(array_values($numb));
                                                    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
                                                    $numb = strtr(rawurlencode($numb), $revert);
                                                    ?>
                                                    <option value="<?php esc_attr_e( $agent->ID ); ?>" data-number=<?php esc_attr_e( $numb ); ?>><?php esc_html_e( $agent->post_title ); ?></option>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                    <option value="" disabled><?php esc_html_e('Please add an agent first.', 'twwr'); ?></option>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <div class="agent-column">
                                            <span class="heading"><?php esc_html_e('WhatsApp Number', 'twwr'); ?></span>
                                            <select name="twwr_whatsapp_button_number[<?php esc_attr_e( $key ); ?>]" class="regular-text button_number">
                                                <option value="" disabled selected><?php _e('Please add an agent first.', 'twwr'); ?></option>
                                            </select>
                                            <input type="hidden" name="twwr_whatsapp_button_label[<?php esc_attr_e( $key ); ?>]" class="button_label">
                                        </div>

                                        <div class="agent-column">
                                            <span class="heading"><?php esc_html_e('Views Percentage', 'twwr'); ?></span>
                                            <input type="number" name="twwr_whatsapp_button_priority[<?php esc_attr_e( $key ); ?>]" class="input-views button_priority" size="3" maxlength="3" min="1" max="100" /> %
                                        </div>
                                        <button class="button_plus button-plus-chat">+</button>
                                        <button class="button_min" style="display:none">-</button>
                                    </li>
                                <?php endif;?>
                            </ul>
                            <p><span class="description"><?php echo esc_html__( 'Add an agent for this WhatsApp chat.', 'twwr' ); ?></span></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                           <label for="twwr_whatsapp_message"><?php echo esc_html__( 'Text Message', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <textarea name="twwr_whatsapp_message" class="large-text twwr_whatsapp_message" rows="5"><?php echo ( $msg )? $msg : ''; ?></textarea>
                            <span class="description"><?php esc_html_e( 'Default greeting message.', 'twwr' ); ?></span>

                            <h4><?php esc_html_e( 'Shortcode', 'twwr' ); ?></h4>

                            <ul class="twwr-chat-shortcodes">
                                <li><code>%twwr_chat_agent_name%</code> <?php esc_html_e( 'Display agent name', 'twwr' ); ?></li>
                                <li><code>%twwr_chat_store_name%</code> <?php esc_html_e( 'Display site name', 'twwr' ); ?></li>
                                <li><code>%twwr_chat_page_url%</code> <?php esc_html_e( 'Display page url', 'twwr' ); ?></li>
                                <li><code>%twwr_chat_page_title%</code> <?php esc_html_e( 'Display page title', 'twwr' ); ?></li>
                            </ul>

                            <?php if ( class_exists('WooCommerce') ) : ?>
                                <h4><?php esc_html_e( 'Shortcode for WooCommerce', 'twwr' ); ?></h4>

                                <ul class="twwr-chat-shortcodes">
                                    <li><code>%twwr_chat_product_name%</code> <?php esc_html_e( 'Display product name', 'twwr' ); ?></li>
                                    <li><code>%twwr_chat_product_price%</code> <?php esc_html_e( 'Display product price', 'twwr' ); ?></li>
                                    <li><code>%twwr_chat_product_url%</code> <?php esc_html_e( 'Display product url', 'twwr' ); ?></li>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="row">
                        <td colspan="2"><h3><?php esc_html_e( 'Facebook Pixel', 'twwr' ); ?></h3></td>
                    </tr>

                    <tr>
                        <th>
                            <label for="twwr_whatsapp_fb_id"><?php echo esc_html__( 'Facebook Pixel ID', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <div class="fb_id_parent">
                                <?php if ( $fb_pix_ids ) : ?>
                                    <?php foreach ( $fb_pix_ids as $id ) : ?>
                                        <?php if ( $id ) : ?>
                                            <div class="fb_pixel_id">
                                                <input type="text" name="twwr_whatsapp_fb_id[]" placeholder="<?php echo esc_html__(' Facebook Pixel ID', 'twwr' ); ?>" class="twwr_whatsapp_fb_id" value="<?php echo ( $id ) ? $id : ''; ?>">
                                                <button class="fb_id_button_plus">+</button>
                                                <button class="fb_id_button_min">-</button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif;?>

                                <div class="fb_pixel_id">
                                    <input type="text" name="twwr_whatsapp_fb_id[]" placeholder="<?php echo esc_html__( 'Facebook Pixel ID', 'twwr' ); ?>" class="twwr_whatsapp_fb_id">
                                    <button class="fb_id_button_plus">+</button>
                                    <button class="fb_id_button_min">-</button>
                                </div>
                            </div>

                            <span class="description"><?php esc_html_e( 'Type your Facebook Pixel ID, you can add more than one Pixel ID (opsional).', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_pixel_events"><?php echo esc_html__('Facebook Pixel Event', 'twwr'); ?></label>
                        </th>
                        <td>
                            <select name="twwr_whatsapp_pixel_events" class="twwr_whatsapp_pixel_events">
                                <option value="ViewContent" <?php echo ($fb_pix == 'ViewContent')? 'selected="selected"' : ''; ?>>ViewContent</option>
                                <option value="Lead" <?php echo ($fb_pix == 'Lead')? 'selected="selected"' : ''; ?>>Lead</option>
                                <option value="AddToWishlist" <?php echo ($fb_pix == 'AddToWishlist')? 'selected="selected"' : ''; ?>>AddToWishlist</option>
                                <option value="AddPaymentInfo" <?php echo ($fb_pix == 'AddPaymentInfo')? 'selected="selected"' : ''; ?>>AddPaymentInfo</option>
                                <option value="CompleteRegistration" <?php echo ($fb_pix == 'CompleteRegistration')? 'selected="selected"' : ''; ?>>CompleteRegistration</option>
                                <option value="AddToCart" <?php echo ($fb_pix == 'AddToCart')? 'selected="selected"' : ''; ?>>AddToCart</option>
                                <option value="InitiateCheckout" <?php echo ($fb_pix == 'InitiateCheckout')? 'selected="selected"' : ''; ?>>InitiateCheckout</option>
                                <option value="Purchase" <?php echo ($fb_pix == 'Purchase')? 'selected="selected"' : ''; ?>>Purchase</option>
                                <option value="Custom" <?php echo ($fb_pix == 'Custom')? 'selected="selected"' : ''; ?>><?php esc_html_e('Custom Event', 'twwr'); ?></option>
                            </select>

                            <input type="text" name="twwr_whatsapp_pixel_events_custom" placeholder="<?php echo esc_html__('Custom Event', 'twwr'); ?>" class="twwr_whatsapp_custom_event" <?php echo ($fb_pix != 'Custom')? 'disabled="disabled"' : ''; ?> value=<?php echo ($fb_pix_custom)? $fb_pix_custom : ''; ?>>
                        </td>
                    </tr>

                    <tr class="row">
                        <td colspan="2"><h3><?php esc_html_e( 'Google Tag Manager', 'twwr' ); ?></h3></td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr_whatsapp_pixel_events"><?php echo esc_html__('Google Tag Manager ID', 'twwr'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr_whatsapp_gtm_id" placeholder="<?php echo esc_html__('Example: GTM-XXXXXXX', 'twwr'); ?>" class="regular-text twwr_whatsapp_gtm_id" value=<?php echo ($gtm_id) ? $gtm_id : ''; ?>>
                            <span class="description"><?php esc_html_e( 'Your Google Tag Manager ID, for example: GTM-XXXXXXX (opsional).', 'twwr' ); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
		<script type="text/javascript">
			function ForNumbers(evt){
			var charCode = (evt.which) ? evt.which : event.keyCode;

			if (
				charCode >= 48 && charCode <= 57 ||
			   charCode >= 96 && charCode <= 105 ||
			   charCode == 8 ||
				charCode == 9 ||
				charCode == 13 ||
				charCode >= 35 && charCode <= 46
			)
			{
				if(parseInt(this.value+String.fromCharCode(charCode), 10) <= 100)
					return true;
			}

			evt.preventDefault();
			evt.stopPropagation();

			return false;
			}
            var inputNumber = document.getElementsByClassName("input-views");
            for(i=0;i<inputNumber.length;i++) {
               inputNumber[i].addEventListener('keypress', ForNumbers, false);
            }
		</script>
        <script id="button-chat-template" type="x-jstw-template">
            <li class="button-contact button-contact-chat" data-seq="{{row}}">
                <span class="dashicons dashicons-move" title="<?php esc_html_e('Drag to reorder', 'twwr'); ?>"></span>

                <div class="agent-column">
                    <span class="heading"><?php esc_html_e('Choose Agent', 'twwr'); ?></span>
                    <select name="twwr_whatsapp_button_agent[{{row}}]" class="regular-text button_type">
                        <?php if( is_array($all_agent) && count($all_agent) > 0 ) : ?>
                            <option value="" disabled selected><?php esc_html_e('Select an agent', 'twwr'); ?></option>
                            <?php foreach( $all_agent as $agent ) :
                            $numb = get_post_meta( $agent->ID, '_twwr_whatsapp_agent_wa_number', true );

                            $numb = json_encode(array_values($numb));
                            $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
                            $numb = strtr(rawurlencode($numb), $revert);
                            ?>
                            <option value="<?php esc_attr_e( $agent->ID ); ?>" data-number=<?php esc_attr_e( $numb ); ?>><?php esc_html_e( $agent->post_title ); ?></option>
                        <?php endforeach; ?>
                        <?php else : ?>
                            <option value="" disabled><?php esc_html_e('Please add a customer service first', 'twwr'); ?></option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="agent-column">
                    <span class="heading"><?php esc_html_e('WhatsApp Number', 'twwr'); ?></span>
                    <select name="twwr_whatsapp_button_number[{{row}}]" class="regular-text button_number">
                        <option value="" disabled selected><?php _e('Select a customer service first', 'twwr'); ?></option>
                    </select>
                    <input type="hidden" name="twwr_whatsapp_button_label[{{row}}]" class="button_label">
                </div>


                <div class="agent-column">
                    <span class="heading"><?php esc_html_e('Views Percentage', 'twwr'); ?></span>
                    <input type="number" name="twwr_whatsapp_button_priority[{{row}}]" class="small-text button_priority" size="3" maxlength="3" min="1" max="100" /> %
                </div>
                <button class="button_plus button-plus-chat">+</button>
                <button class="button_min" style="display:none">-</button>
            </li>
        </script>
        <?php
    }

    function twwr_whatsapp_post_header_columns( $defaults ) {
        $defaults['chat_shortcode'] = esc_html__( 'Shortcode', 'twwr' );
        $defaults['chat_view_today'] = esc_html__( 'Today\'s Views', 'twwr' );

        return array(
            'cb' => '<input type="checkbox" />',
            'title' => esc_html__('WhatsApp Chat Name', 'twwr' ),
            'chat_position' => esc_html__( 'Button Position', 'twwr' ),
            'chat_shortcode' => esc_html__( 'Shortcode', 'twwr' ),
            'chat_url' => esc_html__( 'Rotator Url', 'twwr' ),
            'total_wa_number' => '<span class="dashicons dashicons-phone" title="'. esc_html__('WhatsApp number in the chat.', 'twwr') .'"></span>',
            'chat_stats_today' => esc_html__( 'Today', 'twwr' ),
            'chat_stats_yesterday' => esc_html__( 'Yesterday', 'twwr' ) .' <span class="dashicons dashicons-info" title="'. esc_html__('Today\'s data compared to data from same day last week.', 'twwr') .'"></span>',
        );
    }

    function twwr_whatsapp_post_content_columns( $column_name, $post_ID ) {
        global $wpdb;

        $to_utc = get_option('gmt_offset') * -1;
        $time = current_time( 'mysql' );
        $start = date('Y-m-d 00:00:00', strtotime($time));
        $end = date('Y-m-d 23:59:59', strtotime($time));

        $start7 = date('Y-m-d 00:00:00', strtotime($time.'-7 days'));
        $end7 = date('Y-m-d 23:59:59', strtotime($time.'-7 days'));

        if ( $column_name == 'chat_position' ) {
            $chat_position = get_post_meta( $post_ID, '_twwr_whatsapp_chat_style', true );

            if ( $chat_position == 'twwr-floating' ) {
                $chat_position = esc_html__('Floating Widget Button', 'twwr');
            } elseif ( $chat_position == 'twwr-woocommerce' ) {
                $chat_position = esc_html__('Next to Add to Cart Bitton', 'twwr');
            }

            esc_html_e( $chat_position );
        }

        if ( $column_name == 'total_wa_number' ) {
            $meta_numbers = get_post_meta( $post_ID, '_twwr_whatsapp_button', true );
            echo '<span class="wa-numbers">'. count($meta_numbers) .'</span>';
        }

        if ( $column_name == 'chat_stats_today' ) {
            $view_stats = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            if ( !empty( $view_stats ) ) {
                echo esc_html__('Views: ', 'twwr') . $view_stats;
            } else {
                echo esc_html__('Views: ', 'twwr') . '0';
            }

            $click_stats = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            if ( !empty( $click_stats ) ) {
                echo '<br />';
                echo esc_html__('Clicks: ', 'twwr') . $click_stats;
            } else {
                echo '<br />'. esc_html__('Clicks: ', 'twwr') . '0';
            }
        }

        if ( $column_name == 'chat_stats_yesterday' ) {
            $view_stats_today = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            $view_stats = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start7.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end7.$to_utc.' hours')) );

            echo esc_html__('Views: ', 'twwr') . $view_stats;

            $diff = $view_stats_today - $view_stats;
            if( $view_stats > 0 )
                $percent = number_format( $diff / $view_stats * 100);
            else
                $percent = number_format( $diff / 1 * 100 );

            if( $view_stats_today > $view_stats ){
                echo '<span class="dashicons dashicons-arrow-up"></span>';
                echo '<span class="compared positive">+'.$diff.' ‎(+'.$percent.'%)‎</span>';
            }
            else{
                echo '<span class="dashicons dashicons-arrow-down"></span>';
                echo '<span class="compared negative">'.$diff.' ‎('.$percent.'%)‎</span>';
            }

            $click_stats_today = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            $click_stats = $this->db->twwr_whatsapp_chat_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start7.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end7.$to_utc.' hours')) );

                echo '<br />';
                echo esc_html__('Clicks: ', 'twwr') . $click_stats;

            $diff = $click_stats_today - $click_stats;
            if( $click_stats > 0 )
                $percent = number_format( $diff / $click_stats * 100 );
            else
                $percent = number_format( $diff / 1 * 100 );

            if( $click_stats_today > $click_stats ){
                echo '<span class="dashicons dashicons-arrow-up"></span>';
                echo '<span class="compared positive">+'.$diff.' ‎(+'.$percent.'%)‎</span>';
            }
            else{
                echo '<span class="dashicons dashicons-arrow-down"></span>';
                echo '<span class="compared negative">'.$diff.' ‎('.$percent.'%)‎</span>';
            }
        }

        if ( $column_name == 'chat_shortcode' ) {
                $twwr_whatsapp_shortcode_fieldvalue = sprintf( '[twwr-whatsapp-chat id="%1$d"]', $post_ID );
                if ( $twwr_whatsapp_shortcode_fieldvalue )
                    update_post_meta( $post_ID, 'twwr_whatsapp_shortcode', $twwr_whatsapp_shortcode_fieldvalue );

                echo '<input style="max-width: 250px;" type="text" class="chat-shortcode regular-text code" value="'. esc_attr(get_post_meta( $post_ID, 'twwr_whatsapp_shortcode', true )) .'" readonly />';
        }

        if ( $column_name == 'chat_url' ) {
            echo '<input style="max-width: 250px;" type="text" class="chat-url regular-text code" value="'. esc_attr(get_permalink( $post_ID )) .'" readonly />';
        }
    }
}

$TWWR_Post_Type = new TWWR_Post_Type();