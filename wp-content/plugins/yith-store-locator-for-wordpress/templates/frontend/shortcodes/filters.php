<?php
$filter_display_mode = yith_sl_get_option( 'filters-display-mode', 'opened' );
$filter_radius_step_default = !! get_option( 'yith_sl_default_radius', '50' ) ? get_option( 'yith_sl_default_radius', '50' ) : '';
$args = array(
        'filter_display_mode'            =>  $filter_display_mode,
        'show_filter_radius'             =>  yith_sl_get_option( 'enable-filter-radius', 'yes' ),
        'filter_radius_step'             =>  explode( ',', yith_sl_get_option( 'filter-radius-step', '10,20,30,40,50,100' )),
        'filter_radius_step_default'     =>  $filter_radius_step_default,
        'filter_radius_title'            =>  YITH_Store_Locator_Filters_Taxonomies()->get_filter_radius_title(),
        'distance_unit'                  =>  yith_sl_get_option( 'filter-radius-distance-unit', 'km' ),
        'show_filters'                   =>  yith_sl_get_option( 'enable-filters', 'yes' ),
        'prefix'                         =>  YITH_Store_Locator_Filters_Taxonomies()->get_prefix()
);

do_action( 'yith-sl-before-filters-container' ); ?>

<div id="yith-sl-main-filters-container" class="<?php echo 'layout-' . $filter_display_mode; ?>">

    <a id="yith-sl-open-filters" href="#" rel="nofollow"><?php echo esc_html( apply_filters( 'yith_sl_open_filters_label', __( 'Open filters', 'yith-store-locator' ) ) ); ?></a>

    <div class="wrap-filters-list">
        <?php yith_sl_get_template( $filter_display_mode . '.php', '/frontend/shortcodes/filters/', $args ); ?>

        <?php do_action( 'yith-sl-after-filters-list' ); ?>
    </div>

</div>

<div id="yith-sl-active-filters">

</div>

<?php do_action( 'yith-sl-after-filters-container' ); ?>
