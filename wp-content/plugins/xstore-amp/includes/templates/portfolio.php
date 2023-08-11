<?php
/**
 * Portfolio page template
 *
 * @package    portfolio.php
 * @since      1.0.1
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$tax_query = array();

if ( get_query_var('portfolio_category') ) {
	$tax_query = array(
		array(
			'taxonomy' => 'portfolio_category',
			'field' => 'slug',
			'terms' => get_query_var('portfolio_category')
		)
	);
}

$args = array(
	'post_type' => 'etheme_portfolio',
	'paged' => max( 1, get_query_var('paged') ),
	'posts_per_page' => (int)get_theme_mod('portfolio_count', 12),
	'tax_query' => $tax_query,
	'order' => get_theme_mod( 'portfolio_order', 'DESC' ),
	'orderby' => get_theme_mod( 'portfolio_orderby', 'title' ),
);

$wp_query = new WP_Query($args);
			
?>

<div class="grid">
	<?php if ( $wp_query->have_posts() ) : ?>
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();
		
				include XStore_AMP_TEMPLATES_PATH . 'content-portfolio.php';
			
            endwhile;
            
	endif; ?>
</div>

<?php

echo $this->navigation_pagination($wp_query);

wp_reset_postdata();
			
			