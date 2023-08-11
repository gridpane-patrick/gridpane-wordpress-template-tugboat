<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_kashier_settings',
	array(
		'enabled'                       => array(
			'title'       => __( 'Enable/Disable' . ' ' . $this->method_title , 'woocommerce-gateway-kashier' ),
			'label'       => __( 'Enable ' . $this->method_title, 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title'                         => array(
			'title'       => __( 'Title', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-kashier' ),
			'default'     => __( $this->method_title , 'woocommerce-gateway-kashier' ),
			'desc_tip'    => true,
		),
		'description'                   => array(
			'title'       => __( 'Description', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-kashier' ),
			'default'     => __( $this->method_public_description, 'woocommerce-gateway-kashier' ),
			'desc_tip'    => true,
		),
		'testmode'                      => array(
			'title'       => __( 'Test mode', 'woocommerce-gateway-kashier' ),
			'label'       => __( 'Enable Test Mode', 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' => __( 'Uncheck the box to start live payments.', 'woocommerce-gateway-kashier' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		),
        'merchant_id'                         => array(
            'title'       => __( 'Merchant ID', 'woocommerce-gateway-kashier' ),
			'placeholder' => 'MID-XXX-XXX',
            'type'        => 'text',
            'description' => __( 'Get your Merchant ID from your kashier account.', 'woocommerce-gateway-kashier' ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'test_api_key'          => array(
			'title'       => __( 'Test API Key', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'Please enter the customizable form API key.', 'woocommerce-gateway-kashier' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'api_key'               => array(
			'title'       => __( 'Live API Key', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'Please enter the customizable form API key.', 'woocommerce-gateway-kashier' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'test_secret_key'          => array(
			'title'       => __( 'Test Secret Key', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'Please enter the customizable form Secret key.', 'woocommerce-gateway-kashier' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'secret_key'               => array(
			'title'       => __( 'Live Secret Key', 'woocommerce-gateway-kashier' ),
			'type'        => 'text',
			'description' => __( 'Please enter the customizable form Secret key.', 'woocommerce-gateway-kashier' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'logging'                       => array(
			'title'       => __( 'Logging', 'woocommerce-gateway-kashier' ),
			'label'       => __( 'Log debug messages', 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-kashier' ),
			'default'     => 'no',
			'desc_tip'    => true,
		),
		'advanced_options'                       => array(
			'title'       => __( 'Advanced Options', 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' => __( 'Enable Advance Options', 'woocommerce-gateway-kashier' ),
			'desc_tip'    => true,
			'default'     => 'no',
		),
		'enforce_egp_payment'                       => array(
			'title'       => __( 'Enforce EGP Payment', 'woocommerce-gateway-kashier' ),
			'label'       => __( 'Convert to EGP', 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' => __( 'Enforce EGP payment. Regardless what is displayed currency on your website.', 'woocommerce-gateway-kashier' ),
			'desc_tip'    => true,
			'default'     => 'no',
		),
		'exchange_rate'                         => array(
            'title'       => __( 'Currency Exchange Rate', 'woocommerce-gateway-kashier' ),
            'type'        => 'decimal',
            'description' => __( 'The exchange rate from the default currency on your website to EGP.', 'woocommerce-gateway-kashier' ),
            'default'     => '1',
			'custom_attributes' => array('min' => 1, 'max'=> 15),
            'desc_tip'    => true,
        ),
		'connected_account'               => array(
			'title'       => __( 'Conntecd Account', 'woocommerce-gateway-kashier' ),
			'label'       => __( 'Enable Conntecd Account', 'woocommerce-gateway-kashier' ),
			'type'        => 'checkbox',
			'description' =>  __( 'Gives the ability for authorized Platforms to make payments on behalf of another Kashier Merchants called that are enabled for Connected accounts.', 'woocommerce-gateway-kashier' ),
			'default'     => 'no',
			'desc_tip'    => true,
		),
	)
);

