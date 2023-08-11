<?php
/**
 * Home page template
 *
 * @package    page.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings;

$options = array();

$options['elements'] = array(
	'slider',
	'products_categories_01',
	'products_01',
	'banner_01',
	'posts_01',
	'textarea_block_01'
);
$options['callbacks'] = array(
	'slider' => array($this, 'home_slider'),
	'products_categories_01' => array($this, 'get_products_categories'),
	'products_categories_02' => array($this, 'get_products_categories'),
	'products_01' => array($this, 'get_products'),
	'products_02' => array($this, 'get_products'),
	'banner_01' => array($this, 'banner'),
	'posts_01' => array($this, 'get_posts'),
	'textarea_block_01' => array($this, 'textarea_block'),
);
$options['callbacks_args'] = array(
	'products_01' => array(
		'type' => 'random',
		'args' => array(),
		'carousel_args' => array()
	),
	'products_02' => array(
		'type' => 'random',
		'args' => array(),
		'carousel_args' => array()
	),
	'products_categories_01' => array(
		'args' => array(),
		'carousel_args' => array()
	),
	'products_categories_02' => array(
		'args' => array(),
		'carousel_args' => array()
	),
	'posts_01' => array(
		'type' => 'random',
		'args' => array()
	),
	'banner_01' => array(
		'args' => array()
	),
	'textarea_block_01' => array(
		'args' => array()
	),
);

if ( isset($xstore_amp_settings['home_page']['page_elements']) && !empty($xstore_amp_settings['home_page']['page_elements']) ) {
	$options['elements'] = explode(',', $xstore_amp_settings['home_page']['page_elements']);
	foreach ( $options['elements'] as $element_key => $element_name ) {
		if ( !isset($xstore_amp_settings['home_page'][$element_name.'_visibility']) || !$xstore_amp_settings['home_page'][$element_name.'_visibility'] ) {
			unset($options['elements'][$element_key]);
		}
	}
}
// products
if ( in_array('products_01', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['products_01_type'] ) ) {
		$options['callbacks_args']['products_01']['type'] = $xstore_amp_settings['home_page']['products_01_type'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_01_limit'] ) ) {
		$options['callbacks_args']['products_01']['args']['limit']          = $xstore_amp_settings['home_page']['products_01_limit'];
		$options['callbacks_args']['products_01']['args']['posts_per_page'] = $xstore_amp_settings['home_page']['products_01_limit'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_01_order'] ) ) {
		$options['callbacks_args']['products_01']['args']['order'] = $xstore_amp_settings['home_page']['products_01_order'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_01_title'] ) ) {
		$options['callbacks_args']['products_01']['carousel_args']['title'] = $xstore_amp_settings['home_page']['products_01_title'];
	}
}

if ( in_array('products_02', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['products_02_type'] ) ) {
		$options['callbacks_args']['products_02']['type'] = $xstore_amp_settings['home_page']['products_02_type'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_01_limit'] ) ) {
		$options['callbacks_args']['products_02']['args']['limit']          = $xstore_amp_settings['home_page']['products_02_limit'];
		$options['callbacks_args']['products_02']['args']['posts_per_page'] = $xstore_amp_settings['home_page']['products_02_limit'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_01_order'] ) ) {
		$options['callbacks_args']['products_02']['args']['order'] = $xstore_amp_settings['home_page']['products_02_order'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_02_title'] ) ) {
		$options['callbacks_args']['products_02']['carousel_args']['title'] = $xstore_amp_settings['home_page']['products_02_title'];
	}
}

// product categories
if ( in_array('products_categories_01', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['products_categories_01_title'] ) ) {
		$options['callbacks_args']['products_categories_01']['carousel_args']['title'] = $xstore_amp_settings['home_page']['products_categories_01_title'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_categories_01_limit'] ) ) {
		$options['callbacks_args']['products_categories_01']['args']['number']          = $xstore_amp_settings['home_page']['products_categories_01_limit'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_categories_01_order'] ) ) {
		$options['callbacks_args']['products_categories_01']['args']['order'] = $xstore_amp_settings['home_page']['products_categories_01_order'];
	}
}

if ( in_array('products_categories_02', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['products_categories_02_title'] ) ) {
		$options['callbacks_args']['products_categories_02']['carousel_args']['title'] = $xstore_amp_settings['home_page']['products_categories_02_title'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_categories_02_limit'] ) ) {
		$options['callbacks_args']['products_categories_02']['args']['number']          = $xstore_amp_settings['home_page']['products_categories_02_limit'];
	}
	if ( isset( $xstore_amp_settings['home_page']['products_categories_01_order'] ) ) {
		$options['callbacks_args']['products_categories_02']['args']['order'] = $xstore_amp_settings['home_page']['products_categories_02_order'];
	}
}
// posts
if ( in_array('posts_01', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['posts_01_title'] ) ) {
		$options['callbacks_args']['posts_01']['carousel_args']['title'] = $xstore_amp_settings['home_page']['posts_01_title'];
	}
	if ( isset( $xstore_amp_settings['home_page']['posts_01_type'] ) ) {
		$options['callbacks_args']['posts_01']['type'] = $xstore_amp_settings['home_page']['posts_01_type'];
	}
	if ( isset( $xstore_amp_settings['home_page']['posts_01_limit'] ) ) {
		$options['callbacks_args']['posts_01']['args']['limit']          = $xstore_amp_settings['home_page']['posts_01_limit'];
		$options['callbacks_args']['posts_01']['args']['posts_per_page'] = $xstore_amp_settings['home_page']['posts_01_limit'];
	}
	if ( isset( $xstore_amp_settings['home_page']['posts_01_order'] ) ) {
		$options['callbacks_args']['posts_01']['args']['order'] = $xstore_amp_settings['home_page']['posts_01_order'];
	}
}
// banner
if ( in_array('banner_01', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['banner_01_image'] ) ) {
		$options['callbacks_args']['banner_01']['args']['image'] = $xstore_amp_settings['home_page']['banner_01_image'];
	}
	if ( isset( $xstore_amp_settings['home_page']['banner_01_title'] ) ) {
		$options['callbacks_args']['banner_01']['args']['title'] = $xstore_amp_settings['home_page']['banner_01_title'];
	}
	if ( isset( $xstore_amp_settings['home_page']['banner_01_content'] ) ) {
		$options['callbacks_args']['banner_01']['args']['content'] = $xstore_amp_settings['home_page']['banner_01_content'];
	}
	if ( isset( $xstore_amp_settings['home_page']['banner_01_button_text'] ) ) {
		$options['callbacks_args']['banner_01']['args']['button_text'] = $xstore_amp_settings['home_page']['banner_01_button_text'];
	}
	if ( isset( $xstore_amp_settings['home_page']['banner_01_button_url'] ) ) {
		$options['callbacks_args']['banner_01']['args']['button_url'] = $xstore_amp_settings['home_page']['banner_01_button_url'];
	}
	if ( isset( $xstore_amp_settings['home_page']['banner_01_height'] ) ) {
		$options['callbacks_args']['banner_01']['args']['height'] = $xstore_amp_settings['home_page']['banner_01_height'];
	}
}

if ( in_array('textarea_block_01', $options['elements']) ) {
	if ( isset( $xstore_amp_settings['home_page']['textarea_block_01_title'] ) ) {
		$options['callbacks_args']['textarea_block_01']['args']['title'] = $xstore_amp_settings['home_page']['textarea_block_01_title'];
	}
	if ( isset( $xstore_amp_settings['home_page']['textarea_block_01_content'] ) ) {
		$options['callbacks_args']['textarea_block_01']['args']['content'] = $xstore_amp_settings['home_page']['textarea_block_01_content'];
	}
}

$i=0;

global $xstore_amp_el_settings;
foreach ($options['elements'] as $element) {
	$i++;
	if ( count($options['elements']) > $i) {
		$xstore_amp_el_settings['space'] = '10vw';
	}
	else {
		unset($xstore_amp_el_settings['space']);
	}
    if ( isset( $options['callbacks_args'][ $element ] ) ) {
        call_user_func_array( $options['callbacks'][ $element ], $options['callbacks_args'][ $element ] );
    } else {
        call_user_func( $options['callbacks'][ $element ] );
    }
}

unset($options);