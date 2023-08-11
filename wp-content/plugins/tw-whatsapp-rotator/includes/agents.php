<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'TWWR_Database' ) ) {
	// load chat DB
	include( dirname( __FILE__ ) . '/database.php' );
}

class TWWR_Agents {
    public function __construct(){
        $this->pluginUrl = WP_PLUGIN_URL . '/twwr-whatsapp-chat';
        $this->db = new TWWR_Database;
        
        add_action( 'admin_menu', array($this, 'twwr_whatsapp_agent_add_submenu') );
        add_shortcode( 'twwr-whatsapp-chat', array($this, 'twwr_whatsapp_shortcode'));
        add_action( 'init', array($this, 'twwr_whatsapp_agent_post_type') );
        add_action( 'init', array($this, 'twwr_whatsapp_agent_taxonomy') );
        add_action( 'save_post', array($this, 'twwr_whatsapp_agent_save_meta_box'));
        add_action( 'add_meta_boxes', array($this, 'twwr_whatsapp_agent_meta_box') );
        add_action( 'twwr_whatsapp_chat_agent_edit_process', array($this, 'twwr_whatsapp_agent_edit_process') , 10, 2);
        add_action( 'manage_twwr_whatsapp_agent_posts_custom_column', array($this, 'twwr_whatsapp_agent_content_columns'), 10, 2);
        add_filter( 'manage_twwr_whatsapp_agent_posts_columns', array($this, 'twwr_whatsapp_agent_header_columns'), 10);
        add_filter( 'enter_title_here', array($this, 'twwr_whatsapp_agent_title') );
        add_action( 'load-edit.php', function() {
          add_filter( 'views_edit-twwr_whatsapp_agent', array($this, 'twwr_whatsapp_agent_list_table_after') ); // talk is my custom post type
        });
    }

    function twwr_whatsapp_agent_list_table_after($views) {
        ?>
        <div class="message">
            <p><?php echo sprintf( esc_html__('In WhatsApp Chat list page, click is calculated when %s page is loaded for the first time, before it is being redirected to a WhatsApp number. While in Agents list page, click is calculated when %s page has been clicked and the redirect process to WhatsApp number has begun.', 'twwr'), '<code>domainname.com/wa/chat-name</code>', '<code>domainname.com/wa/chat-name</code>' ); ?></p>
        </div>
        <?php
        return $views;
    }

    function twwr_whatsapp_agent_title( $title ){
        $screen = get_current_screen();
     
        if  ( get_post_type() == 'twwr_whatsapp_agent' ) {
             $title = __('Agent Name', 'twwr');
        }
     
        return $title;
    }

    public function twwr_whatsapp_agent_edit_process($post_id, $news){
        $olds = get_post_meta( $post_id, '_twwr_whatsapp_agent_wa_number', true );
        $args = array(
            'post_type'             => 'twwr_whatsapp_chat',
            'post_status'           => 'publish'
        );

        $chats = get_posts($args);
        if( is_array($chats) && count($chats) > 0 ){
            foreach( $chats as $rot ){
                $all_agent = get_post_meta( $rot->ID, '_twwr_whatsapp_button', true );
                $change = false;
                $new_button = array();
                if( is_array($all_agent) && count($all_agent) > 0 ){
                    foreach($all_agent as $agent ){
                        if( $agent['agent'] == $post_id){
                            foreach($olds as $key => $old){
                                if( $agent['number'] == $old['number'] ){
                                    $new_button[] = array(
                                        'agent' => $agent['agent'],
                                        'number' => $news[$key]['number'],
                                        'label' => $news[$key]['label'],
                                        'priority' => $agent['priority'],
                                    );
                                    $change = true;
                                }
                            }
                        }
                        else{
                            $new_button[] = $agent;
                        }
                    }
                    if($change){
                        update_post_meta(
                            $rot->ID,
                            '_twwr_whatsapp_button',
                            $new_button
                        );
                    }
                }
            }
        }
    }

    function twwr_whatsapp_agent_add_submenu() {
        add_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', esc_html( 'Add New Agent', 'twwr' ), esc_html( 'Add New Agent', 'twwr' ), 'manage_options', 'post-new.php?post_type=twwr_whatsapp_agent' );
    }

