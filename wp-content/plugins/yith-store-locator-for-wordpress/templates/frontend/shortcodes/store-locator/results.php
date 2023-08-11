<?php
extract( $args );
if( is_array( $stores ) && !! $stores ):

    $n_stores = count( $stores );
    $n_stores_to_show = min( yith_sl_get_option( 'number-results-to-show', 5 ), $n_stores );

    ?>

    <ul>

    <?php
        for ( $i=0; $i < $n_stores_to_show; $i++ ):

            $store = YITH_SL_Store($stores[$i]['id']);

            $args = array(
              'store'   =>  $store
            );

            yith_sl_get_template( 'single-result.php', 'frontend/shortcodes/store-locator/', $args );

         endfor;

         ?>

    </ul>

    <?php if( $i < $n_stores ): ?>
        <a href="#" rel="nofollow" id="yith-sl-view-all"><?php echo esc_html( yith_sl_get_option( 'view-all-text', __( 'View all stores', 'yith-store-locator' ) ) ); ?></a>
    <?php endif;

    if( $i < $n_stores ) :

        ?>
        <ul class="additional-stores">

        <?php

            for ( ; $i < $n_stores; $i++ ):

                $store = YITH_SL_Store($stores[$i]['id']);
                $args = array(
                    'store'   =>  $store
                );
                yith_sl_get_template( 'single-result.php', 'frontend/shortcodes/store-locator/', $args );

            endfor;
        ?>

        </ul>

    <?php endif; ?>



    <?php else: ?>
    <p id="no-results-text">
        <?php echo yith_sl_get_option('no-results-text', esc_html__( 'Oops! It looks like no results match your search criteria','yith-store-locator' ) ); ?>
    </p>
<?php endif; ?>