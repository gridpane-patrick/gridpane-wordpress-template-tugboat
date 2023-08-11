<?php extract( $args ); ?>

<form action="#" method="post" id="yith-sl-add-filter">
    <?php do_action( 'yith_sl_before_add_filter_fields' ); ?>

    <div class="form-field">
        <label for="filter_label"><?php esc_html_e( 'Label', 'yith-store-locator' ); ?></label>
        <input name="filter_label" id="filter_label" type="text" value="" />
        <p class="description"><?php esc_html_e( 'Name for the filter (shown on the front-end).', 'yith-store-locator' ); ?></p>
    </div>

    <div class="form-field">
        <label for="filter_slug"><?php esc_html_e( 'Slug', 'yith-store-locator' ); ?></label>
        <input name="filter_slug" id="filter_slug" type="text" value="" maxlength="20" />
        <p class="description"><?php esc_html_e( 'Unique slug/reference for the filter; must be no more than 28 characters.', 'yith-store-locator' ); ?></p>
    </div>

    <div class="form-field">
        <label for="filter_type"><?php esc_html_e( 'Type', 'yith-store-locator' ); ?></label>
        <?php

        $field_type = array(
            'id'        => 'filter_type',
            'name'      => 'filter_type',
            'type'      => 'select',
            'options'   => $types,
        );

        yith_plugin_fw_get_field( $field_type, true, false );

        ?>
        <p class="description"><?php esc_html_e( "Determines how this filters' terms are displayed.", 'yith-store-locator' ); ?></p>
    </div>

    <div class="form-field">
        <label for="filter_icon"><?php esc_html_e( 'Custom icon', 'yith-store-locator' ); ?></label>

        <?php
        $field_icon = array(
            'id'        => 'filter_icon',
            'name'      => 'filter_icon',
            'type'      => 'upload',
        );

        yith_plugin_fw_get_field( $field_icon, true, false );

        ?>
        <p class="description"><?php esc_html_e( "Upload a custom icon for new filter", 'yith-store-locator' ); ?></p>
    </div>

    <div class="form-field">
        <?php
        $field_show_label = array(
            'id'        =>  'filter_show_label',
            'name'      =>  'filter_show_label',
            'type'      =>  'checkbox',
            'value'     => 'yes'
        );

        yith_plugin_fw_get_field( $field_show_label, true, false );

        ?>
        <label for="filter_show_label"><?php esc_html_e( 'Show filter name', 'yith-store-locator' ); ?></label>

        <p class="description"><?php esc_html_e( "Choose if you want to show the filter name", 'yith-store-locator' ); ?></p>
    </div>


    <?php wp_nonce_field( 'yith_sl_add_new_filter', 'yith_sl_add_new_filter' ); ?>

    <p class="submit">
        <button type="submit" name="add_new_filter" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add attribute', 'yith-store-locator' ); ?>">
            <?php esc_html_e( 'Add filter', 'yith-store-locator' ); ?>
        </button>
    </p>
</form>