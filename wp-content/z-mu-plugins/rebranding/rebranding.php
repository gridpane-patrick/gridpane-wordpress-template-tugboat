<?php

/**
 * Returns an array with the usernames that are super admin. Edit the array in this function to add your super admin users.
 * Put the usernames in quotes and separate by comma like it is shown below.
 * @return array
 */

function rpt_get_super_admin_usernames() {
    return Array( 'superadmin1', 'superadmin2' );
}

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Adds the theme categories section on the themes page
add_action( 'admin_notices', 'rpt_add_theme_categories' );

// Changes the theme data for non super admins
add_filter( 'wp_prepare_themes_for_js', 'rpt_rebrand_theme_data' );

// Adds the Theme Rebranding admin menu for super admins
add_action( 'admin_menu', 'rpt_theme_rebranding_menu' );

// Changes the plugin names for super admins on the plugins page, if rebranded
add_filter( 'all_plugins', 'rpt_change_plugin_name_super_admin' );

// Adds the Rebranding action link to each plugin on the Plugins admin page for super admins and some hidden data
add_filter( 'plugin_action_links', 'rpt_add_edit_plugin_link', 10, 2 );

// Adds scripts and a hidden html form to the bottom of the plugins page
add_action( 'admin_print_footer_scripts', 'rpt_print_admin_scripts' );

// Adds some styles for the plugins page and some styles and scripts to change some themes data on the themes page
add_action( 'admin_head', 'rpt_print_admin_head_styles' );

// Add some styles and scripts to rebrand themes in the customizer
add_action( 'customize_controls_head', 'rpt_print_customizer_head_assets' );

// Handles the ajax request to save the rebranding plugin data
add_action( 'wp_ajax_rpt_save_plugin_action', 'rpt_save_plugin_callback' );

// Handles the ajax request to save the rebranding theme data
add_action( 'wp_ajax_rpt_save_theme_action', 'rpt_save_theme_callback' );

// Outputs the rebranded version of the plugins page, blocking the usual loading process in the admin header section
add_action( 'in_admin_header', 'rpt_rebranded_plugins_page' );

// Loads the plugin's translated strings from the languages folder inside the plugin folder
add_action( 'init', 'rpt_load_plugin_textdomain' );

