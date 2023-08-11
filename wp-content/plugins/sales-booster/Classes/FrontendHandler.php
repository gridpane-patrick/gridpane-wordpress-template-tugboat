<?php

namespace SaleBooster\Classes;

class FrontendHandler
{

    public static $timerConfig = [];
    public static $inquireUsConfig = [];

    public static function initSaleBooster()
    {
        wp_enqueue_style("sale-booster-css", SALE_BOOSTER_PLUGIN_DIR_URL . "src/public/css/sale-booster.css", false);
        
        if (!is_singular('product')) {
            return;
        }

        $productId = get_the_ID();
        
        if (defined('FLUENTFORM')) {
            self::singlePageExitPopup($productId); // single page Exit popup modal
        }
        
        //Pro feature 
        if (defined('SALES_BOOTER_PRO_INSTALLED')) {
            $inquireUsConfig =  self::getEnquiryUsSettings($productId);  // inquire us
            self::$inquireUsConfig = $inquireUsConfig;
            self::inquireUsButtonPostion(); // inquire us button position set
        }
        
        $timerConfig = self::getTimerSettings($productId);
        self::$timerConfig = $timerConfig;

        if (!$timerConfig) {
            return;
        }
        wp_enqueue_script("sale-booster-timer-js", SALE_BOOSTER_PLUGIN_DIR_URL . "src/public/js/sale-booster-timer.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
        wp_localize_script('sale-booster-timer-js', 'sale_booster_countdown_vars', $timerConfig);
        add_action('wp_head', array('SaleBooster\Classes\Customization', 'customStyle')); // custom css

        $showTopBar = $timerConfig['position'] == 'both' || $timerConfig['position'] == 'top';
        $showBottomBar = $timerConfig['position'] == 'both' || $timerConfig['position'] == 'bottom';
        $showFooterSticky = $timerConfig['position'] == 'footer_sticky';

        if ($showTopBar) {
            add_action('wp_footer', array('\SaleBooster\Classes\FrontendHandler', 'discountTimerTop'));
        }
        if ($showBottomBar) {
            add_action('woocommerce_share', array('\SaleBooster\Classes\FrontendHandler', 'discountTimerBottom'));
        }

        if ($showFooterSticky) {
            add_action('wp_footer', array('\SaleBooster\Classes\FrontendHandler', 'discountTimerFooter'));
        }
    }

    public static function discountTimerBottom()
    {
        $config = self::$timerConfig;
        if (!$config) {
            return;
        }
        ?>
            <div style="display: none" class="_sale_booster_countdown_wrap _sale-booster-countdown-bottom" style="margin-top:20px">
               <?php if (defined('SALES_BOOTER_PRO_INSTALLED')): ?>
                    <p class="_sale-booster-hits"> <?php echo wp_kses_post($config['title_before']); ?></p>
               <?php endif; ?>
                <div class="_sale-booster-countdown"></div>
                <p class="_sale-booster-hits"> <?php echo wp_kses_post($config['title_after']); ?> </p>
            </div>
        <?php
    }

   /**
    * Top discount Timer
    */ 
    public static function discountTimerTop()
    {
        $className = '_sale-booster-countdown-top';
        $clockName = '_sale_top_clock';
        $config = self::$timerConfig;
        if (!$config) {
            return;
        }
        ?>
        <div style="display: none" class="_sale_booster_countdown_wrap <?php echo esc_attr($className); ?>">
            <div class="_sale-booster-countdown-row">
                <p class="countdown-top-title"> <?php echo wp_kses_post($config['title_after']); ?> </p>
                <div class="_sale-booster-countdown <?php echo esc_html($clockName); ?>"></div>
            </div>
        </div>
        <?php
    }

    /**
    * Discount Timer Footer
    */ 
    public static function discountTimerFooter()
    {
        $className = '_sale-booster-countdown-top _sale-booster-countdown-footer';
        $clockName = '_sale_footer_clock';
        $config = self::$timerConfig;
        if (!$config) {
            return;
        }
        ?>
        <div style="display: none" class="_sale_booster_countdown_wrap <?php echo esc_attr($className); ?>">
            <div class="_sale-booster-countdown-row">
                <p class="countdown-top-title"> <?php echo wp_kses_post($config['title_after']); ?> </p>
                <div class="_sale-booster-countdown <?php echo esc_html($clockName) ?>"></div>
            </div>
        </div>
        <?php
    }
   
    /*
    * Pro Specific Hooks.
    * Add inquire us button in single product page..
    */
    public static function inquireUsButtonPostion()
    {   
        $inquireUsConfig = self::$inquireUsConfig;
      
        if (!$inquireUsConfig){
            return;
        }

        $inquire_us         = $inquireUsConfig['inquire_us'];
        $inquire_us_button  = $inquireUsConfig['inquire_us_button'];
        $remove_cart_button = $inquireUsConfig['remove_cart_button'];

        $below_title         = $inquire_us_button == "below_title";
        $below_description   = $inquire_us_button == "below_description";
        $next_to_cart_button = $inquire_us_button == "next_to_cart_button";
      
        if ($inquire_us != 'yes' || $inquire_us_button == ''){
            return;
        }

        if ($below_title){ // below title
            add_action('woocommerce_single_product_summary', array('\SaleBooster\Classes\FrontendHandler', 'addSingleCustomButton'), 15);
        } else if ($next_to_cart_button && $remove_cart_button != 'yes' ){  // next to cart button 
            add_action('woocommerce_after_add_to_cart_button', array('\SaleBooster\Classes\FrontendHandler', 'addSingleCustomButton'));
        } else { //below_description or next_to_cart_button (when add to cart button remove)
            add_action('woocommerce_single_product_summary', array('\SaleBooster\Classes\FrontendHandler', 'addSingleCustomButton'), 30);
        }
       
    }
    /**
     *  remove single cart button
    */
    public static function removeSingleCartButton()
    {
        $productId = get_the_ID();
        $remove_cart_button = get_post_meta($productId, '_sale_booster_remove_cart_button', true);
        
        if ($remove_cart_button == 'yes' ) {
            if (is_product()) {
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            }
        }
    }
    /**
     *  Added custom button
     */
    public static function addSingleCustomButton()
    {
        $productId = get_the_ID();
        
        $inquireUsConfig = self::$inquireUsConfig;
        
        if (!$inquireUsConfig) {
            return;
        }

        $inquire_us             = $inquireUsConfig['inquire_us'];
        $cart_Button_text       = $inquireUsConfig['cart_Button_text'];
        $inquire_us_button      = $inquireUsConfig['inquire_us_button'];
        $inquire_us_form        = $inquireUsConfig['inquire_us_form'];
        $inquire_us_custom_html = $inquireUsConfig['inquire_us_custom_html'];
        
        $below_title = $inquire_us_button         == "below_title";
        $below_description = $inquire_us_button   == "below_description";
        $next_to_cart_button = $inquire_us_button == "next_to_cart_button";
       
        if ($inquire_us == 'yes' && !defined('SALES_BOOTER_PRO_INSTALLED')) {
            return;
        }
        // class add
        if ($below_title) {
            $inquire_us_button_class = '_sale_booster_below_title';
        }   
        if ($below_description) {
            $inquire_us_button_class = '_sale_booster_below_description';
        }
        if ($next_to_cart_button) {
            $inquire_us_button_class = '_sale_booster_next_to_cart_button';
        }

        if ($inquire_us == 'yes') {
            echo wp_kses_post("<button type='button' id='_sale_booster_inquire_us_btn' class='single_add_to_cart_button button alt ".$inquire_us_button_class."'>" . $cart_Button_text . "</button>");
        }

        /**
         * Enquiry us modal 
         */
        if ( !defined('FLUENTFORM')) {
            return;
        }

        $inquireShortCode = "";
        if ($inquire_us_form == 'custom_html'){
            $inquireShortCode =  $inquire_us_custom_html;
        } else {
            $inquireShortCode =  $inquire_us_form;
        }

        if (!$inquireShortCode){
            return;
        }
        self::inquireUsModal($inquireShortCode);
     ?>
            
        <?php
    }

    /**
     * Enquiry us modal 
     */

     public static function inquireUsModal($inquireShortCode)
     {          
        wp_enqueue_script("sale-booster-inquireus-js", SALE_BOOSTER_PLUGIN_DIR_URL . "src/public/js/sale-booster-inquireus.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
        ?>
            <div id="_sale_booster_inquire_us_modal">
                <div class="_sale_booster_inquire_us_modal_content">
                    <div class="_sale_booster_inquire_us_close">&times;</div><br/><br/>
                    <div>  <?php echo do_shortcode($inquireShortCode); ?>  </div>
                </div>
            </div>  
       <?php 
     }

    /**
     * cart button change on Shop page
     */
    public static function removeShopCartButton($button, $product)
    {   
        if (is_shop() || is_product_category()) {
            $productId = get_the_ID();
            $remove_cart_button = get_post_meta($productId, '_sale_booster_remove_cart_button', true);
            
            if ($remove_cart_button == 'yes') {
                $button = "";
            }
        }
        return $button;
    }
    /**
     * custom cart button text on Shop page
     */
    public static function customTextAddToCartShop($text)
    { 
        if ( is_shop() || is_product_category() ) {
            global $product;
            $productId = get_the_ID();
            $customText = get_post_meta($productId, '_sale_booster_cart_button_text', true);
            if ($customText){
                $text = $customText;
                return $text;
            } 
        }
        return $text;
    }
    /**
     * custom cart button text on single page
     */
    public static function customTextAddToCartSingle($text)
    {
        global $product;
        if (!is_singular('product')) {
            return;
        }
        $productId = get_the_ID();
        $customText = get_post_meta($productId, '_sale_booster_cart_button_text', true);
        if ($customText){
            $text = $customText;
            return $text;
        } 
       return $text;
    }
    /**
     * hide price
     */
    public static function hideSinglePrice()
    {
        $productId = get_the_ID();
        $hidePrice = get_post_meta($productId, '_sale_booster_hide_price', true);
        
        if ($hidePrice == "yes") {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        }
    }
    /**
     * Shop Price Hide
     */
    public static function hideShopPrice($price, $product)
    {
        $productId = get_the_ID();
        
        if (is_shop() || is_product_category()) {
            $hidePrice = get_post_meta($productId, '_sale_booster_hide_price', true);
            if ($hidePrice == 'yes') {
                return '';
            }
        }

        return $price;
    }

    public static function hasCountDownTimerType($productId)
    {
        $type = get_post_meta($productId, '_sale_booster_discount_timer', true);
        
        if (!$type || $type == 'none') {
            return false;
        }

        if ($type == 'fixed_date') {
            $date = get_post_meta($productId, '_sale_booster_expire_date_time', true);
            if (strtotime($date) < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     *  Exit Popup modal in single page
     */
    public static function singlePageExitPopup($productId)
    {      
        if (!is_singular('product')) {
            return;
        }

        $exitPopup  = get_post_meta($productId, '_sale_booster_exit_popup', true);
        $customHTML = get_post_meta($productId, '_sale_booster_exit_custom_html', true);

        $exitShortCode = "";
        
        if ($exitPopup == 'custom_html') {
            $exitShortCode =  $customHTML;
        } else {
            $exitShortCode =  $exitPopup;
        }

        if (!$exitShortCode) {
            return;
        }

        $exitPopupVar = [ //localize data
            'productId' => $productId
        ];

        wp_enqueue_script("sale-booster-exit-popup-js", SALE_BOOSTER_PLUGIN_DIR_URL . "src/public/js/sale-booster-exit-popup.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
        wp_localize_script("sale-booster-exit-popup-js", 'sale_booster_exit_popup_vars', $exitPopupVar);
    ?>
            <div id="_sale_booster_popup" class="_sale_booster_popup__wrapper" style="display:none">
                <div class="_sale_booster_popup__container">
                    <div id="_sale_booster_popup_close" >&times;</div> <br/> <br/> 
                    <?php echo do_shortcode($exitShortCode);?>
                </div>
            </div>
    <?php
    }

    /**
     *  get Timer Settings
     */
    public static function getTimerSettings($productId = false)
    {
        if (!is_singular('product')) {
            return;
        }

        if (!$productId) {
            $productId = get_the_ID();
        }

        $type = get_post_meta($productId, '_sale_booster_discount_timer', true);
        $timerSeconds = false;

        if ($type == 'user_based_time') {

            if (!defined('SALES_BOOTER_PRO_INSTALLED')) {
                return false;
            }

            $timerMinites = intval(get_post_meta($productId, '_sale_booster_user_based_expire_time', true));
            $timerSeconds = $timerMinites * 60;
        } else if ($type == 'fixed_date') {
            $dateTime = get_post_meta($productId, '_sale_booster_expire_date_time', true);
            $timerSeconds = strtotime($dateTime) - time();
        }

        if (!$timerSeconds || $timerSeconds <= 0) {
            return false;
        }

        $titleBefore = get_post_meta($productId, '_sale_booster_stock_quantity', true);
        $titleAfter = get_post_meta($productId, '_sale_booster_note', true);
        
        if (strpos($titleBefore.$titleAfter, "{stock}") !== false) {
            $product = new \WC_Product($productId);
            $stock  = $product->get_stock_quantity();
            $titleBefore = str_replace('{stock}', $stock, $titleBefore);
            $titleAfter = str_replace('{stock}', $stock, $titleAfter);
        }

        $timerConfig = [
            'type'         => $type,
            'timerSeconds' => $timerSeconds,
            'title_before' => $titleBefore,
            'title_after'  => $titleAfter,
            'hide_days'    => get_post_meta($productId, '_sale_booster_hide_days', true),
            'position'     => get_post_meta($productId, '_sale_booster_expaire_date_layout', true)
        ];

        $timerConfig['trans'] = apply_filters('sales_booster_timer_trans', [
            'days' => __('days', 'sale_booster'),
            'hours'  => __('hours', 'sale_booster'),
            'minutes' => __('minutes', 'sale_booster'),
            'seconds' => __('seconds', 'sale_booster')
        ], $productId);
        $timerConfig['product_id'] = $productId;

        return apply_filters('sales_booster_timer_config', $timerConfig, $productId);
    }

    /**
     * get Enquiry Us Settings
    */ 
    public static function getEnquiryUsSettings($productId = false){
       
        if (!is_singular('product')) {
            return;
        }

        if (!$productId) {
            $productId = get_the_ID();
        }
        
        $inquire_us             = get_post_meta($productId, '_sale_booster_inquire_us', true);
        $inquire_us_button      = get_post_meta($productId, '_sale_booster_inquire_us_button', true);
        $remove_cart_button     = get_post_meta($productId, '_sale_booster_remove_cart_button', true);
        $cart_Button_text       = get_post_meta($productId, '_sale_booster_inquire_text', true);
        $inquire_us_form        = get_post_meta($productId, '_sale_booster_inquire_us_form', true);
        $inquire_us_custom_html = get_post_meta($productId, '_sale_booster_inquire_us_custom_html', true);
        
        $inquireUsConfig = [
            'inquire_us'             => $inquire_us,
            'inquire_us_button'      => $inquire_us_button,
            'remove_cart_button'     => $remove_cart_button,
            'cart_Button_text'       => $cart_Button_text,
            'inquire_us_form'        => $inquire_us_form,
            'inquire_us_custom_html' => $inquire_us_custom_html,
        ];

        $inquireUsConfig['product_id'] = $productId;

        return apply_filters('sales_booster_inquireus_config', $inquireUsConfig, $productId);
    }



    public static function shopPageExitPopup()
    {      
        if ( !is_shop() || !defined('FLUENTFORM') ){
            return;
        }
        
        $exitPopup  = get_option('home_page_exit_popup'); 
        $customHTML = get_option('home_page_exit_custom_popup');

        $exitShortCode = "";
        
        if ($exitPopup == 'custom_html'){
            $exitShortCode = $customHTML;
        } else {
            $exitShortCode =  $exitPopup;
        }

        if (!$exitShortCode){
            return;
        }

        wp_enqueue_script("sale-booster-shop-exitpopup-js", SALE_BOOSTER_PLUGIN_DIR_URL . "src/public/js/sale-booster-shop-exit-popup.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);

        ?>
            <div id="_sale_booster_popup" class="_sale_booster_popup__wrapper" style="display:none">
                <div class="_sale_booster_popup__container">
                    <div id="_sale_booster_popup_close" >&times;</div> <br/> <br/> 
                     <?php echo do_shortcode($exitShortCode);?>
                </div>
            </div>
        <?php
    }


    /**
     *  Top Banner on Shop page
    */
    public static function shopPageBannerTop()
    {
        $homePageBanner = get_option("home_page_banner_below_select");

        if ($homePageBanner == 'shop_page'){
            if (!is_shop()){
                return;
            }
        } else if ($homePageBanner == 'category_pages') {
            if (!is_product_category()) {
                return;
            }
        } else {
            return;
        }

        $topBanner = get_option("home_page_banner_below");
        $topBannerLink = get_option("home_page_banner_below_link");
        
        if (!$topBanner) {
            return;
        }

        ?>
             <div class="sale_booster_banner_image_top" style="margin-bottom:10px;"> 
                <a href="<?php echo esc_url($topBannerLink); ?>" target="_blank"> 
                    <img src="<?php echo esc_url($topBanner); ?>"/> 
                </a>
             </div>
         <?php
    }


    /**
     *  Bottom Banner in shop page
    */
    public static function shopPageBannerBottom()
    {   
        $homePageBanner = get_option("home_page_banner_above_footer_select");

        if ($homePageBanner == 'shop_page'){
            if (!is_shop()){
                return;
            }
        } else if($homePageBanner == 'category_pages') {
            if (!is_product_category()) {
                return;
            }
        } else {
           return;
        }

        $aboveFooter = get_option("home_page_banner_above_footer");
        $aboveFooterLink = get_option("home_page_banner_above_footer_link");
        
        if (!$aboveFooter) {
            return;
        }

        ?>
            <div class="sale_booster_banner_image_footer" style="margin-bottom:10px;"> 
                <a href="<?php echo esc_url($aboveFooterLink); ?>" target="_blank"> 
                    <img src="<?php echo esc_url($aboveFooter); ?>"/> 
                </a>
            </div>
        <?php
    }
   
    /**
     * Corner Ad on shop page
    */
    public static function shopPageCornerAd()
    {    
        $corner_page = get_option("corner_page_select");

        if ($corner_page == 'shop_page') {
            if (!is_shop()) {
                return;
            }
        } else if ($corner_page == 'category_pages') {
            if (!is_product_category()) {
                return;
            }
        } else {
            return;
        }
       
        $cornerAd = get_option("home_page_corner_ad");
        $cornerAdLink = get_option("home_page_corner_ad_link");
        $cornerAdClass = get_option("home_corner_ad_position");
       
        if (!$cornerAd) {
            return;
        }

        ?>
            <div class="sale_booster_corner_ad_<?php echo esc_attr($cornerAdClass);?>"> 
                <a href="<?php echo esc_url($cornerAdLink); ?>" target="_blank"> 
                    <img src="<?php echo esc_url($cornerAd); ?>"/> 
                </a>
            </div>
            
            <style>
                .sale_booster_corner_ad_bottom_left{
                    position: fixed;
                    left: 0;
                    bottom: 0;
                    z-index: 20000022;
                }
                .sale_booster_corner_ad_bottom_right{
                    position: fixed;
                    right: 0;
                    bottom: 0;
                    z-index: 20000022;
                }
                .sale_booster_corner_ad_top_left{
                    position: fixed;
                    top: 0;
                    left: 0;
                    z-index: 99999;
                }
                .sale_booster_corner_ad_top_right{
                    position: fixed;
                    right: 0;
                    top: 0;
                    z-index: 99999;
                }
            </style>
        <?php
    }
}