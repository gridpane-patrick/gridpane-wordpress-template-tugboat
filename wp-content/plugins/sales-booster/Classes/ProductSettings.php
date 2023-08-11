<?php

namespace SaleBooster\Classes;

class ProductSettings
{
    
    // register Tab
    public static function registerProductDataTab($product_data_tabs)
    {
        self::adminEnqueueScripts();

        $menuName = esc_html__('Sales Booster Lite', 'sale_booster');
        if (defined('SALES_BOOTER_PRO_INSTALLED')) {
            $menuName = esc_html__('Sales Booster Pro', 'sale_booster');
        }

        $product_data_tabs['_sale_booster'] = array(
            'label'  => $menuName,
            'target' => 'sale_booster_product_data',
            'class'  => array('show_if_sale_booster_product_data'),
        );
        return $product_data_tabs;
    }

    // admin enqueue script
    public static function adminEnqueueScripts()
    {
        wp_enqueue_style("admin-sale-booster-datepicker", SALE_BOOSTER_PLUGIN_DIR_URL . "src/admin/css/admin-sale-booster-datepicker.css", false);
        wp_enqueue_style("admin-sale-booster", SALE_BOOSTER_PLUGIN_DIR_URL . "src/admin/css/admin-sale-booster.css", false);
        wp_enqueue_script("admin-sale-booster-datepicker", SALE_BOOSTER_PLUGIN_DIR_URL . "src/admin/js/admin-sale-booster-datepicker.full.min.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
        wp_enqueue_script("admin-sale-booster", SALE_BOOSTER_PLUGIN_DIR_URL . "src/admin/js/admin-sale-booster.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
    }

    // create Data Fields
    public static function createDataFields()
    {
        global $post;
        $discount_timer_selected = get_post_meta($post->ID, '_sale_booster_discount_timer', true);
        wp_localize_script('admin-sale-booster', 'sale_booster_discount_timer_vars', array(
            'discount_timer' => $discount_timer_selected,
        ));
    ?>
        <div id="sale_booster_product_data" class="panel woocommerce_options_panel">
            <input type="hidden" name="_sales_booster_have_values" value="1"/>
            <div class="options_group">
                <?php
                    // hidden input
                    woocommerce_wp_hidden_input([
                        'id'    => '_sales_booster_have_values',
                        'value' => 1
                    ]);
                    
                    woocommerce_wp_checkbox(
                        array(
                            'id'          => '_sale_booster_remove_cart_button',
                            'label'       => __('Cart Button', 'sale_booster'),
                            'description' => __('Remove', 'sale_booster')
                        )
                    );
                    //cart button text replace
                    woocommerce_wp_text_input(
                        array(
                            'id'          => '_sale_booster_cart_button_text',
                            'label'       => __('Cart Button Text', 'sale_booster'),
                            'description' => __('Replace Cart Button Text', 'sale_booster'),
                            'desc_tip'    => 'true'
                        )
                    );
                
                ?>

                <?php  if (!defined('FLUENTFORM')): ?>
                        <p class="form-field _sale_booster_exit_popup_notice">
                            <label for="_sale_booster_exit_popup_notice">Exit Popup</label>
                            <span class="description">
                                <?php echo self::sales_get_fluentFormInstallUrl(); ?>
                            </span>
                        </p>
                <?php else :
                        woocommerce_wp_select(
                            array(
                                'id'       => '_sale_booster_exit_popup',
                                'label'    => __('Exit Popup', 'sale_booster'),
                                'selected' => true,
                                'options'  => self::getFluentFormsOptions(),
                            )
                        );
                    endif;
                ?>

                    <div id="_sale_booster_exit_custom_html_text_box" style="display:none"> 
                        <?php
                            woocommerce_wp_textarea_input(
                                array(
                                    'id'          => '_sale_booster_exit_custom_html',
                                    'label'       => __('Custom HTML', 'sale_booster'),
                                    'placeholder' => __('Custom HTML.', 'sale_booster'),
                                    'desc_tip'    => 'true',
                                    'description' => __('Custom HTML', 'sale_booster'),
                                    'row'         => 8,
                                    'style'       => 'height:200px'
                                )
                            );
                        ?> 
                    </div>
                
                <?php if (defined('SALES_BOOTER_PRO_INSTALLED')) : 
                    woocommerce_wp_checkbox(
                        array(
                            'id'          => '_sale_booster_inquire_us',
                            'label'       => __('Enquiry Us', 'sale_booster'),
                            'description' => __('Enable', 'sale_booster')
                        )
                    );     
                ?>  
                    <div id="_sale_booster_inquire" style="display:none"> 
                        <?php
                            woocommerce_wp_text_input(
                                array(
                                    'id'          => '_sale_booster_inquire_text',
                                    'label'       => __('Enquiry Us Text', 'sale_booster'),
                                    'placeholder' => __('Enquiry Us', 'sale_booster'),
                                    'desc_tip'    => 'true',
                                    'description' => __('Enquiry Us Text.', 'sale_booster')
                                )
                            );

                            woocommerce_wp_radio(
                                array(
                                    'label'   => __('Enquiry Us Button', 'sale_booster'),
                                    'id'      => '_sale_booster_inquire_us_button',
                                    'options' => array(
                                        'below_title'           => __("Below Title", 'sale_booster'),
                                        'below_description'     => __("Below Description", 'sale_booster'),
                                        'next_to_cart_button'   => __("Next To Cart Button", 'sale_booster'),
                                    ),
                                )
                            );
                            
                        if ( !defined('FLUENTFORM') ) :
                        ?>
                            <p class="form-field _sale_booster_inquire_us_pro_form">
                                <label for="_sale_booster_inquire_us_pro_form">Enquiry Us Form</label>
                                <span class="description">
                                 <?php echo self::sales_get_fluentFormInstallUrl(); ?>
                                </span>
                            </p>
                        <?php else : 
                            woocommerce_wp_select(
                                array(
                                    'id'       => '_sale_booster_inquire_us_form',
                                    'label'    => __('Enquiry Us Form', 'sale_booster'),
                                    'selected' => true,
                                    'options'  => self::getFluentFormsOptions()
                                )
                            );
                        ?>
                            <div id="_sale_booster_inquire_us_custom_html_text_box" style="display:none"> 
                                <?php
                                    woocommerce_wp_textarea_input(
                                        array(
                                            'id'          => '_sale_booster_inquire_us_custom_html',
                                            'label'       => __('Custom HTML', 'sale_booster'),
                                            'placeholder' => __('Custom HTML.', 'sale_booster'),
                                            'desc_tip'    => 'true',
                                            'description' => __('Custom HTML', 'sale_booster'),
                                            'row'         => 8,
                                            'style'       => 'height:200px'
                                        )
                                    );
                                ?> 
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>

                    <p class="form-field _sale_booster_inquire_us_pro">
                        <label for="_sale_booster_inquire_us_pro">Enquiry Us (Pro Only)</label>
                        <span class="description">This field is only available on Pro version. 
                            <a target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>">
                                Purchase Sales Booster Pro
                            </a>
                        </span>
                    </p>

                    <div style="padding: 10px 20px" class="form-field">
                        <h3>Available on pro</h3>
                        <p>Showing inquire us button and contact form is a pro feature. Please upgrade to pro.
                            <a target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>">
                            Purchase Sales Booster Pro
                            </a>
                        </p>
                    </div>
                <?php endif; ?>

                <?php // Hide Price
                    woocommerce_wp_checkbox(
                        array(
                            'id'          => '_sale_booster_hide_price',
                            'label'       => __('Hide Price', 'sale_booster'),
                            'description' => __('Check me to hide price', 'sale_booster')
                        )
                    );
                ?>
            </div>
            
            <div class="options_group">
                <?php
                woocommerce_wp_radio(
                    array(
                        'label'   => __('Discount Timer', 'sale_booster'),
                        'id'      => '_sale_booster_discount_timer',
                        'options' => array(
                            'none'            => __("None", 'sale_booster'),
                            'fixed_date'      => __("Fixed Date", 'sale_booster'),
                            'user_based_time' => __("User Based Timer", 'sale_booster')
                        ),
                    )
                );
                ?>
                <div id="_sale_booster_discounttimer_showhide">
                    <?php
                    if (defined('SALES_BOOTER_PRO_INSTALLED')) :
                        woocommerce_wp_text_input(
                            array(
                                'id'          => '_sale_booster_stock_quantity',
                                'label'       => __('Text Above Timer', 'sale_booster'),
                                'placeholder' => __('Hurry up! just {stock} items left in stock', 'sale_booster'),
                                'description' => __('Use {stock} placeholder to show current stock', 'sale_booster')
                            )
                        ); else:
                        ?>
                        <p class="form-field _sale_booster_stock_quantity_field">
                            <label for="_sale_booster_stock_quantity">Text Above Timer (Pro Only)</label>
                            <span class="description">This field is only available on Pro version. 
                                <a target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>">
                                    Purchase Sales Booster Pro
                                </a>
                            </span>
                        </p>
                    <?php
                    endif;

                    woocommerce_wp_textarea_input(
                        array(
                            'id'          => '_sale_booster_note',
                            'label'       => __('Text Below Timer', 'sale_booster'),
                            'placeholder' => __('Prices Go Up When The Timer Hits Zero.', 'sale_booster'),
                            'desc_tip'    => 'true',
                            'description' => __('Ex: Prices Go Up When The Timer Hits Zero.', 'sale_booster')
                        )
                    );
                    ?>
                    <div id="_sale_booster_fixed_date">
                        <?php
                        woocommerce_wp_text_input(
                            array(
                                'id'          => '_sale_booster_expire_date_time',
                                'label'       => __('Timer Expire Date', 'sale_booster'),
                                'description' => __('ex: (yyyy/mm/dd 00:00:00) UTC time', 'sale_booster')
                            )
                        );
                        ?>
                    </div>
                    <div id="_sale_booster_user_based_timer">
                        <?php
                        // user Based timer
                        woocommerce_wp_text_input(
                            array(
                                'id'                => '_sale_booster_user_based_expire_time',
                                'label'             => __('Timer Expire Time', 'sale_booster'),
                                'description'       => __('ex: 120 Minutes ( Minutes must be set within one day! )', 'sale_booster'),
                                'type'              => 'number',
                                'custom_attributes' => array(
                                    'step' => 'any',
                                    'min'  => '0'
                                )
                            )
                        );
                        ?>
                    </div>
                    <?php
                    woocommerce_wp_radio(
                        array(
                            'label'   => __('Expaire Date Layout', 'sale_booster'),
                            'id'      => '_sale_booster_expaire_date_layout',
                            'options' => array(
                                'top'           => __("Top Sticky", 'sale_booster'),
                                'bottom'        => __("Bellow Purchase Button", 'sale_booster'),
                                'footer_sticky' => __("Footer Sticky", 'sale_booster'),
                                'both'          => __("Header and Bellow Purchase", 'sale_booster'),
                            ),
                        )
                    );

                    woocommerce_wp_radio(
                        array(
                            'label'   => __('Pin This Offer', 'sale-booster'),
                            'id'      => '_sale_booster_offer_pin',
                            'options' => array(
                                'none'                 => __("None", 'sale-booster'),
                                'pin_on_shop_page'     => __("Pin On Shop Page ", 'sale-booster'),
                                'pin_on_catagory_page' => __("Pin On Catagory Page", 'sale-booster'),
                                'both'                 => __("Pin On All Pages", 'sale-booster'),
                            )
                        )
                    );
                    ?>
                    <div class="sale-booster-pro-notice">
                        <span class="description">This field is only available on Pro version. 
                            <a target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>">
                                Purchase Sales Booster Pro
                            </a>
                        </span>
                    </div>
                </div>
                <?php if (!defined('SALES_BOOTER_PRO_INSTALLED')) : ?>
                <div style="padding: 0px 20px; display: none;" id="sales_booster_user_time_promo">
                    <h4 style="margin: 0">User Based Timer</h4>
                    <p>
                        Show custom timer from on user lands on your product page. For Example: You can set time to 20 minites then user will see timer from 20 minites. It's a great way to <b>boost you conversion rate</b> <a target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>">Purchase Sales Booster Pro</a> and increase your conversion rate.
                    </p>
                    <a  target="_blank" rel="noopener nofollow" href="<?php echo SALES_BOOSTER_PRO_URL; ?>" class="button button-primary button-large">Purchase Pro and Increase Your conversion Rate</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * save Data Fields
    */ 
    public static function saveDataFields($post_id)
    {
        if (!isset($_REQUEST['_sales_booster_have_values'])) {
            return;
        }

        // Save Remove cart button
        if (isset($_REQUEST['_sale_booster_remove_cart_button'])) {
            update_post_meta($post_id, '_sale_booster_remove_cart_button', 'yes');
        } else {
            update_post_meta($post_id, '_sale_booster_remove_cart_button', 'no');
        }
        //cart button text
        if (isset($_REQUEST['_sale_booster_cart_button_text'])) {
            update_post_meta($post_id, '_sale_booster_cart_button_text', sanitize_text_field($_REQUEST['_sale_booster_cart_button_text']));
        }
        // exit popup
        if (isset($_REQUEST['_sale_booster_exit_popup'])) {
            update_post_meta($post_id, '_sale_booster_exit_popup', sanitize_text_field($_REQUEST['_sale_booster_exit_popup']));
        }
        // exit custom HTML
        if (isset($_REQUEST['_sale_booster_exit_custom_html'])) {
            update_post_meta($post_id, '_sale_booster_exit_custom_html', wp_kses_post($_REQUEST['_sale_booster_exit_custom_html']));
        }

        if (defined('SALES_BOOTER_PRO_INSTALLED')) { 
            //Enquiry Us Enable
            if (isset($_REQUEST['_sale_booster_inquire_us'])) {
                update_post_meta($post_id, '_sale_booster_inquire_us', 'yes');
            } else {
                update_post_meta($post_id, '_sale_booster_inquire_us', 'no');
            }
            //inquire Text Field
            if (!add_post_meta($post_id, '_sale_booster_inquire_text', 'Buy Now', true)) {
                update_post_meta($post_id, '_sale_booster_inquire_text', sanitize_text_field($_REQUEST['_sale_booster_inquire_text']));
            }
            //inquire button
            if (isset($_REQUEST['_sale_booster_inquire_us_button'])) {
                update_post_meta($post_id, '_sale_booster_inquire_us_button', sanitize_text_field($_REQUEST['_sale_booster_inquire_us_button']));
            }
            //inquire us form 
            if (isset($_REQUEST['_sale_booster_inquire_us_form'])) {
                update_post_meta($post_id, '_sale_booster_inquire_us_form', sanitize_text_field($_REQUEST['_sale_booster_inquire_us_form']));
            }
            //inquire us custom HTML
            if (isset($_REQUEST['_sale_booster_inquire_us_custom_html'])) {
                update_post_meta($post_id, '_sale_booster_inquire_us_custom_html', wp_kses_post($_REQUEST['_sale_booster_inquire_us_custom_html']));
            }
            // save User Based expire time
            if (isset($_REQUEST['_sale_booster_user_based_expire_time'])) {
                update_post_meta($post_id, '_sale_booster_user_based_expire_time', sanitize_text_field($_REQUEST['_sale_booster_user_based_expire_time']));
            }
        }
        
        // Save hide price
        if (isset($_REQUEST['_sale_booster_hide_price'])) {
            update_post_meta($post_id, '_sale_booster_hide_price', 'yes');
        } else {
            update_post_meta($post_id, '_sale_booster_hide_price', 'no');
        }
        // save discount Timer 
        if (isset($_REQUEST['_sale_booster_discount_timer'])) {
            update_post_meta($post_id, '_sale_booster_discount_timer', sanitize_text_field($_REQUEST['_sale_booster_discount_timer']));
        }
        // stock quantity status
        if (isset($_REQUEST['_sale_booster_stock_quantity'])) {
            update_post_meta($post_id, '_sale_booster_stock_quantity', wp_kses_post($_REQUEST['_sale_booster_stock_quantity']));
        }
        //   note
        if (isset($_REQUEST['_sale_booster_note'])) {
            update_post_meta($post_id, '_sale_booster_note', wp_kses_post($_REQUEST['_sale_booster_note']));
        }
        // Save expire date
        if (isset($_REQUEST['_sale_booster_expire_date_time'])) {
            update_post_meta($post_id, '_sale_booster_expire_date_time', sanitize_text_field($_REQUEST['_sale_booster_expire_date_time']));
        }
        // save expire layout
        if (isset($_REQUEST['_sale_booster_expaire_date_layout'])) {
            update_post_meta($post_id, '_sale_booster_expaire_date_layout', sanitize_text_field($_REQUEST['_sale_booster_expaire_date_layout']));
        }
    }

    /**
     * query FluentForms 
    */
    public static function sales_get_fluentforms()
    {
        if( !defined('FLUENTFORM') ) {
            return [];
        }

        return wpFluent()->table('fluentform_forms')
                    ->select(['id', 'title'])
                    ->orderBy('id', 'DESC')
                    ->get();
    }

    /**
     *  FluentForms select Options
    */
    public static function getFluentFormsOptions()
    {
        if ( !defined('FLUENTFORM') ) {
            return [];
        }
       // get fluent form 
       $fluentForms = self::sales_get_fluentforms();
       
       //select dynamic options 
       $options = array( '' => __("Choose Fluent Form", 'sale_booster'));
       foreach($fluentForms as $fluentForm ){
           $options[ '[fluentform id="'.$fluentForm->id.'"]' ] = $fluentForm->title;
       }
       $options['custom_html'] = __("Custom HTML", 'sale_booster');

       return $options;
    }

    /**
     * FluentForms Download or Activate message...
    */
    public static function sales_get_fluentFormInstallUrl()
    {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $allPlugins = get_plugins();
         
        if (!isset($allPlugins['fluentform/fluentform.php'])) {
            $fluentFormLink = wp_nonce_url(
                self_admin_url('update.php?action=install-plugin&plugin=fluentform'),
                'install-plugin_fluentform'
             );
            return "<span> Please install Fluent form </span>
                    <a target='_blank' rel='noopener nofollow' href=".$fluentFormLink.">
                        Download Fluent Form 
                    </a>";
        } 
        return "<span> Please Activate Fluent Form </span>
                <a target='_blank' rel='noopener nofollow' href=".admin_url('plugins.php?plugin_status=all&paged=1&s').">
                     Activate fluent form 
                </a>";
    }
}