// Loads the plugin's translated strings from the languages folder inside the plugin folder
function rpt_load_plugin_textdomain() {
    load_plugin_textdomain( 'rpt_rebranding', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Adds the Theme Rebranding admin menu
function rpt_theme_rebranding_menu() {
    if ( rpt_is_current_user_super_admin() ) {
        add_submenu_page(
            'themes.php',
            esc_html__( 'Theme Rebranding', 'rpt_rebranding' ),
            esc_html__( 'Theme Rebranding', 'rpt_rebranding' ),
            'manage_options',
            'rpt-theme-rebranding',
            'rpt_theme_rebranding_page'
        );
    }
}

// Outputs the page where super admins can rebrand themes
function rpt_theme_rebranding_page() {
    if ( rpt_is_current_user_super_admin() ) {
        $ajax_nonce = wp_create_nonce( 'rpt_themes_ajax_nonce' );
        $default_theme_image_url = rpt_get_theme_default_image_url();
        ?>
        <style type="text/css">
        .rpt-theme-image {
            width: 170px;
        }
        .rpt-themes-table td,
        .rpt-themes-table th {
            padding: 10px;
            background: #fff;
        }
        .rpt-themes-table {
            background: #ddd;
            border-spacing: 1px;
        }
        #rpt-rebrand-theme-form-row td,
        #rpt-rebrand-theme-form-row th {
            border-bottom: 1px solid #dadada;
        }
        #rpt-rebrand-theme-form-row {
            display:table-row;
        }
        #rpt-rebrand-theme-form-cell {
            display:table-cell !important;
        }
        .rpt-float-right {
            float: right;
        }
        .rpt-hide {
            display:none !important;
        }
        .rpt-red {
            color:#cc0000;
        }
        .rpt-green {
            color:#00aa00;
        }
        .rpt-active-rebranding td,
        .rpt-active-rebranding th,
        .rpt-active-rebranding {
            background: #fcf9e8 !important;
        }
        #rpt-rebrand-theme-form-row.rpt-add-left-border #rpt-rebrand-theme-form-cell {
            border-left: 5px solid blue;
        }
        .rpt-rebrand-theme-form {
            display:flex;
        }
        .rpt-column-1 {
            width: 40%;
            padding: 6px 30px 6px 6px;
        }
        .rpt-column-2{
            width: 15%;
            padding: 6px 30px 6px 6px;
        }
        .rpt-column-3 {
            width: 45%;
            padding: 6px 30px 6px 6px;
        }
        .rpt-rebrand-theme-form label,
        .rpt-rebrand-theme-form .rpt-fake-label {
            display:flex;
            width: 100%;
            margin: 11px 0;
        }
        .rpt-rebrand-theme-form .rpt-column-2 label {
            line-height: 34px;
        }
        .rpt-rebrand-theme-form .rpt-column-2 input[type=checkbox] {
            margin: 9px 7px 0 0;
        }
        .rpt-rebrand-theme-form .rpt-field-hint {
            color: gray;
        }
        .rpt-rebrand-theme-form .rpt-field-name {
            padding-top: 5px;
        }
        .rpt-rebrand-theme-form .rpt-column-1 .rpt-field-name {
            width: 100px;
        }
        .rpt-rebrand-theme-form .rpt-column-3 .rpt-field-name {
            width: 210px;
        }
        .rpt-rebrand-theme-form .rpt-field-field {
            flex: auto;
        }
        .rpt-rebrand-theme-form input[type=text],
        .rpt-rebrand-theme-form textarea {
            width: 100%;
            padding: 2px 6px;
            margin: 0;
        }
        #rpt-theme-description {
            height: 80px;
        }
        #rpt-theme-image-preview {
            width: 120px;
        }
        #rpt-theme-save,
        #rpt-theme-cancel,
        #rpt-theme-save-status {
            float: right;
        }
        #rpt-theme-cancel,
        #rpt-theme-save-status {
            margin-right:8px;
        }
        #rpt-theme-save-status {
            line-height: 30px;
        }
        @-webkit-keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% {  opacity: 0.3; }
        }
        @keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        .rpt-loading-image {
            -webkit-animation: pulse 1s infinite ease-in-out;
            -o-animation: pulse 1s infinite ease-in-out;
            -ms-animation: pulse 1s infinite ease-in-out;
            -moz-animation: pulse 1s infinite ease-in-out;
            animation: pulse 1s infinite ease-in-out;
            cursor: wait;
        }
        .rpt-themes-table hr {
            visibility: hidden;
        }
        @media(max-width: 1500px) {
            .rpt-rebrand-theme-form label,
            .rpt-rebrand-theme-form .rpt-fake-label {
                display:flex;
                flex-direction:column;
            }
            .rpt-rebrand-theme-form label.checkbox-label {
                flex-direction:row;
            }
            .rpt-field-field .button {
                vertical-align: top !important;
            }
            .rpt-column-1 {
                width: 35%;
                padding: 6px 15px 6px 6px;
            }
            .rpt-column-2{
                width: 20%;
                padding: 6px 15px 6px 6px;
            }
            .rpt-column-3 {
                width: 45%;
                padding: 6px 15px 6px 6px;
            }
        }
        @media(max-width: 1200px) {
            .rpt-column-1,
            .rpt-column-2,
            .rpt-column-3 {
                width: auto;
                padding: 6px 10px 6px 6px;
            }
            .rpt-rebrand-theme-form {
                flex-direction:column;
            }
        }
        @media(max-width: 767px) {
            table.rpt-themes-table td:nth-of-type(4),
            table.rpt-themes-table th:nth-of-type(4) {
                display:none;
            }
            table.rpt-themes-table th,
            table.rpt-themes-table td {
                text-align: center;
            }
            table.rpt-themes-table #rpt-rebrand-theme-form-row th,
            table.rpt-themes-table #rpt-rebrand-theme-form-row td {
                text-align: left;
            }
            table.rpt-themes-table {
                width: 100%;
            }
        }
        @media(max-width: 500px) {
            #rpt-theme-image {
                display: block;
                margin-bottom: 10px;
            }
            .rpt-theme-image {
                width:100px;
            }
        }
        </style>
        <div class="wrap">
            <h1><?php esc_html_e( 'Theme Rebranding', 'rpt_rebranding' ); ?></h1>
            <table class="rpt-themes-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Image', 'rpt_rebranding' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'rpt_rebranding' ); ?></th>
                        <th><?php esc_html_e( 'Rebrand', 'rpt_rebranding' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'rpt_rebranding' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $themes = wp_get_themes();
                    foreach ( $themes as $theme ) {
                        $stylesheet = sanitize_html_class( $theme->get_stylesheet() );
                        $theme_rebranding = rpt_get_theme( $stylesheet );
                        $screenshot = $theme->get_screenshot();
                        ?>
                        <tr class="rpt-theme-row-<?php echo esc_attr( $stylesheet ); ?>">
                            <td><?php
                            if ( array_key_exists( 'theme_image_url', $theme_rebranding ) && ! empty( $theme_rebranding['theme_image_url'] ) ) {
                                echo "<img class='rpt-theme-image' src='" . esc_url( $theme_rebranding['theme_image_url'] ) . "' />";
                            } elseif ( ! empty( $screenshot ) ) {
                                echo "<img class='rpt-theme-image' src='" . esc_url( $screenshot ) . "' />";
                            } else {
                                echo "<img class='rpt-theme-image' src='" . esc_url( $default_theme_image_url ) . "' />";
                            }
                            ?></td>
                            <td class="rpt-theme-name-cell"><strong><?php
                                $theme_name = $theme->Name;
                                if ( array_key_exists( 'theme_name', $theme_rebranding ) && ! empty( $theme_rebranding['theme_name'] ) ) {
                                    $theme_name = $theme_rebranding['theme_name'] . " (" . $theme_name . ")";
                                }
                                echo esc_html( $theme_name );
                            ?></strong></td>
                            <td><a class="button button-primary"
                                href="javascript:themeRebranding('<?php echo esc_attr( $stylesheet ); ?>')"><?php esc_html_e( 'Rebrand', 'rpt_rebranding' ); ?></a></td>
                            <td>
                                <div class="rpt-theme-html-description"><?php
                                if ( array_key_exists( 'theme_description', $theme_rebranding ) && ! empty( $theme_rebranding['theme_description'] ) ) {
                                    echo "<b>" . esc_html__( 'Rebranded Description:', 'rpt_rebranding' ) . "</b><br>" . wp_kses_post( $theme_rebranding['theme_description'] )
                                        . "<hr><b>" . esc_html__( 'Original Description:', 'rpt_rebranding' ) . "</b><br>" . wp_kses_post( $theme['Description'] );
                                } else {
                                    echo wp_kses_post( $theme['Description'] );
                                }
                                ?></div>
                                <hr>
                                <div class="rpt-theme-details">
                                    <?php
                                    $theme_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme['Version'] ) . "&nbsp;&nbsp;|&nbsp;&nbsp;"
                                        . esc_html__( 'Author:', 'rpt_rebranding' ) . " " . wp_kses_post( $theme['Author'] );
                                    if ( ! empty( $theme_rebranding['theme_author'] )
                                        || ( array_key_exists( 'theme_show_author', $theme_rebranding ) && $theme_rebranding['theme_show_author'] !== 'yes' )
                                        || ( array_key_exists( 'theme_show_version', $theme_rebranding ) && $theme_rebranding['theme_show_version'] !== 'yes' ) ) {
                                        $new_details = esc_html__( '[Hidden]', 'rpt_rebranding' );
                                        if ( 'yes' === $theme_rebranding['theme_show_version'] ) {
                                            if ( 'yes' === $theme_rebranding['theme_show_author'] ) {
                                                $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme['Version'] ) . "&nbsp;&nbsp;|&nbsp;&nbsp;Author: "
                                                    . $theme_rebranding['theme_author'];
                                            } else {
                                                $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme['Version'] );
                                            }
                                        }
                                        if ( 'yes' === $theme_rebranding['theme_show_author'] ) {
                                            if ( 'yes' === $theme_rebranding['theme_show_version'] ) {
                                                $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme['Version'] )
                                                    . "&nbsp;&nbsp;|&nbsp;&nbsp;" . esc_html__( 'Author:', 'rpt_rebranding' ) . " "
                                                    . $theme_rebranding['theme_author'];
                                            } else {
                                                $new_details = esc_html__( 'Author:', 'rpt_rebranding' ) . " " .  wp_kses_post( $theme['Author'] );
                                            }
                                        }
                                        $theme_details = "<b>" . esc_html__( 'Rebranded Details:', 'rpt_rebranding' ) . "</b><br>" . $new_details
                                            . "<hr><b>" . esc_html__( 'Original Details:', 'rpt_rebranding' ) . "</b><br>"
                                            . wp_kses_post( $theme_details );
                                    }
                                    echo wp_kses_post( $theme_details );
                                    ?>
                                </div>
                                <textarea class="rpt-hide"
                                    id="rpt-theme-hidden-data-<?php echo esc_attr( $stylesheet ); ?>"><?php
                                    echo esc_textarea( json_encode( rpt_get_theme( $stylesheet) ) ); ?></textarea>
                                <input id="rpt-theme-image-screenshot-<?php echo esc_attr( $stylesheet ); ?>"
                                    value="<?php echo esc_attr( $screenshot ); ?>" type="hidden" />
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <table id="rpt-rebrand-hidden-table" class="rpt-hide">
            <tr id="rpt-rebrand-theme-form-row">
                <td id="rpt-rebrand-theme-form-cell" colspan="10">
                    <div class="rpt-rebrand-theme-form">
                        <div class="rpt-column-1">
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Name', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-theme-name" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Author', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-theme-author" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name">
                                    <?php esc_html_e( 'Description', 'rpt_rebranding' ); ?><br>
                                    <span class="rpt-field-hint"><?php esc_html_e( '(HTML allowed)', 'rpt_rebranding' ); ?></span>
                                </span>
                                <span class="rpt-field-field">
                                    <textarea id="rpt-theme-description" autocomplete="off"></textarea>
                                </span>
                            </label>
                        </div>
                        <div class="rpt-column-2">
                            <label class="checkbox-label">
                                <input type="checkbox" <?php checked( true ); ?>
                                    autocomplete="off" id="rpt-theme-show-author" /> <?php esc_html_e( 'Show Theme Author', 'rpt_rebranding' ); ?>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" <?php checked( true ); ?>
                                    autocomplete="off" id="rpt-theme-show-version" /> <?php esc_html_e( 'Show Theme Version', 'rpt_rebranding' ); ?>
                            </label>
                        </div>
                        <div class="rpt-column-3">
                            <label>
                                <span class="rpt-field-name">
                                    <?php esc_html_e( 'Categories', 'rpt_rebranding' ); ?>
                                    <span class="rpt-field-hint"><?php esc_html_e( '(comma-separated)', 'rpt_rebranding' ); ?></span>
                                </span>
                                <span class="rpt-field-field">
                                    <input id="rpt-theme-categories" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Image URL', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-theme-image-url" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <div class="rpt-fake-label">
                                <span class="rpt-field-name"><?php esc_html_e( 'Image', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <img id="rpt-theme-image-preview" src="<?php echo esc_url( $default_theme_image_url ); ?>"
                                        alt="<?php esc_attr_e( 'Theme Image', 'rpt_rebranding' ); ?>" />&nbsp;
                                </span>
                            </div>
                            <div class="rpt-fake-label">
                                <span class="rpt-field-name">&nbsp;</span>
                                <span class="rpt-field-field">
                                    <input id="rpt-theme-save" class="button button-primary" type="button" value="<?php esc_attr_e( 'Save Theme', 'rpt_rebranding' ); ?>" />
                                    <input id="rpt-theme-cancel" class="button" type="button" value="<?php esc_attr_e( 'Close', 'rpt_rebranding' ); ?>" />
                                    <span id="rpt-theme-save-status"></span>
                                    <input type="hidden" id="rpt-theme-stylesheet" value="" />
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <script type="text/javascript">

        // Shows the box to edit the theme branding information
        function themeRebranding( stylesheet ) {
            jQuery( '#rpt-theme-save-status' ).html( '' ).removeClass( 'rpt-green' ).removeClass( 'rpt-red' );
            jQuery( '.rpt-active-rebranding' ).removeClass( 'rpt-active-rebranding' );
            jQuery( '.rpt-theme-row-' + stylesheet ).after( jQuery( '#rpt-rebrand-theme-form-row' ) ).addClass( 'rpt-active-rebranding' );
            jQuery( '#rpt-rebrand-theme-form-row' ).addClass( 'rpt-active-rebranding' );
            jQuery( '#rpt-theme-stylesheet' ).val( stylesheet );

            // Set the form with the existing data from the hidden textarea
            responseObject = jQuery.parseJSON( jQuery( '#rpt-theme-hidden-data-' + stylesheet ).val() );
            if ( "theme_name" in responseObject ) {
                jQuery( '#rpt-theme-name' ).val( responseObject.theme_name );
            } else {
                jQuery( '#rpt-theme-name' ).val( "" );
            }
            if ( "theme_author" in responseObject ) {
                jQuery( '#rpt-theme-author' ).val( responseObject.theme_author );
            } else {
                jQuery( '#rpt-theme-author' ).val( "" );
            }
            if ( "theme_description" in responseObject ) {
                jQuery( '#rpt-theme-description' ).val( responseObject.theme_description );
            } else {
                jQuery( '#rpt-theme-description' ).val( "" );
            }
            if ( "theme_categories" in responseObject ) {
                jQuery( '#rpt-theme-categories' ).val( responseObject.theme_categories );
            } else {
                jQuery( '#rpt-theme-categories' ).val( "" );
            }
            if ( "theme_image_url" in responseObject ) {
                if ( '<?php echo esc_url( $default_theme_image_url ); ?>' === responseObject.theme_image_url
                    || jQuery( '#rpt-theme-image-screenshot-' + stylesheet ).val() === responseObject.theme_image_url ) {
                    jQuery( '#rpt-theme-image-url' ).val( '' );
                } else {
                    jQuery( '#rpt-theme-image-url' ).val( responseObject.theme_image_url );
                }
                jQuery( '#rpt-theme-image-preview' ).attr( 'src', responseObject.theme_image_url );
            } else {
                jQuery( '#rpt-theme-image-preview' ).attr( 'src', jQuery( '.rpt-theme-row-' + stylesheet + ' .rpt-theme-image' ).attr( 'src' ) );
                jQuery( '#rpt-plugin-image-url' ).val( '' );
            }
            if ( "theme_show_author" in responseObject ) {
                if ( responseObject.theme_show_author === 'yes' ) {
                    jQuery( "#rpt-theme-show-author" ).prop( "checked", true );
                } else {
                    jQuery( "#rpt-theme-show-author" ).prop( "checked", false );
                }
            } else {
                jQuery( "#rpt-theme-show-author" ).prop( "checked", true );
            }
            if ( "theme_show_version" in responseObject ) {
                if ( responseObject.theme_show_version === 'yes' ) {
                    jQuery( "#rpt-theme-show-version" ).prop( "checked", true );
                } else {
                    jQuery( "#rpt-theme-show-version" ).prop( "checked", false );
                }
            } else {
                jQuery( "#rpt-theme-show-version" ).prop( "checked", true );
            }
        }

        // Checks if a string is valid JSON
        function isValidJSON( string ) {
            try {
                JSON.parse( string );
            } catch (e) {
                return false;
            }
            return true;
        }

        jQuery( function() {

            // Save the changes to the database
            jQuery( '#rpt-theme-save' ).click( function() {

                jQuery( '#rpt-theme-save' ).attr( 'disabled', 'disabled' );
                jQuery( '#rpt-theme-save-status' ).html( '<?php esc_html_e( 'Loading...', 'rpt_rebranding' ); ?>' ).removeClass( 'rpt-green' ).removeClass( 'rpt-red' );

                var themeStylesheet = jQuery( '#rpt-theme-stylesheet' ).val();
                var themeName = jQuery( '#rpt-theme-name' ).val();
                var themeAuthor = jQuery( '#rpt-theme-author' ).val();
                var themeDescription = jQuery( '#rpt-theme-description' ).val();
                var themeCategories = jQuery( '#rpt-theme-categories' ).val();
                var themeImageURL = jQuery( '#rpt-theme-image-url' ).val();
                var themeShowAuthor = 'no';
                var themeShowVersion = 'no';
                if ( jQuery( '#rpt-theme-show-author' ).is( ":checked" ) ) {
                    themeShowAuthor = 'yes';
                }
                if ( jQuery( '#rpt-theme-show-version' ).is( ":checked" ) ) {
                    themeShowVersion = 'yes';
                }

                var data = {
                    'action': 'rpt_save_theme_action',
                    'theme_stylesheet': themeStylesheet,
                    'theme_name': themeName,
                    'theme_author': themeAuthor,
                    'theme_description': themeDescription,
                    'theme_categories': themeCategories,
                    'theme_image_url': themeImageURL,
                    'theme_show_author': themeShowAuthor,
                    'theme_show_version': themeShowVersion,
                    'security': '<?php echo esc_attr( $ajax_nonce ); ?>'
                };

                jQuery.post( ajaxurl, data, function( response ) {
                    var resultStatus = "error";
                    response = response.trim();
                    if ( 'no-access' === response ) {
                        alert( "<?php echo esc_js( 'Error: You do not have access to do this action.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'invalid-nonce' === response ) {
                        alert( "<?php echo esc_js( 'Error: Invalid security nonce. Please reload the page and try again.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'missing-data' === response ) {
                        alert( "<?php echo esc_js( 'Error: The required data was not sent in the POST request.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'invalid-url' === response ) {
                        alert( "<?php echo esc_js( 'Error: The image URL is not valid.', 'rpt_rebranding' ); ?>" );
                    } else if ( '' === response || '0' === response ) {
                        alert( "<?php echo esc_js( 'Error: We got an empty response.', 'rpt_rebranding' ); ?>" );
                    } else if ( ! isValidJSON( response ) ) {
                        alert( "<?php echo esc_js( 'Error: We got an unexpected response. It is possible that the task was still '
                            . 'performed but something is not right.', 'rpt_rebranding' ); ?>" );
                    } else {
                        responseObject = jQuery.parseJSON( response );
                        if ( "status" in responseObject && responseObject.status === 'done' && "textareaData" in responseObject
                            && "themeResponseStylesheet" in responseObject && "themeName" in responseObject  && "themeImageURL" in responseObject ) {
                            jQuery( '#rpt-theme-hidden-data-' + responseObject.themeResponseStylesheet ).val( responseObject.textareaData );
                            jQuery( '.rpt-theme-row-' + responseObject.themeResponseStylesheet + ' .rpt-theme-name-cell strong' ).html( responseObject.themeName );
                            jQuery( '.rpt-theme-row-' + responseObject.themeResponseStylesheet + ' .rpt-theme-image' ).attr( 'src', responseObject.themeImageURL );
                            jQuery( '#rpt-theme-image-preview' ).attr( 'src', responseObject.themeImageURL );
                            jQuery( '.rpt-theme-row-' + responseObject.themeResponseStylesheet +
                                ' .rpt-theme-html-description' ).html( responseObject.themeDescription );
                            jQuery( '.rpt-theme-row-' + responseObject.themeResponseStylesheet + ' .rpt-theme-details' ).html( responseObject.themeDetails );
                            resultStatus = "success";
                        } else {
                            alert( "<?php echo esc_js( 'Error: We got an invalid response. '
                                . 'It is possible that the task was still performed but something is not right.', 'rpt_rebranding' ); ?>" );
                        }
                    }
                    jQuery( '#rpt-theme-save' ).removeAttr( 'disabled' );
                    if ( 'success' === resultStatus ) {
                        jQuery( '#rpt-theme-save-status' ).html( '<?php esc_html_e( 'Done', 'rpt_rebranding' ); ?>' ).removeClass( 'rpt-red' ).addClass( 'rpt-green' );
                    } else {
                        jQuery( '#rpt-theme-save-status' ).html( '<?php esc_html_e( 'Error', 'rpt_rebranding' ); ?>' ).removeClass( 'rpt-green' ).addClass( 'rpt-red' );
                    }
                });
            });

            // Hides the box to edit the plugin branding information
            jQuery( '#rpt-theme-cancel' ).click( function() {
                jQuery( '.rpt-active-rebranding' ).removeClass( 'rpt-active-rebranding' );
                jQuery( '#rpt-rebrand-hidden-table' ).append( jQuery( '#rpt-rebrand-theme-form-row' ) );
            });

        });
        </script>

        <?php
    }
}

/**
 * Changes the theme data for non super admins
 * @param array $themes
 * @return array
 */
function rpt_rebrand_theme_data( $themes ) {
    // We only check for super admin and not for page, so it works in the customizer too
   
    array_multisort(array_map(function($element) {
        return  $element['active'] == 1? $element['active'] :$element['hidden'];
    }, $themes), SORT_DESC, $themes);
  
    if ( ! rpt_is_current_user_super_admin() ) {
        foreach ( $themes as $stylesheet => $theme ) {
            $stylesheet = sanitize_html_class( $stylesheet );
            $theme_rebranding = rpt_get_theme( $stylesheet );
           
            if ( is_array( $themes[ $stylesheet ] ) ) {
                if($themes[ $stylesheet ]['hidden'] == -1){
                    unset($themes[ $stylesheet ]['actions']);
                }
                if ( array_key_exists( "actions", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["actions"] )
                    && is_array( $themes[ $stylesheet ]["actions"] ) && array_key_exists( "autoupdate", $themes[ $stylesheet ]["actions"] )
                    && ! empty( $themes[ $stylesheet ]["actions"]["autoupdate"] ) ) {
                    $themes[ $stylesheet ]["actions"]["autoupdate"] = "";
                }
                if ( is_array( $theme_rebranding ) ) {
                    $themes[ $stylesheet ]["rptAuthorClass"] = "";
                    if ( array_key_exists( "name", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["name"] )
                        && array_key_exists( "theme_name", $theme_rebranding ) && ! empty( $theme_rebranding["theme_name"] ) ) {
                        $themes[ $stylesheet ]["name"] = esc_html( $theme_rebranding["theme_name"] );
                    }
                    if ( array_key_exists( "description", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["description"] )
                        && array_key_exists( "theme_description", $theme_rebranding )
                        && ! empty( $theme_rebranding["theme_description"] ) ) {
                        $themes[ $stylesheet ]["description"] = wp_kses_post( $theme_rebranding["theme_description"] );
                    }
                    if ( array_key_exists( "screenshot", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["screenshot"] )
                        && array_key_exists( "theme_image_url", $theme_rebranding ) && ! empty( $theme_rebranding["theme_image_url"] ) ) {
                        $themes[ $stylesheet ]["screenshot"] = array( esc_url( $theme_rebranding["theme_image_url"] ) );
                    }
                    if ( array_key_exists( "tags", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["tags"] )
                        && array_key_exists( "theme_categories", $theme_rebranding )
                        && ! empty( $theme_rebranding["theme_categories"] ) ) {
                        $themes[ $stylesheet ]["tags"] = esc_html( $theme_rebranding["theme_categories"] );
                    }
                    if ( array_key_exists( "author", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["author"] )
                        && array_key_exists( "theme_author", $theme_rebranding ) && ! empty( $theme_rebranding["theme_author"] ) ) {
                        $themes[ $stylesheet ]["author"] = esc_html( $theme_rebranding["theme_author"] );
                    }
                    if ( array_key_exists( "authorAndUri", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["authorAndUri"] )
                        && array_key_exists( "theme_author", $theme_rebranding ) && ! empty( $theme_rebranding["theme_author"] ) ) {
                        $themes[ $stylesheet ]["authorAndUri"] = esc_html( $theme_rebranding["theme_author"] );
                    }
                    if ( array_key_exists( "name", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["name"] )
                        && array_key_exists( "version", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["version"] )
                        && array_key_exists( "theme_show_version", $theme_rebranding )
                        && 'no' === $theme_rebranding["theme_show_version"] ) {
                        $themes[ $stylesheet ]["name"] .= '<span class="rpt-hide-theme-version"></span>';
                        $themes[ $stylesheet ]["version"] = '';
                    }
                    if ( array_key_exists( "author", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["author"] )
                        && array_key_exists( "theme_show_author", $theme_rebranding ) && 'no' === $theme_rebranding["theme_show_author"] ) {
                        $themes[ $stylesheet ]["author"] = '';
                        $themes[ $stylesheet ]["rptAuthorClass"] = 'rpt-hide-theme-author';
                    }
                    if ( array_key_exists( "authorAndUri", $themes[ $stylesheet ] ) && ! empty( $themes[ $stylesheet ]["authorAndUri"] )
                        && array_key_exists( "theme_show_author", $theme_rebranding ) && 'no' === $theme_rebranding["theme_show_author"] ) {
                        $themes[ $stylesheet ]["authorAndUri"] = '';
                        $themes[ $stylesheet ]["rptAuthorClass"] = 'rpt-hide-theme-author';
                    }
                }
            }
        }
    }
    return $themes;
}

// Adds the theme categories section on the themes page
function rpt_add_theme_categories() {
    if ( ! rpt_is_current_user_super_admin() && get_current_screen()->id === 'themes' ) {

        $class_menu_all = "rpt-active";

        if ( isset( $_GET['category'] ) ) {
            $class_menu_all = "";
        }
        $categories = Array();
        $themes = wp_get_themes();
        $stylesheets_to_hide = Array();
        $stylesheets_to_show = Array();
        foreach ( $themes as $theme ) {
            $stylesheet = sanitize_html_class( $theme->get_stylesheet() );
            $theme_rebranding = rpt_get_theme(  $stylesheet );
            if ( is_array( $theme_rebranding ) && array_key_exists( "theme_categories", $theme_rebranding ) && ! empty( $theme_rebranding["theme_categories"] ) ) {
                $categories_string = $theme_rebranding["theme_categories"];
                $exploded_categories = explode( ",", $categories_string );
                $exploded_categories = array_map( 'trim', $exploded_categories );
                foreach ( $exploded_categories as $category ) {
                    if ( ! in_array( $category, $categories ) && ! empty( $category ) ) {
                        $categories[] = $category;
                    }
                }
                if ( isset( $_GET['category'] ) && is_array( $exploded_categories ) && ! in_array( trim( urldecode( $_GET['category'] ) ), $exploded_categories ) ) {
                    $stylesheets_to_hide[] = $stylesheet;
                    continue;
                }
            } elseif ( isset( $_GET['category'] ) ) {
                $stylesheets_to_hide[] = $stylesheet;
                continue;
            }
            $stylesheets_to_show[] = $stylesheet;
        }

        sort( $categories );
        $themes_count = count( $stylesheets_to_show );
        $themes_hide_count = count( $stylesheets_to_hide );

        if ( is_array( $stylesheets_to_hide ) && ! empty( $stylesheets_to_hide ) ) {
            echo "<style type='text/css'>";
            foreach ( $stylesheets_to_hide as $stylesheet_to_hide ) {
                echo ".theme-browser .theme[data-slug='" . esc_attr( $stylesheet_to_hide ) . "'] { display:none !important; }";
            }
            echo "</style>";
        }
        ?>
        <style type="text/css">
        .rpt-themes-menu {
            background: linear-gradient(180deg, rgba(0,0,0,0) 46px, rgba(192,192,192,1) 46px, rgba(0,0,0,0) 47px ),
                linear-gradient(180deg, rgba(0,0,0,0) 92px, rgba(192,192,192,1) 92px, rgba(0,0,0,0) 93px ) #fff;
            border: 1px solid #ccccd0;
            margin: 20px 0 25px 0;
            padding: 0px 10px 0px 10px;
        }
        .rpt-themes-menu a:visited,
        .rpt-themes-menu a {
            line-height: 38px;
            text-decoration: none;
            color: #1d2327;
            margin: 4px 10px 0 10px;
            border-bottom: 4px solid transparent;
            display: inline-block;
        }
        .rpt-themes-menu a:focus {
            -webkit-box-shadow: none;
            box-shadow: none;
            outline: none;
        }
        .rpt-themes-menu a:hover {
            color: #2271b1;
        }
        .rpt-themes-menu a.rpt-active {
            border-color: #646970;
        }
        .rpt-first-menu-category {
            padding: 0;
            width: 0;
        }
        .rpt-first-menu-category::before {
            position: absolute;
            content: "";
            background: #e2e2e5;
            width: 1px;
            height: 47px;
            margin-left: 0px;
            margin-top: -4px;
        }
        </style>
        <div class="rpt-themes-menu">
            <a class="rpt-themes-menu-element <?php echo esc_attr( $class_menu_all ); ?>"
                href="<?php echo esc_url( admin_url( "themes.php" ) ); ?>"><?php esc_html_e( 'All Themes', 'rpt_rebranding' ); ?></a>
            <?php
            $number = 1;
            foreach ( $categories as $category ) {
                if ( 1 === $number ) {
                    ?>
                    <a class="rpt-first-menu-category rpt-themes-menu-element" href="#">&nbsp;</a>
                    <?php
                }
                $add_class = "";
                if ( isset( $_GET['category'] ) && trim( urldecode( $_GET['category'] ) ) === $category ) {
                    $add_class = "rpt-active";
                }
                ?>
                <a class="rpt-themes-menu-element <?php echo esc_attr( $add_class ); ?>"
                    href="<?php echo esc_url( admin_url( "themes.php?category=" . urlencode( $category ) ) ); ?>"
                    ><?php echo esc_html( $category ); ?></a>
                <?php
                $number++;
            }
            ?>
        </div>
        <?php
        if ( $themes_hide_count > 0 ) {
            ?>
            <script type="text/javascript">
            jQuery( function() {
                jQuery( '.wp-heading-inline .theme-count' ).hide();
                jQuery( '.wp-heading-inline .theme-count' ).after( "<span class='title-count theme-count'><?php echo intval( $themes_count ); ?></span>" );
            });
            </script>
            <?php
        }
    }
}

// Outputs the rebranded version of the plugins page, blocking the usual loading process in the admin header section
function rpt_rebranded_plugins_page() {
    if ( ! rpt_is_current_user_super_admin() && rpt_is_plugins_page() ) {
        ?>
        <style type="text/css">
        .rpt-hide {
            display:none !important;
        }
        .rpt-plugins-menu {
            background: linear-gradient(180deg, rgba(0,0,0,0) 46px, rgba(192,192,192,1) 46px, rgba(0,0,0,0) 47px ),
                linear-gradient(180deg, rgba(0,0,0,0) 92px, rgba(192,192,192,1) 92px, rgba(0,0,0,0) 93px ) #fff;
            border: 1px solid #ccccd0;
            margin: 20px 0 0 0;
            padding: 0px 10px 0px 10px;
        }
        .rpt-plugins-menu a:visited,
        .rpt-plugins-menu a {
            line-height: 38px;
            text-decoration: none;
            color: #1d2327;
            margin: 4px 10px 0 10px;
            border-bottom: 4px solid transparent;
            display: inline-block;
        }
        .rpt-plugins-menu a:focus {
            -webkit-box-shadow: none;
            box-shadow: none;
            outline: none;
        }
        .rpt-plugins-menu a:hover {
            color: #2271b1;
        }
        .rpt-plugins-menu a.rpt-active {
            border-color: #646970;
        }
        .rpt-plugins-list-contain {
            margin: 25px 0 0 0;
            display: block;
        }
        .rpt-plugin-box {
            width: calc(25% - 15px);
            margin-bottom: 20px;
            margin-right:15px;
            display: inline-block;
            vertical-align:top;
        }
        .rpt-first-menu-category {
            padding: 0;
            width: 0;
        }
        .rpt-first-menu-category::before {
            position: absolute;
            content: "";
            background: #e2e2e5;
            width: 1px;
            height: 47px;
            margin-left: 0px;
            margin-top: -4px;
        }
        .rpt-plugin-box-bottom {
            background: #f6f7f7;
            border-left: 1px solid #ccccd0;
            border-bottom: 1px solid #ccccd0;
            border-right: 1px solid #ccccd0;
            text-align: right;
            padding: 11px 20px;
        }
        .rpt-plugin-box-top {
            display: flex;
            flex-direction: row;
            padding: 20px;
            justify-content: space-between;
            flex: auto;
            background: #fff;
            border: 1px solid #ccccd0;
        }
        .rpt-plugin-box-top-left {
            width: 128px;
            min-width: 128px;
        }
        .rpt-plugin-box-top-left img {
            width: 128px;
        }
        .rpt-plugin-box-top-middle {
            flex: auto;
            padding: 0 20px;
        }
        .rpt-plugin-box-name {
            line-height: 1.23;
        }
        .rpt-plugin-box-top-middle .rpt-plugin-box-name {
            margin: 0 0 14px 0;
        }
        .rpt-plugin-box-author {
            font-style: italic;
            margin-top: 14px;
        }
        .rpt-plugin-box-middle-mobile h2 {
            margin-top: 0;
        }
        .rpt-plugin-box-middle-mobile {
            padding: 0 20px 20px 20px;
            background: #fff;
            border-bottom: 1px solid #ccccd0;
            border-left: 1px solid #ccccd0;
            border-right: 1px solid #ccccd0;
            margin-top: -5px;
            display: none;
        }
        .rpt-plugin-box-top-right {

            width: 100px;
    min-width: 100px;
    text-align: center;
}


        #rpt-search-plugins {
            vertical-align: middle;
        }
        @media (min-width:2301px) {
            .rpt-plugin-box.rpt-fourth {
                margin-right:0;
            }
        }
        @media (min-width:1700px) and (max-width:2300px) {
            .rpt-plugin-box {
                width: calc(33.33% - 13px);
            }
            .rpt-plugin-box.rpt-third {
                margin-right:0;
            }
        }
        @media (min-width:1200px) and (max-width:1699px) {
            .rpt-plugin-box {
                width: calc(50% - 11px);
            }
            .rpt-plugin-box.rpt-second {
                margin-right:0;
            }
        }
        @media (max-width:1199px) {
            .rpt-plugin-box {
                width: 100%;
                margin-right:0;
            }
        }
        @media (max-width:500px) {
            .rpt-plugin-box-middle-mobile {
                display: block;
            }
            .rpt-plugin-box-top-middle {
                flex: auto;
                padding: 0 20px;
                display: none;
            }
        }
        @media (max-width:782px) {
            html.wp-toolbar {
                padding-top: 46px;
            }
            #wpadminbar {
                position:fixed;
            }
        }
        </style>
        <?php

       
        if ( isset( $_GET['plugin_status'] ) ) {
            if ( 'active' === $_GET['plugin_status'] ) {
                $class_menu_all = "";
                $class_menu_active = "rpt-active";
                $class_menu_inactive = "";
                $show_status = "active";
            }elseif ( 'inactive' === $_GET['plugin_status'] ) {
                $class_menu_all = "";
                $class_menu_active = "";
                $class_menu_inactive = "rpt-active";
                $show_status = "inactive";
            }
        }else{
            $class_menu_all = "rpt-active";
            $class_menu_active = "";
            $class_menu_inactive = "";
            $show_status = "all";
        }

        if ( isset( $_GET['category'] ) ) {
            $class_menu_all = "";
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = apply_filters( 'all_plugins', get_plugins() ); 
        $plugins_to_show = Array();
        $categories = Array();
        array_multisort(array_map(function($element) {
            return  $element['hidden'];
        }, $plugins), SORT_DESC, $plugins);
        foreach ( $plugins as $plugin_file => $plugin_data ) {
            if (isset($_GET['plugin_status'])and in_array($_GET['plugin_status'],['active','inactive']) AND !$plugin_data['hidden']) {
                continue;
            }
            $rebranded_data = rpt_get_plugin(  $plugin_file );
            if ( is_array( $rebranded_data ) && array_key_exists( 'plugin_categories', $rebranded_data ) && ! empty( $rebranded_data['plugin_categories'] ) ) {
                
                $categories_string = $rebranded_data['plugin_categories'];
                $exploded_categories = explode( ",", $categories_string );
                $exploded_categories = array_map( 'trim', $exploded_categories );
                foreach ( $exploded_categories as $category ) {
                    if ( ! in_array( $category, $categories ) && ! empty( $category ) ) {
                        $categories[] = $category;
                    }
                }

                if ( isset( $_GET['category'] ) && is_array( $exploded_categories ) && ! in_array( trim( urldecode( $_GET['category'] ) ), $exploded_categories ) ) {
                    continue;
                }

            } elseif ( isset( $_GET['category'] ) ) {
                continue;
            }

            if ( is_plugin_active( $plugin_file ) ) {
                $plugin_status = "active";
            } else {
                $plugin_status = "inactive";
            }
            if ( ( 'active' === $show_status && 'inactive' === $plugin_status ) || ( 'inactive' === $show_status && 'active' === $plugin_status ) ) {
                continue;
            }
            if ( $plugin_file === "rebranding-plugins-themes/rebranding-plugins-themes.php" || $plugin_file === rpt_current_plugin_file() ) {
                continue;
            }

            $plugins_to_show[ $plugin_file ] = $plugin_data;
        }
        sort( $categories );
        $plugins_count = count( $plugins_to_show );

        ?>
        <div class="wrap">
            <h1>
            إضافات <span class="title-count theme-count"><?php echo intval( $plugins_count ); ?></span>
                <input placeholder="<?php esc_attr_e( 'البحث ...', 'rpt_rebranding' ); ?>" type="search" id="rpt-search-plugins" onInput="rptSearchPlugins()">
            </h1>

            <div class="rpt-plugins-menu">
                <a class="rpt-plugins-menu-element <?php echo esc_attr( $class_menu_all ); ?>"
                    href="<?php echo esc_url( admin_url( "plugins.php" ) ); ?>"><?php esc_html_e( 'جميع الإضافات', 'rpt_rebranding' ); ?></a>
                <a class="rpt-plugins-menu-element <?php echo esc_attr( $class_menu_active ); ?>"
                    href="<?php echo esc_url( admin_url( "plugins.php?plugin_status=active" ) ); ?>"><?php esc_html_e( 'مفعل', 'rpt_rebranding' ); ?></a>
                <a class="rpt-plugins-menu-element <?php echo esc_attr( $class_menu_inactive ); ?>"
                    href="<?php echo esc_url( admin_url( "plugins.php?plugin_status=inactive" ) ); ?>"><?php esc_html_e( 'غير مفعل', 'rpt_rebranding' ); ?></a>
                <?php
                $number = 1;
                foreach ( $categories as $category ) {
                    if ( 1 === $number ) {
                        ?>
                        <a class="rpt-first-menu-category rpt-plugins-menu-element" href="#">&nbsp;</a>
                        <?php
                    }
                    $add_class = "";
                    if ( isset( $_GET['category'] ) && trim( urldecode( $_GET['category'] ) ) === $category ) {
                        $add_class = "rpt-active";
                    }
                    ?>
                    <a class="rpt-plugins-menu-element <?php echo esc_attr( $add_class ); ?>"
                        href="<?php echo esc_url( admin_url( "plugins.php?category=" . urlencode( $category ) ) ); ?>"
                        ><?php echo esc_html( $category ); ?></a>
                    <?php
                    $number++;
                }
                ?>
            </div>

            <div class="rpt-plugins-list-contain">
                <?php
                $number = 1;
                foreach ( $plugins_to_show as $plugin_file => $plugin_data ) {
                    if (isset($_GET['plugin_status'])and in_array($_GET['plugin_status'],['active','inactive']) AND !$plugin_data['hidden']) {
                        continue;
                    }
                    if(!isset($GLOBALS['plans_plugins'][$plugin_file])){
                        continue;
                    }
                    $rebranded_data = rpt_get_plugin(  $plugin_file );
                 
                    $plugin_name =  $plugin_data['Name'];
                    $plugin_author = "";
                    $plugin_image_url = rpt_get_plugin_default_image_url();
                    $plugin_description = "";
                    $plugin_categories = esc_html__( 'غير مصنف', 'rpt_rebranding' );
                    
                    if ( is_array( $rebranded_data ) ) {
                        if ( array_key_exists( 'plugin_name', $rebranded_data ) && ! empty( $rebranded_data['plugin_name'] ) ) {
                            $plugin_name = $rebranded_data['plugin_name'];
                        } else if ( is_array( $plugin_data ) && array_key_exists( 'Name', $plugin_data ) && ! empty( $plugin_data['Name'] ) ) {
                            $plugin_name = $plugin_data['Name'];
                        }
                        if ( ( array_key_exists( 'plugin_show_author', $rebranded_data ) && "yes" === $rebranded_data['plugin_show_author'] )
                            || ! array_key_exists( 'plugin_show_author', $rebranded_data ) ) {
                            if ( array_key_exists( 'plugin_author', $rebranded_data ) && ! empty( $rebranded_data['plugin_author'] ) ) {
                                $plugin_author = $rebranded_data['plugin_author'];
                            } else if ( is_array( $plugin_data ) && array_key_exists( 'AuthorName', $plugin_data ) && ! empty( $plugin_data['AuthorName'] ) ) {
                                $plugin_author = $plugin_data['AuthorName'];
                            }
                        }
                        if ( array_key_exists( 'plugin_description', $rebranded_data ) && ! empty( $rebranded_data['plugin_description'] ) ) {
                            $plugin_description = $rebranded_data['plugin_description'];
                        } else if ( is_array( $plugin_data ) && array_key_exists( 'Description', $plugin_data ) && ! empty( $plugin_data['Description'] ) ) {
                            $plugin_description = $plugin_data['Description'];
                        }
                        if ( array_key_exists( 'plugin_image_url', $rebranded_data ) && ! empty( $rebranded_data['plugin_image_url'] ) ) {
                            $plugin_image_url = $rebranded_data['plugin_image_url'];
                        }
                        if ( array_key_exists( 'plugin_categories', $rebranded_data ) && ! empty( $rebranded_data['plugin_categories'] ) ) {
                            $plugin_categories = $rebranded_data['plugin_categories'];
                        }
                    }
                    $activate_link = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file );
                    $deactivate_link = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file, 'deactivate-plugin_' . $plugin_file );

                    $add_class = '';
                    if ( $number >= 2 && $number % 2 === 0 ) {
                        $add_class .= " rpt-second";
                    }
                    if ( $number >= 3 && $number % 3 === 0 ) {
                        $add_class .= " rpt-third";
                    }
                    if ( $number >= 4 && $number % 4 === 0 ) {
                        $add_class .= " rpt-fourth";
                    }

                    ?>
                    <div class="rpt-plugin-box <?php echo esc_attr( $add_class ); ?>">
                        <div class="rpt-plugin-box-top">
                            <div class="rpt-plugin-box-top-left">
                                <img src="<?php echo esc_url( $plugin_image_url ); ?>" alt="<?php esc_attr_e( 'Plugin Image', 'rpt_rebranding' ); ?>" />
                            </div>
                            <div class="rpt-plugin-box-top-middle">
                                <h2 class="rpt-plugin-box-name"><?php echo esc_html( $plugin_name ); ?></h2>
                                <div class="rpt-plugin-box-description"><?php echo wp_kses_post( $plugin_description ); ?></div>
                                <?php
                                if ( ! empty( $plugin_author ) ) {
                                    ?>
                                    <div class="rpt-plugin-box-author">بواسطة  <?php echo esc_html( $plugin_author ); ?></div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="rpt-plugin-box-top-right">
                                <?php
                                if ($plugin_data['hidden'] == 1) {
                                    if ( ! is_plugin_active( $plugin_file ) ) {
                                        ?>
                                        <a href="<?php echo esc_url( $activate_link ); ?>" class="button button-primary"><?php esc_html_e( 'تفعيل', 'rpt_rebranding' ); ?></a>
                                        <?php
                                    } else {
                                        ?>
                                        <a href="<?php echo esc_url( $deactivate_link ); ?>" class="button button-primary"><?php esc_html_e( 'تعطيل', 'rpt_rebranding' ); ?></a>
                                        <?php
                                    }
                            }elseif(WAAS1_RESTRICTION_GROUP_ID == 5 and isset($GLOBALS['onlyAdminPlugins']) and in_array($plugin_file,$GLOBALS['onlyAdminPlugins'])){

                                ?>
                                        <a href="#" class="button button-primary" style="    pointer-events: none;
                                    cursor: default;
                                    background: #ddd;
                                    color: red;
                                    padding: 0;">
                                   
                                        <p style='width: 99%;white-space: break-spaces;margin: 0;'>تواصل مع مسئول حسابك لتفعيل الاضافة</p>
                                    
                                </a>

                            <?php
                                
                            }else{ ?>
                                        <a href="#" class="button button-primary" style="    pointer-events: none;
                                    cursor: default;
                                    background: #ddd;
                                    color: red;
                                    padding: 0;">
                                    <?php
                                    
                                    if($GLOBALS['plans_plugins'][$plugin_file]){
                                        echo "<p style='width: 99%;white-space: break-spaces;margin: 0;'>متوفر في ".implode(' , ',$GLOBALS['plans_plugins'][$plugin_file])."</p>";
                                    }
                                    ?>
                                    
                                </a>

                            <?php
                                
                            }

                                ?>
                            </div>
                        </div>
                        <div class="rpt-plugin-box-middle-mobile">
                            <h2 class="rpt-plugin-box-name"><?php echo esc_html( $plugin_name ); ?></h2>
                            <div class="rpt-plugin-box-description">
                            <?php

                            // The name and description are displayed here as well, just for small screens separately
                            echo wp_kses_post( $plugin_description );
                            ?>
                            </div>
                            <?php
                            if ( ! empty( $plugin_author ) ) {
                                ?>
                                <div class="rpt-plugin-box-author"><?php esc_html_e( 'بواطسة', 'rpt_rebranding' ); ?> <?php echo esc_html( $plugin_author ); ?></div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="rpt-plugin-box-bottom"><b><?php esc_html_e( 'التصنيفات:', 'rpt_rebranding' ); ?></b> <?php echo esc_html( $plugin_categories ); ?></div>
                    </div>
                    <?php
                    $number++;
                }
                ?>
            </div>
        </div>
        <script type="text/javascript">
        function rptSearchPlugins() {
            var number, pluginName, toFind;
            number = 0;
            toFind = jQuery( '#rpt-search-plugins' ).val().toLowerCase();
            if ( '' === toFind ) {
                jQuery( '.rpt-plugin-box-top-middle .rpt-plugin-box-name' ).each( function( positionInner, objectInner ) {
                    jQuery( this ).closest( '.rpt-plugin-box' ).removeClass( 'rpt-hide' ).removeClass( 'rpt-second' )
                        .removeClass( 'rpt-third' ).removeClass( 'rpt-fourth' );
                    number++;
                    if ( number >= 2 && number % 2 === 0 ) {
                        jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-second' );
                    }
                    if ( number >= 3 && number % 3 === 0 ) {
                        jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-third' );
                    }
                    if ( number >= 4 && number % 4 === 0 ) {
                        jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-fourth' );
                    }
                });
            } else {
                jQuery( '.rpt-plugin-box-top-middle .rpt-plugin-box-name' ).each( function( positionInner, objectInner ) {
                    pluginName = jQuery( this ).html().toLowerCase();
                    if ( pluginName.indexOf( toFind ) > -1 ) {
                        jQuery( this ).closest( '.rpt-plugin-box' ).removeClass( 'rpt-hide' ).removeClass( 'rpt-second' )
                            .removeClass( 'rpt-third' ).removeClass( 'rpt-fourth' );
                        number++;
                        if ( number >= 2 && number % 2 === 0 ) {
                            jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-second' );
                        }
                        if ( number >= 3 && number % 3 === 0 ) {
                            jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-third' );
                        }
                        if ( number >= 4 && number % 4 === 0 ) {
                            jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-fourth' );
                        }
                    } else {
                        jQuery( this ).closest( '.rpt-plugin-box' ).addClass( 'rpt-hide' ).removeClass( 'rpt-second' )
                            .removeClass( 'rpt-third' ).removeClass( 'rpt-fourth' );
                    }
                });
            }
            jQuery( '.title-count.theme-count' ).html( number );
        }
        </script>
        <?php
        do_action( 'admin_footer' );
        do_action( 'in_admin_footer' );
        do_action( 'admin_print_footer_scripts' );
        exit;
    }
}

/**
 * Checks if this is the admin plugins page
 * @return bool
 */
function rpt_is_plugins_page() {
    if ( get_current_screen()->id === 'plugins' ) {
        if ( isset( $_GET['plugin_status'] ) && in_array( $_GET['plugin_status'], Array( 'mustuse', 'dropins' ) ) ) {
            return false;
        }
        return true;
    }
    return false;
}

// Add some styles and scripts to rebrand themes in the customizer
function rpt_print_customizer_head_assets() {
    if ( ! rpt_is_current_user_super_admin() ) {
        $theme = wp_get_theme();
        $stylesheet = sanitize_html_class( $theme->get_stylesheet() );
        $theme_rebranding = rpt_get_theme(  $stylesheet );
        if ( is_array( $theme_rebranding ) && array_key_exists( "theme_name", $theme_rebranding ) && ! empty( $theme_rebranding["theme_name"] ) ) {
            ?>
            <script type="text/javascript">
            jQuery( function() {

                // We change the active theme name in the customizer
                var sidebarHTML = jQuery( "#tmpl-customize-panel-themes" ).html();
                sidebarHTML = sidebarHTML.replace('{{ data.title }}', '<?php echo esc_html( $theme_rebranding['theme_name'] ) ?>');
                jQuery( "#tmpl-customize-panel-themes" ).html( sidebarHTML );
            });
            </script>
            <?php
        }
        ?>
        <style type="text/css">
        .rpt-hide-theme-version ~ .theme-version,
        .rpt-hide-theme-author {
            display: none !important;
        }
        </style>
        <script type="text/javascript">
        jQuery( function() {

            // So we can hide theme author if needed in the customizer
            var themeHTML = jQuery( "#tmpl-customize-themes-details-view" ).html();
            themeHTML = themeHTML.replace('<h3 class="theme-author">', '<h3 class="theme-author {{{ data.rptAuthorClass }}}">');
            jQuery( "#tmpl-customize-themes-details-view" ).html( themeHTML );
        });
        </script>
        <?php
    }
}

// Adds some styles for the plugins page and some styles and scripts to change some themes data on the themes page
function rpt_print_admin_head_styles() {
    $is_super_admin = rpt_is_current_user_super_admin();
    if ( ! $is_super_admin && get_current_screen()->id === 'themes' ) {
        ?>
        <style type="text/css">
        .rpt-hide-theme-version ~ .theme-version,
        .rpt-hide-theme-author {
            display: none !important;
        }
        </style>
        <script type="text/javascript">
        jQuery( function() {

            // So we can hide theme author if needed
            var themeHTML = jQuery( "#tmpl-theme-single" ).html();
            themeHTML = themeHTML.replace('<p class="theme-author">', '<p class="theme-author {{{ data.rptAuthorClass }}}">');
            jQuery( "#tmpl-theme-single" ).html( themeHTML );

            // Move theme category menu under page title, there are no hooks to do it any other way
            jQuery( '.rpt-themes-menu' ).insertAfter( ".wp-header-end" );
            jQuery('.theme-actions .activate').each(function() {
                  if(jQuery(this).attr('href') == ""){
                   jQuery( jQuery(this).parent('.theme-actions')).html(`
                    <a href="#" class="button button-primary" style="pointer-events: none;cursor: default;background: #f9f9f9;color: red;padding: 0;font-size: 1rem;margin: 0;">غير متوفر في باقتك</a>`) .css({opacity: 1})


                  }
            });
        });
        </script>
        <?php
    }
    if ( $is_super_admin && rpt_is_plugins_page() ) {
        ?>
        <style type="text/css">
        #rpt-rebrand-plugin-form-row td,
        #rpt-rebrand-plugin-form-row th {
            border-bottom: 1px solid #dadada;
        }
        #rpt-rebrand-plugin-form-row {
            display:table-row;
        }
        #rpt-rebrand-plugin-form-cell {
            display:table-cell !important;
        }
        .rpt-float-right {
            float: right;
        }
        .rpt-hide {
            display:none !important;
        }
        .rpt-red {
            color:#cc0000;
        }
        .rpt-green {
            color:#00aa00;
        }
        .rpt-active-rebranding td,
        .rpt-active-rebranding th,
        .rpt-active-rebranding {
            background: #fcf9e8 !important;
        }
        #rpt-rebrand-plugin-form-row.rpt-add-left-border #rpt-rebrand-plugin-form-cell {
            border-left: 5px solid blue;
        }
        .rpt-rebrand-plugin-form {
            display:flex;
        }
        .rpt-column-1,
        .rpt-column-3 {
            width: 50%;
            padding: 6px 30px 6px 6px;
        }
        .rpt-rebrand-plugin-form label,
        .rpt-rebrand-plugin-form .rpt-fake-label {
            display:flex;
            width: 100%;
            margin: 11px 0;
        }
        .rpt-rebrand-plugin-form .rpt-column-3 .checkbox-label {
            line-height: 34px;
        }
        .rpt-rebrand-plugin-form .rpt-column-3 input[type=checkbox] {
            margin: 9px 7px 0 0;
        }
        .rpt-rebrand-plugin-form .rpt-field-hint {
            color: gray;
        }
        .rpt-rebrand-plugin-form .rpt-field-name {
            padding-top: 5px;
        }
        .rpt-rebrand-plugin-form .rpt-column-1 .rpt-field-name {
            width: 100px;
        }
        .rpt-rebrand-plugin-form .rpt-column-3 .rpt-field-name {
            width: 210px;
        }
        .rpt-rebrand-plugin-form .rpt-field-field {
            flex: auto;
        }
        .rpt-rebrand-plugin-form input[type=text],
        .rpt-rebrand-plugin-form textarea {
            width: 100%;
            padding: 2px 6px;
            margin: 0;
        }
        #rpt-plugin-description {
            height: 80px;
        }
        #rpt-plugin-image {
            width: 120px;
        }
        #rpt-plugin-save,
        #rpt-plugin-cancel,
        #rpt-plugin-save-status {
            float: right;
        }
        #rpt-plugin-cancel,
        #rpt-plugin-save-status {
            margin-right:8px;
        }
        #rpt-plugin-save-status {
            line-height: 30px;
        }
        @-webkit-keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% {  opacity: 0.3; }
        }
        @keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        .rpt-loading-image {
            -webkit-animation: pulse 1s infinite ease-in-out;
            -o-animation: pulse 1s infinite ease-in-out;
            -ms-animation: pulse 1s infinite ease-in-out;
            -moz-animation: pulse 1s infinite ease-in-out;
            animation: pulse 1s infinite ease-in-out;
            cursor: wait;
        }
        .column-description hr {
            visibility: hidden;
        }
        @media( max-width: 1023px) {
            .rpt-column-1,
            .rpt-column-3 {
                width: auto;
                padding: 6px 6px 6px 6px;
            }
            .rpt-rebrand-plugin-form {
                flex-direction:column;
            }
            .rpt-rebrand-plugin-form label,
            .rpt-rebrand-plugin-form .rpt-fake-label {
                display:flex;
                flex-direction:column;
            }
            .rpt-rebrand-plugin-form label.checkbox-label {
                flex-direction:row;
            }
            .rpt-field-field .button {
                vertical-align: top !important;
            }
        }
        @media(max-width: 500px) {
            #rpt-plugin-image {
                display: block;
                margin-bottom: 10px;
            }
        }
        </style>
        <?php
    }
}

