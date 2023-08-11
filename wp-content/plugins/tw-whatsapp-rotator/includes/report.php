<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'TWWR_Database' ) ) {
	// load chat DB
	include( dirname( __FILE__ ) . '/database.php' );
}

class TWWR_Report {
    public function __construct(){
        add_action( 'admin_menu', array($this, 'twwr_whatsapp_add_submenu_report') );
        $this->db = new TWWR_Database;
    }

    function twwr_whatsapp_add_submenu_report() {
        add_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', esc_html__('Reports', 'twwr'), esc_html__('Reports', 'twwr'), 'manage_options', 'twwr-whatsapp-reports', array($this, 'twwr_whatsapp_reports') );
        add_submenu_page( 'edit.php?post_type=twwr_whatsapp_chat', esc_html__('Logs', 'twwr'), esc_html__('Logs', 'twwr'), 'manage_options', 'twwr-whatsapp-logs', array($this, 'twwr_whatsapp_logs') );
    }

    function twwr_whatsapp_logs() {
        global $wpdb;
        
        $where = '';
        if( isset($_GET['type']) && $_GET['type'] == 'click' ){
            $where = "WHERE TYPE = 'click'";
        }
        else if( isset($_GET['type']) && $_GET['type'] == 'view' ){
            $where = "WHERE TYPE = 'view'";
        }

        if( isset($_GET['groupby']) && $_GET['groupby'] == 'chat' ){
        $query = "SELECT 'chat' AS category, parent, ip, TYPE, location, browser, os, '-' AS number, DATA, TIME, date_time FROM {$wpdb->prefix}twwr_whatsapp_chat_logs {$where}";
        }
        else if( isset($_GET['groupby']) && $_GET['groupby'] == 'agent' ){
            $query = "SELECT 'agent' AS category, parent, ip, TYPE, location, browser, os, number, DATA, TIME, date_time FROM {$wpdb->prefix}twwr_whatsapp_agent_logs {$where}";
        }
        else{
            $query = "SELECT 'chat' AS category, parent, ip, TYPE, location, browser, os, '-' AS number, DATA, TIME, date_time FROM {$wpdb->prefix}twwr_whatsapp_chat_logs {$where} UNION ALL
            SELECT 'agent' AS category, parent, ip, TYPE, location, browser, os, number, DATA, TIME, date_time FROM {$wpdb->prefix}twwr_whatsapp_agent_logs {$where}";
        }

        
        $items_per_page = 25;
        $total_query = "SELECT COUNT(1) FROM ({$query}) as total";
        $total = $wpdb->get_var( $total_query );
        $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
        $offset = ( $page * $items_per_page ) - $items_per_page;
        $chats = $wpdb->get_results( $query . " ORDER BY date_time DESC LIMIT {$offset}, {$items_per_page}" );

        ?>
        <div class="wrap">
            <div id="wpbody" role="main">
                <div id="wpbody-content">
                    <h1><?php esc_html_e( 'WhatsApp Chat Logs', 'twwr'); ?></h1>
                    <div id="poststuff" class="twwr-whatsapp-chat-container">
                        <div class="tablenav top" style="margin: 25px 0;">
                            <form id="twwr-whatsapp-chat-report-filter" method="GET">
                            <input type="hidden" name="post_type" value="twwr_whatsapp_chat">
                            <input type="hidden" name="page" value="twwr-whatsapp-logs">
                            <div class="alignleft actions">
                                <label class="screen-reader-text" for="cat"><?php _e('Filter by category', 'twwr'); ?></label>
                                <select name="groupby" id="cat" class="postform">
                                    <option value="all" <?php echo ( !isset($_GET['groupby']) || $_GET['groupby'] == 'all' ) ? 'selected' : ''; ?>><?php _e('By Chat & Agent Logs', 'twwr'); ?></option>
                                    <option value="chat" <?php echo ( isset($_GET['groupby']) && $_GET['groupby'] == 'chat' ) ? 'selected' : ''; ?>><?php _e('By Chat Logs', 'twwr'); ?></option>
                                    <option value="agent" <?php echo ( isset($_GET['groupby']) && $_GET['groupby'] == 'agent' ) ? 'selected' : ''; ?>><?php _e('By Agent Logs', 'twwr'); ?></option>
                                </select>
                                <label class="screen-reader-text" for="type"><?php _e('Filter by Type', 'twwr'); ?></label>
                                <select name="type" id="type" class="postform">
                                    <option value="all" <?php echo ( !isset($_GET['type']) || $_GET['type'] == 'all' ) ? 'selected' : ''; ?>><?php _e('By Click & View Logs', 'twwr'); ?></option>
                                    <option value="click" <?php echo ( isset($_GET['type']) && $_GET['type'] == 'click' ) ? 'selected' : ''; ?>><?php _e('By Click Logs', 'twwr'); ?></option>
                                    <option value="view" <?php echo ( isset($_GET['type']) && $_GET['type'] == 'view' ) ? 'selected' : ''; ?>><?php _e('By View Logs', 'twwr'); ?></option>
                                </select>
                                <input type="submit" id="post-query-submit" class="button" value="<?php _e('Filter', 'twwr'); ?>">
                            </div>
                            </form>
                        </div>

                        <div class="postbox">
                            <table class="wp-list-table widefat striped posts">
                                <thead>
                                    <th><?php esc_html_e('IP Address', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Category', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Type', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Location', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Referral', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Operating System', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Browser', 'twwr'); ?></th>
                                    <th><?php esc_html_e('Date Time', 'twwr'); ?></th>
                                </thead>
                                <tbody>
                                    <?php if( is_array($chats) && count($chats) > 0 ) : 
                                    foreach($chats as $chat ) :
                                    $data = unserialize($chat->DATA)?>
                                        <tr>
                                            <td><?php esc_html_e( $chat->ip ); ?></td>
                                            <td><?php echo ucwords( $chat->category); ?></td>
                                            <td><?php echo ucwords($chat->TYPE); ?></td>
                                            <td><?php esc_html_e( $chat->location ); ?></td>
                                            <td><a href="<?php echo esc_url( $data['link'] ); ?>"><?php esc_html_e( $data['link'] ); ?></a></td>
                                            <td><?php esc_html_e( $chat->os ); ?></td>
                                            <td><?php esc_html_e( $chat->browser ); ?></td>
                                            <td><?php echo get_date_from_gmt($chat->date_time, get_option('date_format').' '.get_option('time_format')); ?></td>
                                        </tr>
                                    <?php endforeach;
                                    endif; ?>
                                </tbody>
                            </table>

                            <?php
                            echo paginate_links( array(
                                'base' => add_query_arg( 'cpage', '%#%' ),
                                'format' => '',
                                'prev_text' => __('&laquo;'),
                                'next_text' => __('&raquo;'),
                                'total' => ceil($total / $items_per_page),
                                'current' => $page
                            )); ?>
                        </div>

                        <?php if( $total > 0 ) : ?>
                        <div id="remove-logs" class="postbox">
                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Remove Logs Data', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Remove Logs Data', 'twwr'); ?></span></h2>

