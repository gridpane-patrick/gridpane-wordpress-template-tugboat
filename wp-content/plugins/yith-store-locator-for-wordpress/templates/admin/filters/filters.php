<div class="wrap yith-sl-filters yith-plugin-fw-panel-custom-tab-container">
    <h1><?php esc_html_e( 'Stores filters', 'yith-store-locator' ); ?></h1>

    <br class="clear" />
    <div id="col-container">
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h3><?php esc_html_e( 'Add new filter', 'yith-store-locator' ); ?></h3>
                    <p><?php esc_html_e( 'First, create a filter (for example: "Services") and then populate it with terms ("Parking")', 'yith-store-locator' ); ?></p>
                    <?php yith_sl_get_template( 'add-new.php', 'admin/filters/', $args ); ?>
                </div>
            </div>
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <?php yith_sl_get_template( 'list.php', 'admin/filters/', $args ); ?>
            </div>
        </div>

    </div>
    <script type="text/javascript">
        /* <![CDATA[ */

        jQuery( 'a.delete' ).click( function() {
            if ( window.confirm( '<?php esc_html_e( 'Are you sure you want to delete this attribute?', 'yith-store-locator' ); ?>' ) ) {
                return true;
            }
            return false;
        });

        /* ]]> */
    </script>
</div>