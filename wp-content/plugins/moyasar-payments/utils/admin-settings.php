<?php

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('Enable Moyasar Payment Gateway', 'moyasar-payments-text'),
        'default' => 'yes'
    ),

    'api_sk' => array(
        'title' => __('Secret Key', 'moyasar-payments-text'),
        'type' => 'text',
        'description' => __('This key is used by the server to verify payments upon clients return.', 'moyasar-payments-text')
    ),
    'api_pk' => array(
        'title' => __('Publishable Key', 'moyasar-payments-text'),
        'type' => 'text',
        'description' => __('This key is used by client\'s browser to create a new payment.', 'moyasar-payments-text')
    ),

    'enable_creditcard' => array(
        'title' => __('Enable Credit Card', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('This option allows you to enable Credit Card method.', 'moyasar-payments-text'),
        'default' => 'yes'
    ),
    'enable_applepay' => array(
        'title' => __('Enable Apple Pay', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('This option allows you to enable Apple Pay method.', 'moyasar-payments-text'),
        'default' => 'no'
    ),
    'enable_stcpay' => array(
        'title' => __('Enable STC Pay', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('This option allows you to enable STC Pay method.', 'moyasar-payments-text'),
        'default' => 'no'
    ),

    'supported_networks' => array(
        'title' => __('Supported Networks', 'moyasar-payments-text'),
        'type' => 'multiselect',
        'description' => __('Supported networks by Credit Card method and Apple Pay.', 'moyasar-payments-text'),
        'options' => array(
            'mada' => __('Mada', 'moyasar-payments-text'),
            'visa' => __('VISA', 'moyasar-payments-text'),
            'mastercard' => __('Mastercard', 'moyasar-payments-text'),
            'amex' => __('American Express', 'moyasar-payments-text')
        ),
        'default' => array(
            'mada',
            'visa',
            'mastercard'
        )
    ),

    'new_order_status' => array(
        'title' => __('New Order Status', 'moyasar-payments-text'),
        'type' => 'select',
        'default' => 'processing',
        'options' => array(
            'processing' => __('Processing', 'moyasar-payments-text'),
            'on-hold' => __('On Hold', 'moyasar-payments-text'),
            'completed' => __('Completed', 'moyasar-payments-text'),
        )
    ),

    'fixed_width' => array(
        'title' => __('Fixed Width', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('Set form max width to 340px', 'moyasar-payments-text'),
        'default' => 'yes'
    ),

    'auto_void' => array(
        'title' => __('Auto Void', 'moyasar-payments-text'),
        'type' => 'checkbox',
        'label' => __('Enable Auto void for payments that do not pass currency/amount check.', 'moyasar-payments-text'),
        'default' => 'no'
    ),
);
