<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TWWR_Database {
    public function __construct(){
        global $twwr_whatsapp_chat_db_version;
        $twwr_whatsapp_chat_db_version = '0.0.2';
        register_activation_hook( __FILE__, array($this, 'twwr_whatsapp_chat_create_db' ));
        add_action( 'plugins_loaded', array($this, 'twwr_whatsapp_chat_check_db') );
        add_action( 'wp_ajax_twwr_do_delete', array($this, 'twwr_whatsapp_chat_do_delete'));
        add_action( 'wp_ajax_nopriv_twwr_do_delete', array($this, 'twwr_whatsapp_chat_do_delete'));
    }

    function twwr_whatsapp_chat_check_db(){
        global $twwr_whatsapp_chat_db_version;
        if ( get_option( 'twwr_whatsapp_chat_db_version' ) != $twwr_whatsapp_chat_db_version ) {
            $this->twwr_whatsapp_chat_create_db();
        }
    }
    
    function twwr_whatsapp_chat_create_db() {
        global $wpdb;
        global $twwr_whatsapp_chat_db_version;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'twwr_whatsapp_chat_logs';

        $sql = "CREATE TABLE $table_name (
            parent int(11) NOT NULL,
            ip varchar(16) NOT NULL,
            type varchar(8) NOT NULL,
            location varchar(32),
            browser varchar(128),
            os varchar(128),
            data text,
            time time DEFAULT '00:00:00' NOT NULL,
            date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'twwr_whatsapp_agent_logs';

        $sql = "CREATE TABLE $table_name (
            parent int(11) NOT NULL,
            chat_id int(11) NOT NULL,
            type varchar(8) NOT NULL,
            ip varchar(16) NOT NULL,
            location varchar(32),
            browser varchar(128),
            os varchar(128),
            number varchar(16),
            data text,
            time time DEFAULT '00:00:00' NOT NULL,
            date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'twwr_whatsapp_chat_db_version', $twwr_whatsapp_chat_db_version );
    }

    public function twwr_whatsapp_chat_log_insert( $parent_id, $ip, $type, $data, $location='', $browser='', $os = '' ){
        if( !empty($parent_id) && !empty($type) && !empty($data) ){
            global $wpdb;
    
            $table_name = $wpdb->prefix . 'twwr_whatsapp_chat_logs';
            $wpdb->insert( 
                $table_name, 
                array(
                    'parent'=> $parent_id,
                    'ip'=> $ip,
                    'type' => $type,
                    'location' => $location,
                    'browser' => $browser,
                    'os' => $os,
                    'data' => $data,
                    'time' => gmdate('H:i:s'), 
                    'date_time' => gmdate('Y-m-d H:i:s')
                ) 
            );
        }
        else{
            return false;
        }
    }

    public function twwr_whatsapp_agent_log_insert( $parent_id, $ip, $chat_id, $type, $number, $data, $location='', $browser='', $os = '' ){
        if( !empty($parent_id) && !empty($type) && !empty($ip) && !empty($number) && !empty($chat_id) ){
            global $wpdb;
    
            $table_name = $wpdb->prefix . 'twwr_whatsapp_agent_logs';
            $wpdb->insert( 
                $table_name, 
                array(
                    'parent'=> $parent_id,
                    'chat_id'=> $chat_id,
                    'ip'=> $ip,
                    'type' => $type,
                    'number' => $number,
                    'location' => $location,
                    'browser' => $browser,
                    'os' => $os,
                    'data' => $data,
                    'time' => gmdate('H:i:s'), 
                    'date_time' => gmdate('Y-m-d H:i:s')
                ) 
            );
        }
        else{
            return false;
        }
    }

    public function twwr_whatsapp_chat_get_log( $type='all', $start_date='', $end_date='' ){
        global $wpdb;
        if( empty($start_date) && empty($end_date) ){
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_chat_logs", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE type == '{$type}'", OBJECT );
        }
        else{
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE date_time between '{$start_date}' and '{$end_date}'", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE  type == '{$type}' AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
        }
        
        return $results;
    }

    public function twwr_whatsapp_chat_total_by_id( $id='', $type='all', $start_date='', $end_date='' ){
        global $wpdb;
        
        if( empty($start_date) && empty($end_date) ){
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE parent = {$id}", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE parent = {$id} AND type = '{$type}'", OBJECT );
        }
        else{
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE parent = {$id} AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_chat_logs WHERE parent = {$id} AND type = '{$type}' AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
        }
        
        return $results[0]->total;
    }

    public function twwr_whatsapp_agent_total_by_id( $id='', $type='all', $start_date='', $end_date='' ){
        global $wpdb;
        if( empty($start_date) && empty($end_date) ){
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE parent = {$id}", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE parent = {$id} AND type = '{$type}'", OBJECT );
        }
        else{
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE parent = {$id} AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT count(*) as total FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE parent = {$id} AND type = '{$type}' AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
        }
        
        return $results[0]->total;
    }

    public function twwr_whatsapp_agent_get_log( $type='all', $start_date='', $end_date='' ){
        global $wpdb;
        if( empty($start_date) && empty($end_date) ){
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_agent_logs", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE type = '{$type}'", OBJECT );
        }
        else{
            if( $type == 'all' )
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE date_time between '{$start_date}' and '{$end_date}'", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}twwr_whatsapp_agent_logs WHERE  type = '{$type}' AND date_time between '{$start_date}' and '{$end_date}'", OBJECT );
        }

        return $results;
    }
    
    function twwr_whatsapp_chat_do_delete(){
        global $wpdb;
        
        if ( !is_user_logged_in() && ! wp_verify_nonce( $_POST['nonce'], 'twwr_delete_logs_nonce' ) ) {
            wp_send_json(__('Delete action failed', 'twwr'));
            exit; // Get out of here, the nonce is rotten!
        }
        else{
            if( $_POST['time'] == 'lifetime' ){
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_agent_logs"));
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_chat_logs"));
            }
            else if( $_POST['time'] == '1year' ){
                $time = date("Y", strtotime( current_time( 'mysql' ). "-". $_POST['time'] ) );
                $cur_time = date("Y", strtotime( current_time( 'mysql' )) );
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_agent_logs where YEAR(date_time) >= {$time} AND YEAR(date_time) < {$cur_time}"));
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_chat_logs where YEAR(date_time) >= {$time} AND YEAR(date_time) < {$cur_time}"));
            }
            else{
                $time = date("m", strtotime( current_time( 'mysql' ). "-". $_POST['time'] ) );
                $cur_time = date("m", strtotime( current_time( 'mysql' )) );
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_agent_logs where MONTH(date_time) >= {$time} AND MONTH(date_time) < {$cur_time}"));
                $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}twwr_whatsapp_chat_logs where MONTH(date_time) >= {$time} AND MONTH(date_time) < {$cur_time}"));
            }
            wp_send_json(__('Your log has been deleted', 'twwr'));
        }
    }
}

$TWWR_Database = new TWWR_Database();