    function twwr_whatsapp_agent_post_type() {
        $labels = array(
            'name'                  => esc_html__( 'Agents', 'twwr' ),
            'singular_name'         => esc_html__( 'Agent', 'twwr' ),
            'add_new'               => esc_html__( 'Add Agent', 'twwr' ),
            'add_new_item'          => esc_html__( 'Add Agent', 'twwr' ),
            'edit_item'             => esc_html__( 'Edit Agent', 'twwr' ),
            'new_item'              => esc_html__( 'New Agent', 'twwr' ),
            'view_item'             => esc_html__( 'View Agent', 'twwr' ),
            'search_items'          => esc_html__( 'Search Agent', 'twwr' ),
            'not_found'             => esc_html__( 'Nothing found', 'twwr' ),
            'not_found_in_trash'    => esc_html__( 'Nothing found in Trash', 'twwr' ),
            'parent_item_colon'     => '',
            'featured_image'        => esc_html__( 'Agent Avatar', 'twwr' ),
            'set_featured_image'    => esc_html__( 'Set Agent Avatar', 'twwr' ),
            'remove_featured_image' => esc_html__( 'Remove Agent Avatar', 'twwr' ),
            'use_featured_image'    => esc_html__( 'Use Agent Avatar', 'twwr' ),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=twwr_whatsapp_chat',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'agent', 'with_front' => false, 'feed' => false ),
            'menu_icon'             => 'dashicons-groups',
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_position'         => null,
            'supports'              => array('title', 'thumbnail', 'author')
        );
        register_post_type( 'twwr_whatsapp_agent', $args );
    }

    function twwr_whatsapp_agent_taxonomy() {
        $args = array(
            'label'        => esc_html__( 'Departement', 'twwr' ),
            'rewrite'      => array( 'slug' => 'agent-department' ),
            'hierarchical' => true
        );

        register_taxonomy( 'twwr_agent_department', 'twwr_whatsapp_agent', $args );
    }

