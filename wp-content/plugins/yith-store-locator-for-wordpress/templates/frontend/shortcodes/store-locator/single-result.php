<?php
extract( $args );
$is_featured = $store->get_prop( 'featured' );
$show_name = yith_sl_get_option( 'stores-list-show-store-name', 'yes' );
$show_image = yith_sl_get_option( 'stores-list-show-store-image', 'yes' );
$show_description = yith_sl_get_option( 'stores-list-show-store-description', 'no' );

$show_address = yith_sl_get_option( 'stores-list-show-store-address', 'yes' );
$show_get_direction = yith_sl_get_option( 'stores-list-show-get-directions', 'yes' );
$get_direction_style = yith_sl_get_option( 'stores-list-get-direction-style', 'link' );

$show_contact_info = yith_sl_get_option( 'stores-list-show-store-contact-info', 'yes' );
$show_contact_store = yith_sl_get_option( 'stores-list-show-contact-store', 'yes' );
$contact_store_style = yith_sl_get_option( 'stores-list-contact-store-style', 'link' );
$contact_store_page = yith_sl_get_option( 'stores-list-contact-store-page' );
if( !!$contact_store_page ){
    $contact_store_page_url = get_the_permalink( $contact_store_page );
}

$store_name_link = $store->get_store_name_link();

$show_view_website = yith_sl_get_option( 'stores-list-show-visit-website', 'yes' );
$view_website_style = yith_sl_get_option( 'stores-list-visit-website-style', 'link' );
$view_website_text = yith_sl_get_option( 'stores-list-visit-website-text', esc_html__( 'View website','yith-store-locator' ) );
?>

<li class="wrap-store-details <?php if( $is_featured ) echo 'featured' ?>" data-id="<?php echo esc_attr( $store->get_id() ) ?>">

    <?php if( $show_image === 'yes' ): ?>
        <div class="store-image">
            <?php
            $image = $store->get_image();
            if( !empty( $image ) ):
                echo wp_kses_post( $image );
            else: ?>
                <div class="no-image">
                    <p class="store-name">
                        <?php echo esc_html( $store->get_name() ); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <address class="store-info">
        <p class="store-name">

            <?php if( $show_name === 'yes' ): ?>
                <?php if( $store_name_link === 'none' ): ?>
                    <span><?php echo esc_html( $store->get_name() ); ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url( $store_name_link ); ?>" >
                        <?php echo esc_html( $store->get_name() ); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if( $is_featured ): ?>
                <span class="featured-store">
                    <?php
                    $featured_icon = yith_sl_get_option( 'stores-list-featured-icon', YITH_SL_ASSETS_URL .'images/store-locator/featured.svg' );
                    $featured_label = yith_sl_get_option( 'stores-list-featured-label', esc_html__( 'Featured', 'yith-store-locator' ) );
                    ?>
                    <img src="<?php echo esc_url( $featured_icon ); ?>" />
                    <?php echo esc_html( $featured_label ); ?>
                </span>
            <?php endif; ?>
        </p>

        <?php if( $show_description === 'yes' && !! $store->get_description() ): ?>
            <p class="store-description">
                <?php echo wp_kses_post( $store->get_description() ); ?>
            </p>
        <?php endif; ?>

        <?php if( $show_address === 'yes' ): ?>
            <p class="store-address">
                <?php echo wp_kses_post( $store->get_full_address() ); ?>
            </p>
        <?php endif; ?>

        <?php if( $show_contact_info === 'yes' ): ?>
            <ul class="store-contact">
                <?php if( !! $store->get_prop( 'phone' )  ): ?>
                    <li class="store-phone">
                        <b>
                            <?php esc_html_e( 'Phone','yith-store-locator' ); ?>:</b>
                        <a href="tel:<?php echo $store->get_prop( 'phone' ); ?>"><?php echo $store->get_prop( 'phone' ); ?></a>
                    </li>
                <?php endif; ?>
                <?php if( !! $store->get_prop( 'mobile_phone' ) ) : ?>
                    <li class="store-mobile">
                        <b>
                            <?php esc_html_e( 'Mobile Phone','yith-store-locator' ); ?>:
                        </b>
                        <a href="tel:<?php echo $store->get_prop( 'mobile_phone' );?>"><?php echo $store->get_prop( 'mobile_phone' );?></a>
                    </li>
                <?php endif; ?>
                <?php if( !!$store->get_prop( 'email' ) ): ?>
                    <li class="store-email">
                        <b>
                            <?php esc_html_e( 'Email','yith-store-locator' ); ?>:</b>
                        <?php echo $store->get_prop( 'email' ); ?>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <?php if( $show_get_direction === 'yes' && !!$store->get_direction_link() )  : ?>
            <a target="_blank" rel="noopener" class="get-direction custom-link <?php echo $get_direction_style ?>" href="<?php echo esc_url( $store->get_direction_link() ); ?>"><?php esc_html_e( 'Get direction >','yith-store-locator' ); ?></a>
        <?php endif; ?>

        <?php if( $show_contact_store === 'yes' && isset( $contact_store_page_url ) ): ?>
            <a target="_blank" class="contact-store custom-link <?php echo $contact_store_style?>" href="<?php echo esc_url( $contact_store_page_url ); ?>"><?php esc_html_e( 'Contact store','yith-store-locator' ); ?></a>
        <?php endif; ?>

        <?php if( $show_view_website === 'yes' && !! $store->get_prop( 'website' ) ): ?>
            <?php $target = apply_filters( 'yith_sl_target_view_website_link', '_blank' ); ?>
            <a target="<?php echo esc_attr( $target ); ?>" rel="noopener" class="view-website custom-link <?php echo $view_website_style?>" href="<?php echo esc_url( $store->get_prop( 'website' ) ); ?>"><?php echo esc_html( $view_website_text ); ?></a>
        <?php endif; ?>
    </address>
</li>