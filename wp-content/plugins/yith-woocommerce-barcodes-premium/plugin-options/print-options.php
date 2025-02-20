<?php
if( !defined( 'ABSPATH' ) )
    exit;


$tool = array(

    'print'  =>  array(

        'tool_section_start'   =>  array(
            'name'  => __('Print barcodes', 'yith-woocommerce-barcodes'),
            'type' =>   'title',
            'desc' => __( 'The print feature opens a new windows, so it is necessary to allow pop-ups on this page for the right functioning of the feature', 'yith-woocommerce-barcodes' ),

        ),

        'tool_print_barcodes_show_image'            => array(
	        'name'    => __( 'Show product image', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'onoff',
	        'desc'    => __( 'Enable to add the product image in the printable products list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_show_image',
	        'default' => 'no',
        ),
        'tool_print_barcodes_show_name'            => array(
	        'name'    => __( 'Show product name', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'onoff',
	        'desc'    => __( 'Enable to add the product name in the printable products list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_show_name',
	        'default' => 'yes',
        ),
        'tool_print_barcodes_show_price'            => array(
	        'name'    => __( 'Show product price', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'onoff',
	        'desc'    => __( 'Enable to add the product price in the printable products list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_show_price',
	        'default' => 'no',
        ),
        'tool_print_barcodes_show_sku'            => array(
	        'name'    => __( 'Show product sku', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'onoff',
	        'desc'    => __( 'Enable to add the product sku in the printable products list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_show_sku',
	        'default' => 'yes',
        ),
        'tool_print_barcodes_show_short_description'            => array(
	        'name'    => __( 'Show product short description', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'onoff',
	        'desc'    => __( 'Enable to add the product short description in the printable products list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_show_short_description',
	        'default' => 'no',
        ),
        'tool_print_barcodes_number_of_columns'            => array(
	        'name'    => __( 'Number of columns', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'number',
	        'desc'    => __( 'Choose the number of columns to display in the printable list', 'yith-woocommerce-barcodes' ),
	        'id'      => 'tool_print_barcodes_number_of_columns',
	        'default' => '2',
        ),
        'ywbc_enable_print_barcodes_variations'                => array(
	        'name'    => __( 'Print a barcode list of: ', 'yith-woocommerce-barcodes' ),
	        'type'    => 'yith-field',
	        'yith-type' => 'select',
	        'id'      => 'ywbc_enable_print_barcodes_variations',
	        'options' => array(
		        'all_products'       => __( "All products", 'yith-woocommerce-barcodes' ),
		        'include_variations'        => __( "All products, including variations", 'yith-woocommerce-barcodes' ),
	        ),
	        'default' => 'all_products',
        ),
        'tool_print_barcodes'    => array(
            'type'  =>'print-barcodes',
            'desc' => __('Choose to print a list of barcodes of all products and if include or not the products variations', 'yith-woocommerce-barcodes' ),
            'id'    =>  'ywbc_print_product_barcode'
        ),
        'tool_print_barcodes_by_products'    => array(
	        'name' => __('Print barcodes by product', 'yith-woocommerce-barcodes'),
	        'type'  =>'print-barcodes-by-products',
	        'id'    =>  'tool_print_barcodes_by_products'
        ),

        'tool_print_section_end' =>  array(
            'type'  =>  'sectionend',
        ),

    )
);

return apply_filters( 'ywbc_tool_otions', $tool );