    function twwr_whatsapp_agent_save_meta_box( $post_id ) {
        if( get_post_type() == 'twwr_whatsapp_agent' ){
            if (array_key_exists('twwr-whatsapp-email', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_agent_email',
                    $_POST['twwr-whatsapp-email']
                );
            }

            if (array_key_exists('twwr-whatsapp-gender', $_POST)) {
                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_agent_gender',
                    $_POST['twwr-whatsapp-gender']
                );
            }

            if ( array_key_exists( 'twwr_whatsapp_number', $_POST ) ) {
                $arr_button = array();
                foreach ( $_POST['twwr_whatsapp_number'] as $i => $type ) {
                    if ( !empty( $_POST['twwr_whatsapp_number'][$i] ) ) {
                        $arr_button[] = array(
                            'number'  => $_POST['twwr_whatsapp_number'][$i],
                            'label'  => $_POST['twwr_whatsapp_number_label'][$i],
                            'status'  => $_POST['twwr_whatsapp_number_status'][$i],
                        );
                    }
                }

                do_action('twwr_whatsapp_chat_agent_edit_process', $post_id, $arr_button);

                update_post_meta(
                    $post_id,
                    '_twwr_whatsapp_agent_wa_number',
                    $arr_button
                );
            }

            if (array_key_exists('twwr_whatsapp_working_day_allday', $_POST)) {
                update_post_meta($post_id, '_twwr_whatsapp_working_day_allday_status', $_POST['twwr_whatsapp_working_day_allday'] );
            }
            else{
                update_post_meta($post_id, '_twwr_whatsapp_working_day_allday_status', 0 );
                
                update_post_meta($post_id, '_twwr_whatsapp_working_day_monday_status', $_POST['twwr_whatsapp_working_day_monday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_monday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_monday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_monday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_monday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_monday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_monday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_monday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_monday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_tuesday_status', $_POST['twwr_whatsapp_working_day_tuesday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_tuesday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_tuesday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_tuesday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_tuesday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_tuesday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_tuesday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_tuesday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_tuesday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_wednesday_status', $_POST['twwr_whatsapp_working_day_wednesday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_wednesday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_wednesday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_wednesday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_wednesday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_wednesday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_wednesday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_wednesday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_wednesday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_thursday_status', $_POST['twwr_whatsapp_working_day_thursday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_thursday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_thursday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_thursday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_thursday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_thursday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_thursday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_thursday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_thursday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_friday_status', $_POST['twwr_whatsapp_working_day_friday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_friday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_friday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_friday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_friday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_friday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_friday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_friday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_friday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_saturday_status', $_POST['twwr_whatsapp_working_day_saturday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_saturday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_saturday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_saturday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_saturday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_saturday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_saturday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_saturday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_saturday_end'])) );

                update_post_meta($post_id, '_twwr_whatsapp_working_day_sunday_status', $_POST['twwr_whatsapp_working_day_sunday_status'] );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_sunday_start', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_sunday_start'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_sunday_break', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_sunday_break'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_sunday_break_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_sunday_break_end'])) );
                update_post_meta($post_id, '_twwr_whatsapp_working_day_sunday_end', date("H:i:s", strtotime($_POST['twwr_whatsapp_working_day_sunday_end'])) );
            }
        }

    }

    function twwr_whatsapp_agent_meta_box() {
        add_meta_box(
            'twwr_whatsapp_agent_meta_box', // Unique ID
            esc_html__('Agents', 'twwr'), // Box title
            array($this, 'twwr_whatsapp_agent_meta_box_html'), // Content callback, must be of type callable
            array('twwr_whatsapp_agent'), // Post type
            'normal',
            'high'
        );
    }

    function twwr_whatsapp_agent_meta_box_html( $post ) {
        $email = get_post_meta( $post->ID, '_twwr_whatsapp_agent_email', true );
        $gender = get_post_meta( $post->ID, '_twwr_whatsapp_agent_gender', true );
        $wa_numbers = get_post_meta( $post->ID, '_twwr_whatsapp_agent_wa_number', true );
        $operations = get_post_meta( $post->ID, '_twwr_whatsapp_agent_operation', true );

        $sunday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_sunday_status', true );
        $sunday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_sunday_start', true );
        $sunday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_sunday_break', true );
        $sunday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_sunday_break_end', true );
        $sunday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_sunday_end', true );

        $monday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_monday_status', true );
        $monday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_monday_start', true );
        $monday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_monday_break', true );
        $monday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_monday_break_end', true );
        $monday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_monday_end', true );

        $tuesday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_tuesday_status', true );
        $tuesday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_tuesday_start', true );
        $tuesday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_tuesday_break', true );
        $tuesday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_tuesday_break_end', true );
        $tuesday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_tuesday_end', true );

        $wednesday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_wednesday_status', true );
        $wednesday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_wednesday_start', true );
        $wednesday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_wednesday_break', true );
        $wednesday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_wednesday_break_end', true );
        $wednesday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_wednesday_end', true );

        $thursday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_thursday_status', true );
        $thursday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_thursday_start', true );
        $thursday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_thursday_break', true );
        $thursday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_thursday_break_end', true );
        $thursday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_thursday_end', true );

        $friday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_friday_status', true );
        $friday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_friday_start', true );
        $friday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_friday_break', true );
        $friday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_friday_break_end', true );
        $friday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_friday_end', true );

        $saturday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_saturday_status', true );
        $saturday_start = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_saturday_start', true );
        $saturday_break = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_saturday_break', true );
        $saturday_break_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_saturday_break_end', true );
        $saturday_end = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_saturday_end', true );

        $allday_status = get_post_meta( $post->ID, '_twwr_whatsapp_working_day_allday_status', true );
        ?>

        <div class="twwr-whatsapp-admin-post-form">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="twwr-whatsapp-email"><?php esc_html_e( 'Email', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twwr-whatsapp-email" class="regular-text twwr-whatsapp-email" value="<?php echo ($email)? $email : ''; ?>" />
                            <span class="description"><?php esc_html_e( 'Agent email address.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr-whatsapp-gender"><?php esc_html_e( 'Gender', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <select name="twwr-whatsapp-gender" class="regular-text twwr-whatsapp-gender" id="twwr-whatsapp-gender">
                                <option value="<?php esc_html_e('Male', 'twwr'); ?>" <?php echo ($gender == __('Male', 'twwr') ) ? 'selected' : ''; ?>><?php esc_html_e('Male', 'twwr'); ?></option>
                                <option value="<?php esc_html_e('Female', 'twwr'); ?>" <?php echo ($gender == __('Female', 'twwr') ) ? 'selected' : ''; ?>><?php esc_html_e('Female', 'twwr'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr-whatsapp-numbers"><?php echo esc_html_e( 'WhatsApp Number', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <ul class="parent_button parent-button-agent">
                                <?php
                                $key = 0;
                                if ( $wa_numbers ) : ?>
                                    <?php foreach ( $wa_numbers as $key => $number ) : ?>
                                        <li class="button-agent button-contact" daa-setq="<?php esc_attr_e( $key ); ?>">
                                            <span class="dashicons dashicons-move" title="<?php esc_html_e( ' Drag to reorder', 'twwr' ); ?>"></span>

                                            <div class="agent-column">
                                                <span class="heading"><?php echo esc_html__('WhatsApp Number', 'twwr' ); ?></span>
                                                <input type="text" name="twwr_whatsapp_number[<?php esc_attr_e( $key ); ?>]" placeholder="<?php echo esc_html__('Ex: 628170912180', 'twwr' ); ?>" class="widefat wa-number-id" value="<?php esc_attr_e( $number['number'] ) ?>">
                                            </div>

                                            <div class="agent-column">
                                                <span class="heading"><?php echo esc_html__('Notes', 'twwr' ); ?></span>
                                                <input type="text" name="twwr_whatsapp_number_label[<?php esc_attr_e( $key ); ?>]" placeholder="<?php echo esc_html__('Label', 'twwr' ); ?>" class="widefat wa-number-label" value="<?php esc_attr_e( $number['label'] ) ?>">
                                            </div>

                                            <div class="agent-column">
                                                <span class="heading"><?php echo esc_html__('Status', 'twwr' ); ?></span>
                                                <input type="checkbox" name="twwr_whatsapp_number_status[<?php esc_attr_e( $key ); ?>]" value="1" class="wa-number-status" <?php esc_attr_e( ($number['status'] == 1) ? 'checked' : '' ); ?>/><label for="twwr_whatsapp_number_status"><?php echo esc_html__( 'Active', 'twwr' ); ?></label>
                                            </div>

                                            <button class="button_plus button-plus-agent">+</button>
                                            <button class="button_min button-min-agent">-</button>
                                        </li>
                                    <?php endforeach; $key++;?>
                                <?php else :?>
                                    <li class="button-agent button-contact" data-seq="<?php esc_attr_e( $key ); ?>">
                                        <span class="dashicons dashicons-move" title="Drag to reorder"></span>

                                        <div class="agent-column">
                                            <span class="heading"><?php echo esc_html__('WhatsApp Number', 'twwr' ); ?></span>
                                            <input type="text" name="twwr_whatsapp_number[<?php esc_attr_e( $key ); ?>]" placeholder="<?php echo esc_html__('Ex: 628170912180', 'twwr' ); ?>" class="widefat wa-number-id">
                                        </div>

                                        <div class="agent-column">
                                            <span class="heading"><?php echo esc_html__('Notes', 'twwr' ); ?></span>
                                            <input type="text" name="twwr_whatsapp_number_label[<?php esc_attr_e( $key ); ?>]" placeholder="<?php echo esc_html__('Label', 'twwr' ); ?>" class="widefat wa-number-label">
                                        </div>

                                        <div class="agent-column">
                                            <span class="heading"><?php echo esc_html__('Status', 'twwr' ); ?></span>
                                            <input type="checkbox" name="twwr_whatsapp_number_status[<?php esc_attr_e( $key ); ?>]" value="1" class="wa-number-status" checked/><label for="twwr_whatsapp_number_status"><?php echo esc_html__( 'Active', 'twwr' ); ?></label>
                                        </div>
                                        <button class="button_plus button-plus-agent">+</button>
                                        <button class="button_min button-min-agent" style="display:none">-</button>
                                    </li>
                                <?php endif;?>
                            </ul>
                            <span class="description"><?php esc_html_e( 'One Agent can have more than 1 WhatsApp number.', 'twwr' ); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="twwr-whatsapp-gender"><?php esc_html_e( 'Day & Work Hours', 'twwr' ); ?></label>
                        </th>
                        <td>
                            <table id="working-hours" class="wp-list-table widefat striped posts working-day-time-table">
								<thead>
                                    <tr>
                                        <th><b><?php esc_html_e('Day', 'twwr'); ?></b></th>
                                        <th><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></th>
                                        <th><b><?php esc_html_e('Start Work Break', 'twwr'); ?></b></th>
                                        <th><b><?php esc_html_e('End Work Break', 'twwr'); ?></b></th>
                                        <th><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></th>
                                    </tr>
								</thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5">
                                            <input type="checkbox" id="twwr_whatsapp_working_day_allday" name="twwr_whatsapp_working_day_allday" value="1" <?php echo ($allday_status == 1) ? 'checked' : ''; ?>/> <label for="twwr_whatsapp_working_day_allday"><?php esc_html_e( 'WhatsApp number will always active 24 hours, 7 days a week.', 'twwr' ); ?></label>
                                            <p><span class="description"><?php esc_html_e( 'Hours and minutes can be changed by selecting the hours & minutes from the form below.', 'twwr' ); ?></span></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <h3><?php esc_html_e('Manually set the hours & minutes', 'twwr'); ?></h3>
                                        </td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_monday_status" value="1" <?php esc_attr_e( ($monday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Monday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_monday_start" value="<?php esc_attr_e( $monday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_monday_break" value="<?php esc_attr_e( $monday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_monday_break_end" value="<?php esc_attr_e( $monday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_monday_end" value="<?php esc_attr_e( $monday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_tuesday_status" value="1" <?php esc_attr_e( ($tuesday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Tuesday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_tuesday_start" value="<?php esc_attr_e( $tuesday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_tuesday_break" value="<?php esc_attr_e( $tuesday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_tuesday_break_end" value="<?php esc_attr_e( $tuesday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_tuesday_end" value="<?php esc_attr_e( $tuesday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_wednesday_status" value="1" <?php esc_attr_e( ($wednesday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Wednesday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_wednesday_start" value="<?php esc_attr_e( $wednesday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_wednesday_break" value="<?php esc_attr_e( $wednesday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_wednesday_break_end" value="<?php esc_attr_e( $wednesday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_wednesday_end" value="<?php esc_attr_e( $wednesday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_thursday_status" value="1" <?php esc_attr_e( ($thursday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Thursday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_thursday_start" value="<?php esc_attr_e( $thursday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_thursday_break" value="<?php esc_attr_e( $thursday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_thursday_break_end" value="<?php esc_attr_e( $thursday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_thursday_end" value="<?php esc_attr_e( $thursday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_friday_status" value="1" <?php esc_attr_e( ($friday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Friday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_friday_start" value="<?php esc_attr_e( $friday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_friday_break" value="<?php esc_attr_e( $friday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_friday_break_end" value="<?php esc_attr_e( $friday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_friday_end" value="<?php esc_attr_e( $friday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_saturday_status" value="1" <?php esc_attr_e( ($saturday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Saturday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_saturday_start" value="<?php esc_attr_e( $saturday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_saturday_break" value="<?php esc_attr_e( $saturday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_saturday_break_end" value="<?php esc_attr_e( $saturday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_saturday_end" value="<?php esc_attr_e( $saturday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                    <tr class="twwr-whatsapp-working-day">
                                        <td><label><input type="checkbox" name="twwr_whatsapp_working_day_sunday_status" value="1" <?php esc_attr_e( ($sunday_status == 1) ? 'checked' : '' ); ?>/><?php esc_html_e('Sunday', 'twwr'); ?></label></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Start Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_sunday_start" value="<?php esc_attr_e( $sunday_start ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Rest Start Hour', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_sunday_break" value="<?php esc_attr_e( $sunday_break ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('Break Time Over', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_sunday_break_end" value="<?php esc_attr_e( $sunday_break_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                        <td><span class="mobile-label"><b><?php esc_html_e('End Working Hours', 'twwr'); ?></b></span><input type="text" name="twwr_whatsapp_working_day_sunday_end" value="<?php esc_attr_e( $sunday_end ); ?>" class="twwr-whatsapp-chat-timepicker"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <script id="button-agent-template" type="x-jstw-template">
            <li class="button-agent button-contact" data-seq="{{row}}">
                <span class="dashicons dashicons-move" title="Drag to reorder"></span>

                <div class="agent-column">
                    <span class="heading"><?php echo esc_html__('WhatsApp Number', 'twwr' ); ?></span>
                    <input type="text" name="twwr_whatsapp_number[{{row}}]" placeholder="<?php echo esc_html__('Ex: 628170912180', 'twwr' ); ?>" class="widefat wa-number-id">
                </div>

                <div class="agent-column">
                    <span class="heading"><?php echo esc_html__('Notes', 'twwr' ); ?></span>
                    <input type="text" name="twwr_whatsapp_number_label[{{row}}]" placeholder="<?php echo esc_html__('Label', 'twwr' ); ?>" class="widefat wa-number-label">
                </div>

                <div class="agent-column">
                    <span class="heading"><?php echo esc_html__('Status', 'twwr' ); ?></span>
                    <input type="checkbox" name="twwr_whatsapp_number_status[{{row}}]" value="1" class="wa-number-status" checked/><label for="twwr_whatsapp_number_status"><?php echo esc_html__( 'Active', 'twwr' ); ?></label>
                </div>
                <button class="button_plus button-plus-agent">+</button>
                <button class="button_min button-min-agent" style="display:none">-</button>
            </li>
        </script>
        <?php
    }
    
    function twwr_whatsapp_agent_header_columns( $defaults ) {
        $defaults['agent_image'] = esc_html__( 'Avatar', 'twwr' );
        $defaults['wa_numbers'] = esc_html__( 'WhatsApp Number', 'twwr' );
        $defaults['agent_email'] = esc_html__( 'Email', 'twwr' );
        $defaults['agent_view_today'] = esc_html__( 'Today', 'twwr' );
        
        return array(
            'cb' => '<input type="checkbox" />',
            'agent_image' => esc_html__( 'Avatar', 'twwr' ),
            'title' => esc_html__('Agent Name', 'twwr' ),
            'wa_numbers' => esc_html__( 'WhatsApp Number', 'twwr' ),
            'agent_email' => esc_html__( 'Email', 'twwr' ),
            'agent_stats_today' => esc_html__( 'Today', 'twwr' ),
            'agent_stats_yesterday' => esc_html__( 'Yesterday', 'twwr' ) .' <span class="dashicons dashicons-info" title="'. esc_html__('Compared to the same day\'s data a week ago.', 'twwr') .'"></span>',
        );
    }

    function twwr_whatsapp_agent_content_columns( $column_name, $post_ID ) {
        $to_utc = get_option('gmt_offset') * -1;
        $time = current_time( 'mysql' );
        $start = date('Y-m-d 00:00:00', strtotime($time));
        $end = date('Y-m-d 23:59:59', strtotime($time));

        $start7 = date('Y-m-d 00:00:00', strtotime($time.'-7 days'));
        $end7 = date('Y-m-d 23:59:59', strtotime($time.'-7 days'));
        
        if ( $column_name == 'agent_image' ) {
            echo get_the_post_thumbnail( $post_ID, 'twwr-whatsapp-chat-thumb', array( 'class' => 'agent-avatar' ) );
        }

        if ( $column_name == 'wa_numbers' ) {
            $number = array();
            $meta_numbers = get_post_meta( $post_ID, '_twwr_whatsapp_agent_wa_number', true );
            foreach ( $meta_numbers as $numb ) {
                $number[] = $numb['number'];
            }

            echo ( is_array($number) && count($number) > 0 ) ? implode(',<br /> ', $number) : '-';
        }

        if ( $column_name == 'agent_email' ) {
            $meta_email = get_post_meta( $post_ID, '_twwr_whatsapp_agent_email', true );
            esc_html_e ( $meta_email );
        }

        if ( $column_name == 'agent_stats_today' ) {
            $view_stats = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            if ( !empty( $view_stats ) ) {
                echo esc_html__('View: ', 'twwr') . $view_stats;
            } else {
                echo esc_html__('View: ', 'twwr') . '0';
            }

            $click_stats = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            if ( !empty( $click_stats ) ) {
                echo '<br />';
                echo esc_html__('Click: ', 'twwr') . $click_stats;
            } else {
                echo '<br />'. esc_html__('Click: ', 'twwr') . '0';
            }
        }

        if ( $column_name == 'agent_stats_yesterday' ) {
            $view_stats_today = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            $view_stats = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'view', date('Y-m-d H:i:s', strtotime($start7.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end7.$to_utc.' hours')) );

            echo esc_html__('View: ', 'twwr') . $view_stats;

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

            $click_stats_today = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end.$to_utc.' hours')) );
            $click_stats = $this->db->twwr_whatsapp_agent_total_by_id( $post_ID, 'click', date('Y-m-d H:i:s', strtotime($start7.$to_utc.' hours')), date('Y-m-d H:i:s', strtotime($end7.$to_utc.' hours')) );

                echo '<br />';
                echo esc_html__('Click: ', 'twwr') . $click_stats;

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
    }
}

$TWWR_Agents = new TWWR_Agents();