// Adds scripts and a hidden html form to the bottom of the plugins page
function rpt_print_admin_scripts() {
    if ( rpt_is_current_user_super_admin() && rpt_is_plugins_page() ) {
        $ajax_nonce = wp_create_nonce( 'rpt_plugins_ajax_nonce' );
        $default_plugin_image_url = rpt_get_plugin_default_image_url();
        ?>
        <table id="rpt-rebrand-hidden-table" class="rpt-hide">
            <tr id="rpt-rebrand-plugin-form-row">
                <td id="rpt-rebrand-plugin-form-cell" colspan="10">
                    <div class="rpt-rebrand-plugin-form">
                        <div class="rpt-column-1">
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Name', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-plugin-name" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Author', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-plugin-author" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name">
                                    <?php esc_html_e( 'Description', 'rpt_rebranding' ); ?><br>
                                    <span class="rpt-field-hint"><?php esc_html_e( '(HTML allowed)', 'rpt_rebranding' ); ?></span>
                                </span>
                                <span class="rpt-field-field">
                                    <textarea id="rpt-plugin-description" autocomplete="off"></textarea>
                                </span>
                            </label>
                        </div>
                        <div class="rpt-column-3">
                            <label class="checkbox-label">
                                <input type="checkbox" <?php checked( true ); ?>
                                    autocomplete="off" id="rpt-plugin-show-author" /> <?php esc_html_e( 'Show Plugin Author', 'rpt_rebranding' ); ?>
                            </label>
                            <label>
                                <span class="rpt-field-name">
                                    <?php esc_html_e( 'Categories', 'rpt_rebranding' ); ?>
                                    <span class="rpt-field-hint"><?php esc_html_e( '(comma-separated)', 'rpt_rebranding' ); ?></span>
                                </span>
                                <span class="rpt-field-field">
                                    <input id="rpt-plugin-categories" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <label>
                                <span class="rpt-field-name"><?php esc_html_e( 'Image URL', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <input id="rpt-plugin-image-url" type="text" value="" autocomplete="off" />
                                </span>
                            </label>
                            <div class="rpt-fake-label">
                                <span class="rpt-field-name"><?php esc_html_e( 'Image', 'rpt_rebranding' ); ?></span>
                                <span class="rpt-field-field">
                                    <img id="rpt-plugin-image"
                                        src="<?php echo esc_url( $default_plugin_image_url ); ?>" alt="<?php esc_attr_e( 'Plugin Image', 'rpt_rebranding' ); ?>" />
                                </span>
                            </div>
                            <div class="rpt-fake-label">
                                <span class="rpt-field-name">&nbsp;</span>
                                <span class="rpt-field-field">
                                    <input id="rpt-plugin-save" class="button button-primary" type="button" value="<?php esc_attr_e( 'Save Plugin', 'rpt_rebranding' ); ?>" />
                                    <input id="rpt-plugin-cancel" class="button" type="button" value="<?php esc_attr_e( 'Close', 'rpt_rebranding' ); ?>" />
                                    <span id="rpt-plugin-save-status"></span>
                                    <input type="hidden" id="rpt-plugin-file" value="" />
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <script type="text/javascript">

        // Shows the box to edit the plugin branding information
        function pluginRebranding( pluginFile, pluginHash ) {
            jQuery( '#rpt-plugin-save-status' ).html( '' ).removeClass( 'rpt-green' ).removeClass( 'rpt-red' );
            jQuery( '.rpt-active-rebranding' ).removeClass( 'rpt-active-rebranding' );
            jQuery( '#rpt-rebranding-link-' + pluginHash ).closest( 'tr' ).after( jQuery( '#rpt-rebrand-plugin-form-row' ) ).addClass( 'rpt-active-rebranding' );
            jQuery( '#rpt-rebrand-plugin-form-row' ).addClass( 'rpt-active-rebranding' );
            if ( jQuery( '#rpt-rebranding-link-' + pluginHash ).closest( 'tr' ).hasClass( 'active' ) && jQuery( '.plugins .active th.check-column' ).length > 0 ) {
                jQuery( '#rpt-rebrand-plugin-form-row #rpt-rebrand-plugin-form-cell' ).css(
                    'border-left', jQuery( '.plugins .active th.check-column' ).css( 'border-left' ) );
            } else {
                jQuery( '#rpt-rebrand-plugin-form-row #rpt-rebrand-plugin-form-cell' ).css( 'border-left', 'none' );
            }
            jQuery( '#rpt-plugin-file' ).val( pluginFile );

            // Set the form with the existing data from the hidden textarea
            responseObject = jQuery.parseJSON( jQuery( '#rpt-plugin-hidden-data-' + pluginHash ).val() );
            if ( "plugin_name" in responseObject ) {
                jQuery( '#rpt-plugin-name' ).val( responseObject.plugin_name );
            } else {
                jQuery( '#rpt-plugin-name' ).val( "" );
            }
            if ( "plugin_author" in responseObject ) {
                jQuery( '#rpt-plugin-author' ).val( responseObject.plugin_author );
            } else {
                jQuery( '#rpt-plugin-author' ).val( "" );
            }
            if ( "plugin_description" in responseObject ) {
                jQuery( '#rpt-plugin-description' ).val( responseObject.plugin_description );
            } else {
                jQuery( '#rpt-plugin-description' ).val( "" );
            }
            if ( "plugin_categories" in responseObject ) {
                jQuery( '#rpt-plugin-categories' ).val( responseObject.plugin_categories );
            } else {
                jQuery( '#rpt-plugin-categories' ).val( "" );
            }
            if ( "plugin_image_url" in responseObject && '<?php echo esc_url( $default_plugin_image_url ); ?>' !== responseObject.plugin_image_url ) {
                jQuery( '#rpt-plugin-image' ).attr( 'src', responseObject.plugin_image_url );
                jQuery( '#rpt-plugin-image-url' ).val( responseObject.plugin_image_url );
            } else {
                jQuery( '#rpt-plugin-image' ).attr( 'src', '<?php echo esc_url( $default_plugin_image_url ); ?>' );
                jQuery( '#rpt-plugin-image-url' ).val( '' );
            }
            if ( "plugin_show_author" in responseObject ) {
                if ( responseObject.plugin_show_author === 'yes' ) {
                    jQuery( "#rpt-plugin-show-author" ).prop( "checked", true );
                } else {
                    jQuery( "#rpt-plugin-show-author" ).prop( "checked", false );
                }
            } else {
                jQuery( "#rpt-plugin-show-author" ).prop( "checked", true );
            }
        }

        // Checks if a string is valid JSON
        function isValidJSON( string ) {
            try {
                JSON.parse( string );
            } catch (e) {
                return false;
            }
            return true;
        }

        jQuery( function() {

            // Save the changes to the database
            jQuery( '#rpt-plugin-save' ).click( function() {

                jQuery( '#rpt-plugin-save' ).attr( 'disabled', 'disabled' );
                jQuery( '#rpt-plugin-save-status' ).html( '<?php esc_html_e( 'Loading...', 'rpt_rebranding' ); ?>' ).removeClass( 'rpt-green' ).removeClass( 'rpt-red' );

                var pluginFile = jQuery( '#rpt-plugin-file' ).val();
                var pluginName = jQuery( '#rpt-plugin-name' ).val();
                var pluginAuthor = jQuery( '#rpt-plugin-author' ).val();
                var pluginDescription = jQuery( '#rpt-plugin-description' ).val();
                var pluginCategories = jQuery( '#rpt-plugin-categories' ).val();
                var pluginImageURL = jQuery( '#rpt-plugin-image-url' ).val();
                var pluginShowAuthor = 'no';
                if ( jQuery( '#rpt-plugin-show-author' ).is( ":checked" ) ) {
                    pluginShowAuthor = 'yes';
                }

                var data = {
                    'action': 'rpt_save_plugin_action',
                    'plugin_file': pluginFile,
                    'plugin_name': pluginName,
                    'plugin_author': pluginAuthor,
                    'plugin_description': pluginDescription,
                    'plugin_categories': pluginCategories,
                    'plugin_image_url': pluginImageURL,
                    'plugin_show_author': pluginShowAuthor,
                    'security': '<?php echo esc_attr( $ajax_nonce ); ?>'
                };

                jQuery.post( ajaxurl, data, function( response ) {
                    var resultStatus = "error";
                    response = response.trim();
                    if ( 'no-access' === response ) {
                        alert( "<?php echo esc_js( 'Error: You do not have access to do this action.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'invalid-nonce' === response ) {
                        alert( "<?php echo esc_js( 'Error: Invalid security nonce. Please reload the page and try again.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'invalid-url' === response ) {
                        alert( "<?php echo esc_js( 'Error: The image URL is not valid.', 'rpt_rebranding' ); ?>" );
                    } else if ( 'missing-data' === response ) {
                        alert( "<?php echo esc_js( 'Error: The required data was not sent in the POST request.', 'rpt_rebranding' ); ?>" );
                    } else if ( '' === response || '0' === response ) {
                        alert( "<?php echo esc_js( 'Error: We got an empty response.', 'rpt_rebranding' ); ?>" );
                    } else if ( ! isValidJSON( response ) ) {
                        alert( "<?php echo esc_js( 'Error: We got an unexpected response. It is possible that the task was still performed but something is not right.',
                            'rpt_rebranding' ); ?>" );
                    } else {
                        responseObject = jQuery.parseJSON( response );
                        if ( "status" in responseObject && responseObject.status === 'done' && "textareaData" in responseObject
                            && "pluginHash" in responseObject && "pluginName" in responseObject ) {
                            jQuery( '#rpt-plugin-hidden-data-' + responseObject.pluginHash ).val( responseObject.textareaData );
                            jQuery( 'tr[data-plugin="' + pluginFile + '"] .plugin-title strong' ).html( responseObject.pluginName );
                            jQuery( '#rpt-plugin-image' ).attr( 'src', responseObject.pluginImageURL );
                            resultStatus = "success";
                        } else {
                            alert( "<?php echo esc_js( 'Error: We got an invalid response. It is possible that the task was still performed but something is not right.',
                                'rpt_rebranding' ); ?>" );
                        }
                    }
                    jQuery( '#rpt-plugin-save' ).removeAttr( 'disabled' );
                    if ( 'success' === resultStatus ) {
                        jQuery( '#rpt-plugin-save-status' ).html( "<?php esc_html_e( 'Done', 'rpt_rebranding' ); ?>" ).removeClass( 'rpt-red' ).addClass( 'rpt-green' );
                    } else {
                        jQuery( '#rpt-plugin-save-status' ).html( "<?php esc_html_e( 'Error', 'rpt_rebranding' ); ?>" ).removeClass( 'rpt-green' ).addClass( 'rpt-red' );
                    }
                });
            });

            // Hides the box to edit the plugin branding information
            jQuery( '#rpt-plugin-cancel' ).click( function() {
                jQuery( '.rpt-active-rebranding' ).removeClass( 'rpt-active-rebranding' );
                jQuery( '#rpt-rebrand-hidden-table' ).append( jQuery( '#rpt-rebrand-plugin-form-row' ) );
            });

        });
        </script>
        <?php
    }
}

// Handles the ajax request to save the rebranding plugin data
function rpt_save_plugin_callback() {
    if ( ! check_ajax_referer( 'rpt_plugins_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }
    if ( ! rpt_is_current_user_super_admin() ) {
        wp_die( 'no-access' );
    }

    if ( ! isset( $_POST['plugin_file'] ) || ! isset( $_POST['plugin_name'] ) || ! isset( $_POST['plugin_author'] ) || ! isset( $_POST['plugin_description'] )
        || ! isset( $_POST['plugin_categories'] ) || ! isset( $_POST['plugin_image_url'] )
        || ! isset( $_POST['plugin_show_author'] ) ) {
        wp_die( 'missing-data' );
    }

    if ( ! empty( $_POST['plugin_image_url'] ) ) {
        if ( rpt_is_valid_url( $_POST['plugin_image_url'] ) ) {
            $plugim_image_url = $_POST['plugin_image_url'];
        } else {
            wp_die( 'invalid-url' );
        }
    } else {
        $plugim_image_url = rpt_get_plugin_default_image_url();
    }

    $plugin_rebranding = Array(
        'plugin_file' => rpt_sanitize_plugin_file( $_POST['plugin_file'] ),
        'plugin_name' => sanitize_text_field( $_POST['plugin_name'] ),
        'plugin_author' => sanitize_text_field( $_POST['plugin_author'] ),
        'plugin_description' => wp_kses_post( stripslashes_deep( $_POST['plugin_description'] ) ),
        'plugin_categories' => sanitize_text_field( $_POST['plugin_categories'] ),
        'plugin_image_url' => esc_url_raw( $plugim_image_url ),
        'plugin_show_author' => sanitize_html_class( $_POST['plugin_show_author'] ),
    );
    $plugin_hash = md5( $plugin_rebranding['plugin_file'] );
    file_put_contents(RE_PLUGINS.$plugin_hash.'.json',json_encode($plugin_rebranding,JSON_UNESCAPED_UNICODE));
 

    $textarea_data = json_encode( $plugin_rebranding );

    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_rebranding['plugin_file'] );
    if ( is_array( $plugin_data ) && array_key_exists( "Name", $plugin_data ) ) {
        $plugin_name = $plugin_data['Name'];
        if ( ! empty( $plugin_rebranding['plugin_name'] ) ) {
            $plugin_name = esc_html( $plugin_rebranding['plugin_name'] ) . " (" . $plugin_name . ")";
        }
    } else {
        $plugin_name = esc_html__( 'Error', 'rpt_rebranding' );
    }

    wp_die( json_encode( Array(
        'status' => 'done',
        'textareaData' => $textarea_data,
        'pluginHash' => $plugin_hash,
        'pluginName' => $plugin_name,
        'pluginImageURL' => esc_url( $plugin_rebranding['plugin_image_url'] ),
    ) ) );
}

// Handles the ajax request to save the rebranding theme data
function rpt_save_theme_callback() {
    if ( ! check_ajax_referer( 'rpt_themes_ajax_nonce', 'security', false ) ) {
        wp_die( 'invalid-nonce' );
    }
    if ( ! rpt_is_current_user_super_admin() ) {
        wp_die( 'no-access' );
    }
    if ( ! isset( $_POST['theme_stylesheet'] ) || ! isset( $_POST['theme_name'] ) || ! isset( $_POST['theme_author'] ) || ! isset( $_POST['theme_description'] )
        || ! isset( $_POST['theme_categories'] ) || ! isset( $_POST['theme_image_url'] ) || ! isset( $_POST['theme_show_author'] )
        || ! isset( $_POST['theme_show_version'] ) ) {
        wp_die( 'missing-data' );
    }

    $stylesheet = sanitize_html_class( $_POST['theme_stylesheet'] );
    $theme_object = wp_get_theme( $stylesheet );

    if ( ! empty( $_POST['theme_image_url'] ) ) {
        if ( rpt_is_valid_url( $_POST['theme_image_url'] ) ) {
            $image_url = $_POST['theme_image_url'];
        } else {
            wp_die( 'invalid-url' );
        }
    } else {
        $screenshot = $theme_object->get_screenshot();
        if ( empty( $screenshot ) ) {
            $image_url = rpt_get_theme_default_image_url();
        } else {
            $image_url = $screenshot;
        }
    }

    $theme_rebranding = Array(
        'theme_stylesheet' => $stylesheet,
        'theme_name' => sanitize_text_field( $_POST['theme_name'] ),
        'theme_author' => sanitize_text_field( $_POST['theme_author'] ),
        'theme_description' => wp_kses_post( stripslashes_deep( $_POST['theme_description'] ) ),
        'theme_categories' => sanitize_text_field( $_POST['theme_categories'] ),
        'theme_image_url' => esc_url_raw( $image_url ),
        'theme_show_author' => sanitize_html_class( $_POST['theme_show_author'] ),
        'theme_show_version' => sanitize_html_class( $_POST['theme_show_version'] ),
    );

    file_put_contents(RE_THEMES . $stylesheet.'.json', json_encode($theme_rebranding,JSON_UNESCAPED_UNICODE) );

    $textarea_data = json_encode( $theme_rebranding );

    if ( is_object( $theme_object ) ) {
        $theme_name = $theme_object->Name;
        if ( ! empty( $theme_rebranding['theme_name'] ) ) {
            $theme_name = esc_html( $theme_rebranding['theme_name'] . " (" . $theme_name . ")" );
        }
        $theme_description = $theme_object->Description;
        if ( ! empty( $theme_rebranding['theme_description'] ) ) {
            $theme_description = "<b>" . esc_html__( 'Rebranded Description:', 'rpt_rebranding' ) . "</b><br>" . $theme_rebranding['theme_description']
                . "<hr><b>" . esc_html__( 'Original Description:', 'rpt_rebranding' ) . "</b><br>" . wp_kses_post( $theme_description );
        }
        $theme_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme_object->Version ) . "&nbsp;&nbsp;|&nbsp;&nbsp;"
            . esc_html__( 'Author:', 'rpt_rebranding' ) . " " . wp_kses_post( $theme_object->Author );
        if ( ! empty( $theme_rebranding['theme_author'] ) || $theme_rebranding['theme_show_author'] !== 'yes'
            || $theme_rebranding['theme_show_version'] !== 'yes' ) {
            $new_details = esc_html__( '[Hidden]', 'rpt_rebranding' );
            if ( 'yes' === $theme_rebranding['theme_show_version'] ) {
                if ( 'yes' === $theme_rebranding['theme_show_author'] ) {
                    $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme_object->Version ) . "&nbsp;&nbsp;|&nbsp;&nbsp;"
                        . esc_html__( 'Author:', 'rpt_rebranding' ) . " " . $theme_rebranding['theme_author'];
                } else {
                    $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme_object->Version );
                }
            }
            if ( 'yes' === $theme_rebranding['theme_show_author'] ) {
                if ( 'yes' === $theme_rebranding['theme_show_version'] ) {
                    $new_details = esc_html__( 'Version:', 'rpt_rebranding' ) . " " . esc_html( $theme_object->Version ) . "&nbsp;&nbsp;|&nbsp;&nbsp;"
                        . esc_html__( 'Author:', 'rpt_rebranding' ) . " " . $theme_rebranding['theme_author'];
                } else {
                    $new_details = esc_html__( 'Author:', 'rpt_rebranding' ) . " " .  wp_kses_post( $theme_object->Author );
                }
            }
            $theme_details = "<b>" . esc_html__( 'Rebranded Details:', 'rpt_rebranding' ) . "</b><br>" . $new_details
                . "<hr><b>" . esc_html__( 'Original Details:', 'rpt_rebranding' ) . "</b><br>" . wp_kses_post( $theme_details );
        }
    } else {
        $theme_name = esc_html__( 'Error', 'rpt_rebranding' );
        $theme_description = esc_html__( 'Error', 'rpt_rebranding' );
        $theme_details = esc_html__( 'Error', 'rpt_rebranding' );  
    }

    wp_die( json_encode( Array(
        'status' => 'done',
        'textareaData' => $textarea_data,
        'themeResponseStylesheet' => $stylesheet,
        'themeImageURL' => $image_url,
        'themeDescription' => $theme_description,
        'themeDetails' => $theme_details,
        'themeName' => $theme_name,
    ) ) );
}

/**
 * Adds the Rebranding action link to each plugin on the Plugins admin page for super admins and some hidden data
 * @param string $actions
 * @param string $plugin_file
 * @return string
 */
function rpt_add_edit_plugin_link( $actions, $plugin_file ) {
    if ( rpt_is_current_user_super_admin() && rpt_is_plugins_page() ) {

        // Add the link to edit branding
        $edit_branding = '<a id="rpt-rebranding-link-' . esc_attr( md5( $plugin_file ) ) . '" href="javascript:pluginRebranding(\''
            . esc_attr( $plugin_file ) . '\', \'' . esc_attr( md5( $plugin_file ) ) . '\')">' . esc_html__( 'Rebranding', 'rpt_rebranding' ) . '</a>';

        // Hidden preloaded data to for the form to edit later
        $edit_branding .= '<textarea class="rpt-hide" id="rpt-plugin-hidden-data-' . md5( $plugin_file ) . '">'
            . esc_textarea( json_encode( rpt_get_plugin(  $plugin_file ) ) ) . '</textarea>';

        $actions = rpt_add_element_to_array( $actions, 'rpt-rebranding', $edit_branding, 'non-existing' );
    }
    return $actions;
}

/**
 * Returns the default image URL for plugins
 * @return string
 */
function rpt_get_plugin_default_image_url() {
    return WPMU_PLUGIN_URL. '/rebranding/images/plugin-default.png';
}

/**
 * Returns the default image URL for themes
 * @return string
 */
function rpt_get_theme_default_image_url() {
    return WPMU_PLUGIN_URL. '/rebranding/images/theme-default.png';
}

/**
 * Changes the plugin names for super admins on the plugins page, if rebranded
 * @param array $all_plugins
 * @return array
 */
function rpt_change_plugin_name_super_admin( $all_plugins ) {
    if ( rpt_is_current_user_super_admin() && rpt_is_plugins_page() ) {
        foreach ( $all_plugins as $plugin_file => $plugin_data ) {
            $rebranded_data = rpt_get_plugin($plugin_file );
                  if ( array_key_exists( 'plugin_name', $rebranded_data ) && ! empty( $rebranded_data['plugin_name'] ) ) {
                $all_plugins[ $plugin_file ]['Name'] = esc_html( $rebranded_data['plugin_name'] ) . " (" . $all_plugins[ $plugin_file ]['Name'] . ")";
            }
          
        }
    }
    return $all_plugins;
}

/**
 * Checks if the current user is a super admin
 * @return bool
 */
function rpt_is_current_user_super_admin() {

    return $GLOBALS['current_user_super_admin'];
   
    // if ( is_user_logged_in() && current_user_can( "manage_options" ) ) {
    //     $user_id = get_current_user_id();
    //     $user = get_user_by( "id", $user_id );
    //     $username = $user->user_login;
    //     if ( in_array( $username, rpt_get_super_admin_usernames() ) ) {
    //         return true;
    //     }
    // }
    return false;
}

/**
 * Adds a new element in an array on the exact place we want (if possible).
 * We use this when adding a custom column or an action link somewhere in the admin panel.
 * @param array $original_array
 * @param string $add_element_key
 * @param mixed $add_element_value
 * @param string $add_before_key
 * @return array
 */
function rpt_add_element_to_array( $original_array, $add_element_key, $add_element_value, $add_before_key ) {
    $is_added = 0;
    $new_array = array();
    foreach( $original_array as $key => $value ) {
        if ( $key === $add_before_key ) {
      	    $new_array[ $add_element_key ] = $add_element_value;
            $is_added = 1;
        }
        $new_array[ $key ] = $value;
    }
    if ( 0 === $is_added ) {
        $new_array[ $add_element_key ] = $add_element_value;
    }
    return $new_array;
}

/**
 * Checks if the string is a valid URL
 * @param string $string
 * @return bool
 */
function rpt_is_valid_url( $url ) {
    if ( filter_var( $url, FILTER_VALIDATE_URL ) === FALSE ) {
        return false;
    }
    return true;
}

/**
 * Sanitizes a string by removing invalid characters for a plugin file identifier (folder name plus file name or only file name)
 * @param string $string
 * @return string
 */
function rpt_sanitize_plugin_file( $string ) {
    $string_no_separator = str_replace( '/', 'rpt-unique-separator', $string );
    $specials = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!',
        '{', '}', '%', '+', chr( 0 ) );
    foreach ( $specials as $special ) {
        $string_no_separator = str_replace( $special, '', $string_no_separator );
    }
    $string = str_replace( 'rpt-unique-separator', '/', $string_no_separator );
    return $string;
}

/**
 * Returns the current plugin file and folder
 * @return string
 */
function rpt_current_plugin_file() {
    return basename( plugin_dir_path( __FILE__ ) ) . '/' . basename( __FILE__ );
}

function rpt_get_plugin($name){
    $plugin_hash = md5( $name );

    return @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/rebranding/plugins/'.$plugin_hash.'.json',true),true);
}
function rpt_get_theme($name){
    return @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/rebranding/themes/'.$name.'.json',true),true);
}