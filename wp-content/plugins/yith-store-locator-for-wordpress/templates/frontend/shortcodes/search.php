<?php

extract( $args );

do_action( 'yith-sl-before-search-store-container' );

$search_bar_icon    = yith_sl_get_option( 'icon-search-bar', YITH_SL_ASSETS_URL . 'images/store-locator/search.svg' );

?>

<div id="yith-sl-wrap-search-stores">

    <div id="yith-sl-search-stores">

        <h4 class="title-search-bar"><?php echo esc_html( $title_search_bar ); ?></h4>

        <?php if( $enable_search_bar === 'yes' ): ?>
            <label for="search-stores" class="wrap-search-bar" id="yith-sl-wrap-search-bar">
                <input type="text" id="yith-sl-search-bar-address" class="search-stores yith-sl-gmap-places-autocomplete" name="search-stores" placeholder="<?php echo esc_attr( $placeholder ); ?>">
                <img id="yith-sl-search-icon" src="<?php echo $search_bar_icon ?>">
                <span id="address-tooltip">
                    <?php echo apply_filters( 'yith_sl_address_tooltip_text', esc_html__( 'Please enter a valid address', 'yith-store-locator' )); ?>
                </span>
            </label>
        <?php endif; ?>

        <div class="wrap-buttons">
            <?php if( $enable_geolocation === 'yes' ):
                if( $geolocation_style === 'button' ): ?>
                    <button class="yith-sl-geolocation" id="yith-sl-geolocation"><?php echo esc_html( $geolocation_text ); ?></button>
                <?php else : ?>
                    <a class="yith-sl-geolocation style-text" id="yith-sl-geolocation" href="#" rel="nofollow"><?php echo esc_html( $geolocation_text ); ?></a>
                <?php endif;
            endif;
            if( $enable_show_all_stores === 'yes' ): ?>
                <button class="show-all-stores search-button" id="yith-sl-show-all-stores"><?php echo esc_html( $show_all_stores_text ); ?></button>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php do_action( 'yith-sl-after-search-store-container' );

?>

