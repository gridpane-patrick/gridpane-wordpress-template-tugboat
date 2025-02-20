<?php
/**
 * WCFM plugin core
 *
 * Plugin WCFMu Preferences Controller
 *
 * @author  WC Lovers
 * @package wcfmu/core
 * @version 3.2.10
 */

class WCFMu_Preferences
{

    private $wcfm_module_options = [];


    public function __construct()
    {
        global $WCFM, $WCFMu;

        $wcfm_options              = (array) get_option('wcfm_options');
        $this->wcfm_module_options = isset($wcfm_options['module_options']) ? $wcfm_options['module_options'] : [];
        $this->wcfm_module_options = apply_filters('wcfm_module_options', $this->wcfm_module_options);

        add_filter('wcfm_is_pref_products_import', [ &$this, 'wcfmpref_products_import' ], 750);

        add_filter('wcfm_is_pref_bulk_stock_manager', [ &$this, 'wcfmpref_bulk_stock_manager' ], 750);

        add_filter('wcfm_is_pref_vendor_reviews', [ &$this, 'wcfmpref_vendor_reviews' ], 750);

        add_filter('wcfm_is_pref_vendor_followers', [ &$this, 'wcfmpref_vendor_followers' ], 750);

        add_filter('wcfm_is_pref_chatbox', [ &$this, 'wcfmpref_chatbox' ], 750);

        add_filter('wcfm_is_pref_support', [ &$this, 'wcfmpref_support' ], 750);

        add_filter('wcfm_is_pref_shipment_tracking', [ &$this, 'wcfmpref_shipment_tracking' ], 750);

        add_filter('wcfm_is_pref_vendor_invoice', [ &$this, 'wcfmpref_vendor_invoice' ], 750);

        add_filter('wcfm_is_pref_vendor_badges', [ &$this, 'wcfmpref_vendor_badges' ], 750);

        add_filter('wcfm_is_pref_vendor_verification', [ &$this, 'wcfmpref_vendor_verification' ], 750);

        add_filter('wcfm_is_pref_vendor_vacation', [ &$this, 'wcfmpref_vendor_vacation' ], 750);

        add_filter('wcfm_is_pref_shipstation', [ &$this, 'wcfmpref_shipstation' ], 750);

        add_filter('wcfm_is_pref_facebook_marketplace', [ &$this, 'wcfmpref_facebook_marketplace' ], 750);

    }//end __construct()


    // Products Import
    function wcfmpref_products_import($is_pref)
    {
        $product_import = ( isset($this->wcfm_module_options['product_import']) ) ? $this->wcfm_module_options['product_import'] : 'no';
        if ($product_import == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_products_import()


    // Bulk Stock Manager
    function wcfmpref_bulk_stock_manager($is_pref)
    {
        $bulk_stock_manager = ( isset($this->wcfm_module_options['bulk_stock_manager']) ) ? $this->wcfm_module_options['bulk_stock_manager'] : 'no';
        if ($bulk_stock_manager == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_bulk_stock_manager()


    // Vendor Reviews
    function wcfmpref_vendor_reviews($is_pref)
    {
        $reviews = ( isset($this->wcfm_module_options['reviews']) ) ? $this->wcfm_module_options['reviews'] : 'no';
        if ($reviews == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_reviews()


    // Vendor Followers
    function wcfmpref_vendor_followers($is_pref)
    {
        $vendor_followers = ( isset($this->wcfm_module_options['vendor_followers']) ) ? $this->wcfm_module_options['vendor_followers'] : 'no';
        if ($vendor_followers == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_followers()


    // Vendor Chat Box
    function wcfmpref_chatbox($is_pref)
    {
        $chatbox = ( isset($this->wcfm_module_options['chatbox']) ) ? $this->wcfm_module_options['chatbox'] : 'no';
        if ($chatbox == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_chatbox()


    // Vendor Support
    function wcfmpref_support($is_pref)
    {
        $support = ( isset($this->wcfm_module_options['support']) ) ? $this->wcfm_module_options['support'] : 'no';
        if ($support == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_support()


    // Shipment Tracking
    function wcfmpref_shipment_tracking($is_pref)
    {
        $shipment_tracking = ( isset($this->wcfm_module_options['shipment_tracking']) ) ? $this->wcfm_module_options['shipment_tracking'] : 'no';
        if ($shipment_tracking == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_shipment_tracking()


    // Vendor Invoice
    function wcfmpref_vendor_invoice($is_pref)
    {
        $vendor_invoice = ( isset($this->wcfm_module_options['vendor_invoice']) ) ? $this->wcfm_module_options['vendor_invoice'] : 'no';
        if ($vendor_invoice == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_invoice()


    // Vendor Badges
    function wcfmpref_vendor_badges($is_pref)
    {
        $vendor_badges = ( isset($this->wcfm_module_options['vendor_badges']) ) ? $this->wcfm_module_options['vendor_badges'] : 'no';
        if ($vendor_badges == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_badges()


    // Vendor Verification
    function wcfmpref_vendor_verification($is_pref)
    {
        $vendor_verification = ( isset($this->wcfm_module_options['vendor_verification']) ) ? $this->wcfm_module_options['vendor_verification'] : 'no';
        if ($vendor_verification == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_verification()


    // Vendor Vacation
    function wcfmpref_vendor_vacation($is_pref)
    {
        $vendor_vacation = ( isset($this->wcfm_module_options['vendor_vacation']) ) ? $this->wcfm_module_options['vendor_vacation'] : 'no';
        if ($vendor_vacation == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_vendor_vacation()


    // Shipstation
    function wcfmpref_shipstation($is_pref)
    {
        $shipstation = ( isset($this->wcfm_module_options['shipstation']) ) ? $this->wcfm_module_options['shipstation'] : 'no';
        if ($shipstation == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_shipstation()


    // Facebook for Marketplace
    function wcfmpref_facebook_marketplace($is_pref)
    {
        $facebook_marketplace = ( isset($this->wcfm_module_options['facebook_marketplace']) ) ? $this->wcfm_module_options['facebook_marketplace'] : 'no';
        if ($facebook_marketplace == 'yes') {
            $is_pref = false;
        }

        return $is_pref;

    }//end wcfmpref_facebook_marketplace()


}//end class