                            <div class="inside">                                        
                                <?php wp_nonce_field( 'twwr_delete_logs_nonce' ); ?>
                                <select name="twwr_whatsapp_option_delete" class="twwr-whatsapp-option-delete">
                                    <option value="1month"><?php _e('Last Month', 'twwr'); ?></option>
                                    <option value="3month"><?php _e('Last 3 Month', 'twwr'); ?></option>
                                    <option value="6month"><?php _e('Last 6 Month', 'twwr'); ?></option>
                                    <option value="1year"><?php _e('Last Year', 'twwr'); ?></option>
                                    <option value="lifetime"><?php _e('Lifetime', 'twwr'); ?></option>
                                </select>
                                <a href="#" class="button button-primary btn-delete twwr-whatsapp-do-delete"><?php echo esc_html__('Remove Now', 'twwr'); ?></a>
                                <span class="description twwr-whatsapp-delete-message"></span>
                                <span class="description"><?php esc_html_e( 'WARNING: This action is undoable. Please use with caution.', 'twwr' ); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    function twwr_whatsapp_reports() {
        $chats = get_posts( array(
            'post_type' => 'twwr_whatsapp_chat',
        ));

        $agent = get_posts( array(
            'post_type' => 'twwr_whatsapp_agent',
        ));

        if( !isset($_GET['groupby']) || $_GET['groupby'] == 'chat' ){
            $group = 'chat';
        }
        else if( isset($_GET['groupby']) && $_GET['groupby'] == 'agent' ){
            $group = 'agent';
        }
        else{
            $group = 'chat';
        }

        $to_utc = get_option('gmt_offset') * -1;
        $time = current_time( 'mysql' );

        if( !isset($_GET['date']) || $_GET['date'] == 'this-week' ){
            $start = date('Y-m-d', strtotime($time.'-7 days'));
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'yesterday' ){
            $start = date('Y-m-d', strtotime($time.'-1 days'));
            $end = date('Y-m-d 23:59:59', strtotime($time.'-1 days'));
        }
        else if( isset($_GET['date']) && $_GET['date'] == '7-days' ){
            $start = date('Y-m-d', strtotime($time.'-7 days'));
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'today' ){
            $start = date('Y-m-d 00:00:00', strtotime($time));
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'last-week' ){
            $previous_week = strtotime($time.'-1 week +1 day');
            $start = date('Y-m-d', strtotime("last monday", $previous_week));
            $end = date('Y-m-d', strtotime("next sunday", $previous_week));
        }
        else if( isset($_GET['date']) && $_GET['date'] == '14-days' ){
            $start = date('Y-m-d', strtotime($time.'-14 days'));
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'this-month' ){
            $start = date('Y-m-01', strtotime($time));
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'last-month' ){
            $start = date('Y-m-d', strtotime("first day of previous month"));
            $end = date('Y-m-d', strtotime("last day of previous month"));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'this-year' ){
            $start = date('Y-01-01');
            $end = date('Y-m-d 23:59:59', strtotime($time));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'last-year' ){
            $start = date('Y-01-01', strtotime('previous year'));
            $end = date('Y-12-31', strtotime('previous year'));
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'custom' ){
            $start = $_GET['custom_from'].' 00:00:00';
            $end = isset($_GET['custom_to']) ? $_GET['custom_to'].' 23:59:59' : date('Y-m-d H:i:s') ;
        }
        else if( isset($_GET['date']) && $_GET['date'] == 'all' ){
            $date_start = '';
            $date_end = '';
        }

        if( !empty($start) && !empty($end) ){
            $date_start = date('Y-m-d H:i:s', strtotime($start.$to_utc.' hours'));
            $date_end = date('Y-m-d  H:i:s', strtotime($end.$to_utc.' hours'));
        }

        $raw_chat = $this->db->twwr_whatsapp_chat_get_log('all', $date_start, $date_end);
        $raw_agent = $this->db->twwr_whatsapp_agent_get_log('all', $date_start, $date_end);

        $total_view_chat = 0;
        $total_click_chat = 0;
        $total_count_chat = count($chats);
        $total_count_agent = count($agent);

        $chats_view = array();
        $chats_click = array();
        $chats_url_view = array();
        $chats_url_click = array();
        $agent_chart = array();
        $agent_chart_temp = array();
        $agent_chart_label = array();
        $agent_view = array();
        $agent_click = array();
        $main_chart = array();
        $chart_label = array();
        $chart_chat = array();
        $chart_loc = array();
        $chart_loc_temp = array();
        $chart_loc_label = array();
        $chart_loc_bg = array();
        $chart_loc_click = array();
        $chart_loc_click_temp = array();
        $chart_loc_click_label = array();
        $chart_loc_click_bg = array();
        $chart_browser = array();
        $chart_browser_temp = array();
        $chart_browser_label = array();
        $chart_browser_label_temp = array();
        $chart_browser_bg = array();
        $chart_browser_click_temp = array();
        $chart_browser_click_label = array();
        $chart_browser_click_label_temp = array();
        $chart_browser_click_bg = array();

        foreach( $raw_chat as $rot ){
            $data = unserialize($rot->data);
            if( $rot->type == 'view' ){
                if( $group == 'chat' ){
                    $total_view_chat++;
                    if(isset($data['link'])){
                        if( isset($chats_url_view[$data['link']]) )
                            $chats_url_view[$data['link']] = $chats_url_view[$data['link']] + 1;
                        else{
                            $chats_url_view[$data['link']] = 1;
                        }
                    }
                }

                $chats_view[$rot->parent][] = $rot;

                $key = get_date_from_gmt($rot->date_time, 'Ymd');
                if( !isset($main_chart[$key]) )
                    $chart_label[] = get_date_from_gmt($rot->date_time, 'd F');

                if( isset($main_chart[$key][$rot->parent]) )
                    $main_chart[$key][$rot->parent] = $main_chart[$key][$rot->parent] + 1;
                else{
                    $main_chart[$key][$rot->parent] = 1;
                }

                $loc_key = strtolower($rot->location);
                if( !isset($chart_loc_temp[$loc_key]) )
                    $chart_loc_label[] = $rot->location;

                if( isset($chart_loc_temp[$loc_key]) )
                    $chart_loc_temp[$loc_key] = $chart_loc_temp[$loc_key] + 1;
                else{
                    $chart_loc_temp[$loc_key] = 1;
                }

                $browser_key = strtolower($rot->browser).'_'.strtolower($rot->os);
                if( !isset($chart_browser_temp[$browser_key]) )
                    $chart_browser_label_temp[$browser_key] = $rot->browser.' ('.$rot->os.')';

                if( isset($chart_browser_temp[$browser_key]) )
                    $chart_browser_temp[$browser_key] = $chart_browser_temp[$browser_key] + 1;
                else{
                    $chart_browser_temp[$browser_key] = 1;
                }
            }
            if( $rot->type == 'click' ){
                if( $group == 'chat' ){
                    $total_click_chat++;
                    if(isset($data['link'])){
                        if( isset($chats_url_click[$data['link']]) )
                            $chats_url_click[$data['link']] = $chats_url_click[$data['link']] + 1;
                        else{
                            $chats_url_click[$data['link']] = 1;
                        }
                    }
                }

                $browser_key = strtolower($rot->browser).'_'.strtolower($rot->os);
                if( !isset($chart_browser_click_temp[$browser_key]) )
                    $chart_browser_click_label_temp[$browser_key] = $rot->browser.' ('.$rot->os.')';

                if( isset($chart_browser_click_temp[$browser_key]) )
                    $chart_browser_click_temp[$browser_key] = $chart_browser_click_temp[$browser_key] + 1;
                else{
                    $chart_browser_click_temp[$browser_key] = 1;
                }

                $loc_key = strtolower($rot->location);
                if( !isset($chart_loc_click_temp[$loc_key]) )
                    $chart_loc_click_label[] = $rot->location;

                if( isset($chart_loc_click_temp[$loc_key]) )
                    $chart_loc_click_temp[$loc_key] = $chart_loc_click_temp[$loc_key] + 1;
                else{
                    $chart_loc_click_temp[$loc_key] = 1;
                }

                $chats_click[$rot->parent][] = $rot;

            }
        }
        
        arsort($chats_url_view);
        arsort($chats_url_click);
        $chats_url_view = array_slice($chats_url_view, 0, 20);
        $chats_url_click = array_slice($chats_url_click, 0, 20);
        
        $rgb = array(
            'rgba(255, 99, 132, 0.3)',
            'rgba(72, 169, 166, 0.3)',
            'rgba(225, 206, 122, 0.3)',
            'rgba(225, 0, 0, 0.3)',
            'rgba(0, 225, 0, 0.3)',
            'rgba(0, 0, 225, 0.3)',
        );

        foreach( $chats as $i => $temp ){
            $count_temp = array();
            foreach( $main_chart as $main ){
                $count_temp[] = (isset($main[$temp->ID]) ? $main[$temp->ID] : 0);
            }
            $i = $i%6;

            $chart_chat[] = array(
                'label' => $temp->post_title,
                'fill' => false,
                'backgroundColor' => $rgb[$i],
                'borderColor' => $rgb[$i],
                'data' => $count_temp
            );
        }

        $bg_loc = array(
            '#7fb800',
            '#ffb400',
            '#694966',
            '#00a6ed',
            '#f6511d',
            '#0d2c54',
        );

        foreach( $chart_loc_label as $i => $label ){
            $i = $i%6;

            $chart_loc[] = $chart_loc_temp[strtolower($label)];
            $chart_loc_bg[] = $bg_loc[$i];
        }

        foreach( $chart_loc_click_label as $i => $label ){
            $i = $i%6;

            $chart_loc_click[] = $chart_loc_click_temp[strtolower($label)];
            $chart_loc_click_bg[] = $bg_loc[$i];
        }

        $i = 0;
        foreach( $chart_browser_label_temp as $key => $label ){
            $i = $i%6;

            $chart_browser[] = $chart_browser_temp[$key];
            $chart_browser_label[] = $chart_browser_label_temp[$key];
            $chart_browser_bg[] = $bg_loc[$i];
            $i++;
        }

        $i = 0;
        foreach( $chart_browser_click_label_temp as $key => $label ){
            $i = $i%6;

            $chart_browser_click[] = $chart_browser_click_temp[$key];
            $chart_browser_click_label[] = $chart_browser_click_label_temp[$key];
            $chart_browser_click_bg[] = $bg_loc[$i];
            $i++;
        }

        $agent_chat = array();

        foreach( $raw_agent as $temp ){
            $agent_chat[$temp->parent][$temp->chat_id]['log'] = $temp;

            if( $temp->type == 'view' ){
                if( $group == 'agent' )
                    $total_view_chat++;

                if( isset($agent_chat[$temp->parent]['count']['view']) )
                    $agent_chat[$temp->parent]['count']['view'] = $agent_chat[$temp->parent]['count']['view'] + 1;
                else
                    $agent_chat[$temp->parent]['count']['view'] = 1;

                if( isset($agent_chat[$temp->parent][$temp->chat_id]['count']['view']) )
                    $agent_chat[$temp->parent][$temp->chat_id]['count']['view'] = $agent_chat[$temp->parent][$temp->chat_id]['count']['view'] + 1;
                else
                    $agent_chat[$temp->parent][$temp->chat_id]['count']['view'] = 1;
            }
            if( $temp->type == 'click' ){
                if( $group == 'agent' )
                    $total_click_chat++;

                if( isset($agent_chat[$temp->parent]['count']['click']) )
                    $agent_chat[$temp->parent]['count']['click'] = $agent_chat[$temp->parent]['count']['click'] + 1;
                else
                    $agent_chat[$temp->parent]['count']['click'] = 1;

                if( isset($agent_chat[$temp->parent][$temp->chat_id]['count']['click']) )
                    $agent_chat[$temp->parent][$temp->chat_id]['count']['click'] = $agent_chat[$temp->parent][$temp->chat_id]['count']['click'] + 1;
                else
                    $agent_chat[$temp->parent][$temp->chat_id]['count']['click'] = 1;

                $timezone = get_option('gmt_offset');
                $key = date('H', strtotime($temp->time.$timezone.' hours'));

                if( !isset( $agent_chart_label[$temp->parent] ) || !in_array( date('H', strtotime($temp->time.$timezone.' hours')).':00', $agent_chart_label[$temp->parent] ) )
                    $agent_chart_label[$temp->parent][] = date('H', strtotime($temp->time.$timezone.' hours')).':00';

                if( isset($agent_chart_temp[$temp->parent][$temp->chat_id][$key]) )
                    $agent_chart_temp[$temp->parent][$temp->chat_id][$key] = $agent_chart_temp[$temp->parent][$temp->chat_id][$key] + 1;
                else{
                    $agent_chart_temp[$temp->parent][$temp->chat_id][$key] = 1;
                }
            }
        }

        foreach( $agent_chart_label as $par_id => $labels ){
            sort($agent_chart_label[$par_id]);
            sort($labels);
            foreach( $chats as $i => $temp ){
                $dataset_temp = array();

                foreach( $labels as $label ){
                    $key = date('H', strtotime($label));
                    if( isset($agent_chart_temp[$par_id][$temp->ID][$key]) )
                        $dataset_temp[] = $agent_chart_temp[$par_id][$temp->ID][$key];
                    else
                        $dataset_temp[] = 0;
                }

                $i = $i%6;

                if( array_sum($dataset_temp) > 0 ){
                    $agent_chart[$par_id][] = array(
                        'label' => $temp->post_title,
                        'fill' => false,
                        'backgroundColor' => $rgb[$i],
                        'borderColor' => $rgb[$i],
                        'data' => $dataset_temp
                    );
                }
            }
        }

        $total_percent_chat = ( $total_view_chat == 0 ) ? 0 : ($total_click_chat / $total_view_chat) * 100;
        ?>
        <div class="wrap">
            <div id="wpbody" role="main">
                <div id="wpbody-content">
                    <h1><?php esc_html_e( 'WhatsApp Chat Statistics', 'twwr'); ?></h1>
                    <div class="twwr-whatsapp-chat-container">
                        <div class="tablenav top" style="margin: 25px 0;">
                            <form id="twwr-whatsapp-chat-report-filter" method="GET">
                            <div class="alignleft actions">
                                <input type="hidden" name="post_type" value="twwr_whatsapp_chat">
                                <input type="hidden" name="page" value="twwr-whatsapp-reports">
                                <label for="filter-by-date" class="screen-reader-text"><?php _e('Filter by date', 'twwr'); ?></label>
                                <select name="date" id="filter-by-date">
                                    <option value="all" <?php echo (isset($_GET['date']) && $_GET['date'] == 'all') ? 'selected' : ''; ?>><?php _e('All Time', 'twwr'); ?></option>
                                    <option value="today" <?php echo (isset($_GET['date']) && $_GET['date'] == 'today') ? 'selected' : ''; ?>><?php esc_html_e('Today', 'twwr'); ?></option>
                                    <option value="yesterday" <?php echo (isset($_GET['date']) && $_GET['date'] == 'yesterday') ? 'selected' : ''; ?>><?php esc_html_e('Yesterday', 'twwr'); ?></option>
                                    <option value="7-days" <?php echo (isset($_GET['date']) && $_GET['date'] == '7-days') ? 'selected' : ''; ?>><?php esc_html_e('Last 7 Days', 'twwr'); ?></option>
                                    <option value="this-week" <?php echo (!isset($_GET['date']) || $_GET['date'] == 'this-week') ? 'selected' : ''; ?>><?php esc_html_e('This Week', 'twwr'); ?></option>
                                    <option value="last-week" <?php echo (isset($_GET['date']) && $_GET['date'] == 'last-week') ? 'selected' : ''; ?>><?php esc_html_e('Last Week', 'twwr'); ?></option>
                                    <option value="14-days" <?php echo (isset($_GET['date']) && $_GET['date'] == '14-days') ? 'selected' : ''; ?>><?php esc_html_e('Last 14 Days', 'twwr'); ?></option>
                                    <option value="this-month" <?php echo (isset($_GET['date']) && $_GET['date'] == 'this-month') ? 'selected' : ''; ?>><?php esc_html_e('This Month', 'twwr'); ?></option>
                                    <option value="last-month" <?php echo (isset($_GET['date']) && $_GET['date'] == 'last-month') ? 'selected' : ''; ?>><?php esc_html_e('Last Month', 'twwr'); ?></option>
                                    <option value="this-year" <?php echo (isset($_GET['date']) && $_GET['date'] == 'this-year') ? 'selected' : ''; ?>><?php esc_html_e('This Year', 'twwr'); ?></option>
                                    <option value="last-year" <?php echo (isset($_GET['date']) && $_GET['date'] == 'last-year') ? 'selected' : ''; ?>><?php esc_html_e('Last Year', 'twwr'); ?></option>
                                    <option value="custom" <?php echo (isset($_GET['date']) && $_GET['date'] == 'custom') ? 'selected' : ''; ?>><?php esc_html_e('Custom', 'twwr'); ?></option>
                                </select>
								<div class="custom-dates">
									<label class="cd-item">
										<span><?php _e('From', 'twwr'); ?></span>
										<input type="text" name="custom_from" class="date-dropdown" value="<?php echo (isset($_GET['custom_from'])) ? $_GET['custom_from'] : date('Y-m-d') ?>">
									</label>
                                    <label class="cd-item">
										<span><?php _e('To', 'twwr'); ?></span>
										<input type="text" name="custom_to" class="date-dropdown" value="<?php echo (isset($_GET['custom_to'])) ? $_GET['custom_to'] : date('Y-m-d'); ?>">
									</label>
								</div>

                                <label class="screen-reader-text" for="cat"><?php _e('Filter by category', 'twwr'); ?></label>
                                <select name="groupby" id="cat" class="postform">
                                    <option value="chat" <?php echo (isset($_GET['groupby']) && $_GET['groupby'] == 'chat') ? 'selected' : ''; ?>><?php esc_html_e('By WhatsApp Chat', 'twwr'); ?></option>
                                    <option value="agent" <?php echo (isset($_GET['groupby']) && $_GET['groupby'] == 'agent') ? 'selected' : ''; ?>><?php esc_html_e('By Agent', 'twwr'); ?></option>
                                </select>
                                <input type="submit" id="post-query-submit" class="button" value="<?php esc_html_e('Filter', 'twwr'); ?>">
                            </div>
                            </form>
                        </div>
                        <div id="dashboard-widgets-wrap">
                            <div id="dashboard-widgets" class="metabox-holder">
                                <div id="chat-stats">
                                    <h2>
                                        <?php esc_html_e('Report:', 'twwr'); ?>

                                        <?php
                                        // define date state
                                        $date = !empty( $_GET['date'] ) ? esc_html($_GET['date']) : 'today';

                                        if ( $date == '7-days' ) {
                                            $date = esc_html__('Last 7 Days', 'twwr');
                                        } else if ( $date == 'all' ) {
                                            $date = esc_html__('All Time', 'twwr');
                                        } else if ( $date == 'today' ) {
                                            $date = esc_html__('Today', 'twwr');
                                        } else if ( $date == 'yesterday' ) {
                                            $date = esc_html__('Yesterday', 'twwr');
                                        } else if ( $date == 'this-week' ) {
                                            $date = esc_html__('This Week', 'twwr');
                                        } else if ( $date == 'last-week' ) {
                                            $date = esc_html__('Last Week', 'twwr');
                                        } else if ( $date == '14-days' ) {
                                            $date = esc_html__('Last 14 Days', 'twwr');
                                        } else if ( $date == 'this-month' ) {
                                            $date = esc_html__('This Month', 'twwr');
                                        } else if ( $date == 'last-month' ) {
                                            $date = esc_html__('Last Month', 'twwr');
                                        } else if ( $date == 'this-year' ) {
                                            $date = esc_html__('This Year', 'twwr');
                                        } else if ( $date == 'last-year' ) {
                                            $date = esc_html__('Last Year', 'twwr');
                                        } else if ( $date == 'custom' ) {
                                            $date = esc_html__('Custom', 'twwr');
                                        } else {
                                            $date = esc_html__('Last 7 Days', 'twwr');
                                        }

                                        esc_html_e( $date );
                                        ?>
                                    </h2>

                                    <div class="stat-column">
                                        <span class="dashicons dashicons-welcome-view-site"></span>
                                        <h3><?php echo number_format($total_view_chat); ?></h3>
                                        <span><?php esc_html_e( 'Total Views', 'twwr' ); ?></span>
                                    </div>
                                    <div class="stat-column">
                                        <span class="dashicons dashicons-randomize"></span>
                                        <h3><?php echo number_format($total_click_chat); ?></h3>
                                        <span><?php esc_html_e( 'Total Click', 'twwr' ); ?></span>
                                    </div>
                                    <div class="stat-column">
                                        <span class="dashicons dashicons-image-filter"></span>
                                        <h3><?php echo number_format( $total_percent_chat, 2 ); ?>%</h3>
                                        <span><?php esc_html_e( 'Click Percentage', 'twwr' ); ?></span>
                                    </div>
                                    <div class="stat-column">
                                        <span class="dashicons dashicons-networking"></span>
                                        <h3><?php echo number_format($total_count_chat); ?></h3>
                                        <span><?php esc_html_e( 'WhatsApp Chat', 'twwr' ); ?></span>
                                    </div>
                                    <div class="stat-column">
                                        <span class="dashicons dashicons-groups"></span>
                                        <h3><?php echo number_format($total_count_agent); ?></h3>
                                        <span><?php esc_html_e( 'Agent', 'twwr' ); ?></span>
                                    </div>
                                </div>

                                <div id="chat" class="postbox-container" style="width: 100% !important;">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('WhatsApp Chat Graph', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('WhatsApp Chat Graph', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <canvas id="myChart"></canvas>

                                                    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
                                                    <script>
                                                        var ctx = document.getElementById('myChart').getContext('2d');
                                                        var chart = new Chart(ctx, {
                                                            // The type of chart we want to create
                                                            type: 'bar',

                                                            // The data for our dataset
                                                            data: {
                                                                labels: <?php echo json_encode($chart_label); ?>,
                                                                datasets: <?php echo json_encode($chart_chat); ?>
                                                            },

                                                            // Configuration options go here
                                                            options: {
                                                                responsive: true,
                                                                legend: {
                                                                    display: true,
                                                                    position: 'bottom',
                                                                    labels: {
                                                                        fontColor: '#999999',
                                                                        fontSize: 12
                                                                    }
                                                                },
                                                                scales: {
                                                                    xAxes: [{
                                                                        barPercentage: 0.5,
                                                                        barThickness: 25,
                                                                        maxBarThickness: 20,
                                                                        minBarLength: 10,
                                                                        gridLines: {
                                                                            offsetGridLines: true
                                                                        }
                                                                    }]
                                                                }
                                                            }
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="chat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('WhatsApp Chat Statistics', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('WhatsApp Chat Statistics', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <table class="wp-list-table widefat striped posts">
                                                        <thead>
                                                            <th><?php esc_html_e('WhatsApp Chat Name', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Views', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Clicks', 'twwr'); ?></th>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach($chats as $chat) : ?>
                                                                <?php
                                                                // set variable names
                                                                $view = isset( $chats_view[$chat->ID] ) ? count($chats_view[$chat->ID]) : 0;
                                                                $click = isset( $chats_click[$chat->ID] ) ? count($chats_click[$chat->ID]) : 0;
                                                                ?>
                                                                <tr>
                                                                    <td style="width: 60%;"><?php esc_html_e( $chat->post_title ); ?></td>
                                                                    <td align="center"><?php esc_html_e( $view ); ?></td>
                                                                    <td align="center"><?php esc_html_e( $click ); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="chat-agent" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Agent Statistics', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Agent Statistics', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <table class="wp-list-table widefat striped posts">
                                                        <thead>
                                                            <th><?php esc_html_e('Agent Name', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Total Views', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Total Clicks', 'twwr'); ?></th>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ( is_array($agent) && count($agent) > 0 ) : ?>
                                                                <?php foreach( $agent as $temp ) : ?>
                                                                    <?php
                                                                    // set variable names
                                                                    $agent_view = isset( $agent_chat[$temp->ID]['count']['view'] ) ? $agent_chat[$temp->ID]['count']['view'] : 0;
                                                                    $agent_click = isset( $agent_chat[$temp->ID]['count']['click'] ) ? $agent_chat[$temp->ID]['count']['click'] : 0;
                                                                    ?>
                                                                    <tr>
                                                                        <td style="width: 60%;">
                                                                            <?php if ( has_post_thumbnail($temp->ID) ) : ?>
                                                                                <?php echo get_the_post_thumbnail( $temp->ID, 'thumbnail', array( 'class' => 'agent-avatar alignleft' ) ); ?>
                                                                            <?php endif; ?>

                                                                            <?php
                                                                            // add thickbox library
                                                                            add_thickbox();
                                                                            ?>

                                                                            <a href="#TB_inline?&width=900&height=650&inlineId=popup<?php esc_attr_e( $temp->ID ); ?>" class="agent-name thickbox"><?php echo get_the_title($temp->ID); ?></a>

                                                                            <div id="popup<?php esc_attr_e( $temp->ID ); ?>" style="display:none;">
                                                                                <h3>
                                                                                    <?php esc_html_e( 'Report', 'twwr' ); ?> <?php echo get_the_title($temp->ID); ?>
                                                                                </h3>

                                                                                <table class="wp-list-table widefat striped posts">
                                                                                    <thead>
                                                                                        <th><?php esc_html_e('WhatsApp Chat Name', 'twwr'); ?></th>
                                                                                        <th><?php esc_html_e('Number of Views', 'twwr'); ?></th>
                                                                                        <th><?php esc_html_e('Number of Clicks', 'twwr'); ?></th>
                                                                                        <th><?php esc_html_e('Click Percentage', 'twwr'); ?></th>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    <?php if(isset($agent_chat[$temp->ID]) && is_array($agent_chat[$temp->ID])):
                                                                                        foreach( $agent_chat[$temp->ID] as $rot_id => $val ) :
                                                                                            if( $rot_id != 'count' ) :
                                                                                                $view = isset( $val['count']['view'] ) ? $val['count']['view'] : 0;
                                                                                                $click = isset( $val['count']['click'] ) ? $val['count']['click'] : 0;
                                                                                                $percent = ( $view == 0 ) ? 0 : ($click / $view) * 100;
                                                                                                ?>
                                                                                                    <tr>
                                                                                                        <td><?php echo get_the_title($rot_id); ?></td>
                                                                                                        <td align="center"><?php esc_html_e( $view ); ?></td>
                                                                                                        <td align="center"><?php esc_html_e( $click ); ?></td>
                                                                                                        <td align="center"><?php echo number_format( $percent ); ?>%</td>
                                                                                                    </tr>
                                                                                            <?php endif;
                                                                                        endforeach;
                                                                                    endif; ?>
                                                                                    </tbody>
                                                                                </table>

                                                                                <h3><?php esc_html_e( 'Best Hour by Clicks', 'twwr' ); ?></h3>
                                                                                <canvas id="agent-detail-chart<?php esc_attr_e( $temp->ID ); ?>"></canvas>
                                                                                <script>
                                                                                    var ctx = document.getElementById('agent-detail-chart<?php esc_attr_e( $temp->ID ); ?>').getContext('2d');
                                                                                    var chart = new Chart(ctx, {
                                                                                        // The type of chart we want to create
                                                                                        type: 'line',

                                                                                        // The data for our dataset
                                                                                        data: {
                                                                                            labels: <?php echo json_encode(@$agent_chart_label[$temp->ID]); ?>,
                                                                                            datasets: <?php echo json_encode(@$agent_chart[$temp->ID]); ?>
                                                                                        },

                                                                                        // Configuration options go here
                                                                                        options: {
                                                                                            responsive: true,
                                                                                            legend: {
                                                                                                display: true,
                                                                                                position: 'right',
                                                                                                labels: {
                                                                                                    fontColor: '#999999',
                                                                                                    fontSize: 12
                                                                                                }
                                                                                            },
                                                                                            scales: {
                                                                                                xAxes: [{
                                                                                                    barPercentage: 0.5,
                                                                                                    barThickness: 25,
                                                                                                    maxBarThickness: 20,
                                                                                                    minBarLength: 10,
                                                                                                    gridLines: {
                                                                                                        offsetGridLines: true
                                                                                                    }
                                                                                                }]
                                                                                            }
                                                                                        }
                                                                                    });
                                                                                </script>
                                                                            </div>
                                                                        </td>
                                                                        <td align="center"><?php esc_html_e( $agent_view ); ?></td>
                                                                        <td align="center"><?php esc_html_e( $agent_click ); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if( isset($chart_browser) && count($chart_browser) > 0 ) : ?>
                                <div id="chat-browser-view-stat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Browser Statistics (Views)', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Browser Statistics (Views)', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <canvas id="browser-view-chart"></canvas>
                                                    <script>
                                                        // get the pie chart canvas
                                                        var chartID = jQuery("#browser-view-chart");

                                                        // pie chart data
                                                        var browserData = {
                                                            labels: <?php echo json_encode($chart_browser_label); ?>,
                                                            datasets: [{
                                                                    label: '<?php _e('Statistik View Browser', 'twwr'); ?>',
                                                                    data: <?php echo json_encode($chart_browser); ?>,
                                                                    backgroundColor: <?php echo json_encode($chart_browser_bg); ?>,
                                                            }]
                                                        };

                                                        // options
                                                        var options = {
                                                            responsive: true,
                                                            legend: {
                                                                display: true,
                                                                position: 'right',
                                                                labels: {
                                                                    fontColor: '#999999',
                                                                    fontSize: 14
                                                                }
                                                            },
                                                            tooltips: {
                                                                callbacks: {
                                                                    label: function(tooltipItem, data) {
                                                                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                                        var tooltipLabel = data.labels[tooltipItem.index];
                                                                        var tooltipData = allData[tooltipItem.index];
                                                                        var total = 0;
                                                                        for (var i in allData) {
                                                                            total += allData[i];
                                                                        }
                                                                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                                    }
                                                                }
                                                            }
                                                        };

                                                        //create Chart class object
                                                        var browserChart = new Chart(chartID, {
                                                            type: 'pie',
                                                            data: browserData,
                                                            options: options
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if( isset($chart_browser_click) && count($chart_browser_click) > 0 ) : ?>
                                <div id="chat-browser-click-stat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Browser Statistics (Clicks)', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Browser Statistics (Clicks)', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <canvas id="browser-click-chart"></canvas>
                                                    <script>
                                                        // get the pie chart canvas
                                                        var chartID = jQuery("#browser-click-chart");

                                                        // pie chart data
                                                        var browserData = {
                                                            labels: <?php echo json_encode($chart_browser_click_label); ?>,
                                                            datasets: [{
                                                                    label: '<?php _e('Browser Click Statistics', 'twwr'); ?>',
                                                                    data: <?php echo json_encode($chart_browser_click); ?>,
                                                                    backgroundColor: <?php echo json_encode($chart_browser_click_bg); ?>,
                                                            }]
                                                        };

                                                        // options
                                                        var options = {
                                                            responsive: true,
                                                            legend: {
                                                                display: true,
                                                                position: 'right',
                                                                labels: {
                                                                    fontColor: '#999999',
                                                                    fontSize: 14
                                                                }
                                                            },
                                                            tooltips: {
                                                                callbacks: {
                                                                    label: function(tooltipItem, data) {
                                                                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                                        var tooltipLabel = data.labels[tooltipItem.index];
                                                                        var tooltipData = allData[tooltipItem.index];
                                                                        var total = 0;
                                                                        for (var i in allData) {
                                                                            total += allData[i];
                                                                        }
                                                                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                                    }
                                                                }
                                                            }
                                                        };

                                                        //create Chart class object
                                                        var browserChart = new Chart(chartID, {
                                                            type: 'pie',
                                                            data: browserData,
                                                            options: options
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if( isset($chart_loc) && count($chart_loc) > 0 ) : ?>
                                <div id="chat-location-view-stat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Location Statistics (Views)', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Location Statistics (Views)', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <canvas id="location-chart"></canvas>
                                                    <script>
                                                        // get the pie chart canvas
                                                        var chartID = jQuery("#location-chart");

                                                        // pie chart data
                                                        var locationData = {
                                                            labels: <?php echo json_encode($chart_loc_label); ?>,
                                                            datasets: [
                                                                {
                                                                    label: '<?php _e('Location Statistics (Views)', 'twwr'); ?>',
                                                                    data: <?php echo json_encode($chart_loc); ?>,
                                                                    backgroundColor: <?php echo json_encode($chart_loc_bg); ?>,
                                                                }
                                                            ]
                                                        };

                                                        // options
                                                        var options = {
                                                            responsive: true,
                                                            legend: {
                                                                display: true,
                                                                position: 'right',
                                                                labels: {
                                                                    fontColor: '#999999',
                                                                    fontSize: 14
                                                                }
                                                            },
                                                            tooltips: {
                                                                callbacks: {
                                                                    label: function(tooltipItem, data) {
                                                                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                                        var tooltipLabel = data.labels[tooltipItem.index];
                                                                        var tooltipData = allData[tooltipItem.index];
                                                                        var total = 0;
                                                                        for (var i in allData) {
                                                                            total += allData[i];
                                                                        }
                                                                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                                    }
                                                                }
                                                            }
                                                        };

                                                        //create Chart class object
                                                        var locationChart = new Chart(chartID, {
                                                            type: 'pie',
                                                            data: locationData,
                                                            options: options
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if( isset($chart_loc_click) && count($chart_loc_click) > 0 ) : ?>
                                <div id="chat-location-click-stat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Location Statistics (Clicks)', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Location Statistics (Clicks)', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <canvas id="location-chart-click"></canvas>
                                                    <script>
                                                        // get the pie chart canvas
                                                        var chartID = jQuery("#location-chart-click");

                                                        // pie chart data
                                                        var locationData = {
                                                            labels: <?php echo json_encode($chart_loc_click_label); ?>,
                                                            datasets: [
                                                                {
                                                                    label: '<?php _e('Location Statistics (Clicks)', 'twwr'); ?>',
                                                                    data: <?php echo json_encode($chart_loc_click); ?>,
                                                                    backgroundColor: <?php echo json_encode($chart_loc_click_bg); ?>,
                                                                }
                                                            ]
                                                        };

                                                        // options
                                                        var options = {
                                                            responsive: true,
                                                            legend: {
                                                                display: true,
                                                                position: 'right',
                                                                labels: {
                                                                    fontColor: '#999999',
                                                                    fontSize: 14
                                                                }
                                                            },
                                                            tooltips: {
                                                                callbacks: {
                                                                    label: function(tooltipItem, data) {
                                                                        var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                                        var tooltipLabel = data.labels[tooltipItem.index];
                                                                        var tooltipData = allData[tooltipItem.index];
                                                                        var total = 0;
                                                                        for (var i in allData) {
                                                                            total += allData[i];
                                                                        }
                                                                        var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                                        return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                                    }
                                                                }
                                                            }
                                                        };

                                                        //create Chart class object
                                                        var locationChart = new Chart(chartID, {
                                                            type: 'pie',
                                                            data: locationData,
                                                            options: options
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div id="chat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Url Statistics', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Url Statistics', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <table class="wp-list-table widefat striped posts">
                                                        <thead>
                                                            <th><?php esc_html_e('URL', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Number of WhatsApp Chat Views', 'twwr'); ?></th>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach($chats_url_view as $label => $view ) : ?>
                                                                <tr>
                                                                    <td style="width: 60%;"><a href="<?php echo esc_url( $label ); ?>"><?php esc_html_e( $label ); ?></td>
                                                                    <td align="center"><?php esc_html_e( $view ); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="chat" class="postbox-container">
                                    <div class="meta-box-sortables ui-sortable">
                                        <div class="postbox">
                                            <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text"><?php esc_html_e('Url Statistics', 'twwr'); ?></span><span class="toggle-indicator" aria-hidden="true"></span></button>

                                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Url Statistics', 'twwr'); ?></span></h2>

                                            <div class="inside">
                                                <div class="twwr-whatsapp-chat-widget">
                                                    <table class="wp-list-table widefat striped posts">
                                                        <thead>
                                                            <th><?php esc_html_e('URL', 'twwr'); ?></th>
                                                            <th><?php esc_html_e('Number of WhatsApp Chat Clicks', 'twwr'); ?></th>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach($chats_url_click as $label => $view ) : ?>
                                                                <tr>
                                                                    <td style="width: 60%;"><a href="<?php echo esc_url( $label ); ?>"><?php esc_html_e( $label ); ?></a></td>
                                                                    <td align="center"><?php esc_html_e( $view ); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

$TWWR_Report = new TWWR_Report();