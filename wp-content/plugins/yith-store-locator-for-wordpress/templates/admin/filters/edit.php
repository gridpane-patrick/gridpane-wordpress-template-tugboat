<?php
extract( $args );
$filter     =   yith_sl_get_filter_taxonomy( $_GET['edit-taxonomy'] );
$label      =   !! $filter->filter_label ? $filter->filter_label : 'Undefined';
$icon       =   !! $filter->filter_icon ? $filter->filter_icon : '';
$slug       =   !! $filter->filter_slug ? $filter->filter_slug : '';
$type       =   !! $filter->filter_type ? $filter->filter_type : 'dropdown';
$show_label =   !! $filter->filter_show_label ?  $filter->filter_show_label : 0;

?>
<div class="yith-plugin-fw-panel-custom-tab-container yith-sl-filters wrap">
    <h3><?php esc_html_e( 'Edit filter', 'yith-store-locator' ); ?></h3>
    <form action="#" method="post" id="yith-sl-edit-filter">
        <?php do_action( 'yith_sl_before_edit_filter_fields' ); ?>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row" valign="top">
                        <label for="filter_label"><?php esc_html_e( 'Label', 'yith-store-locator' ); ?></label>
                    </th>
                    <td>
                        <input name="filter_label" id="filter_label" type="text" value="<?php echo esc_attr( $label ); ?>" />
                        <p class="description"><?php esc_html_e( 'Name for the filter (shown on the front-end).', 'yith-store-locator' ); ?></p>
                    </td>
                </tr>

                <tr class="form-field form-required <?php echo yith_sl_is_radius_filter( $slug ) ? 'hidden-field' : ''; ?>">
                    <th scope="row" valign="top">
                        <label for="filter_slug"><?php esc_html_e( 'Slug', 'yith-store-locator' ); ?></label>
                    </th>
                    <td>
                        <input name="filter_slug" id="filter_slug" type="text" value="<?php echo esc_attr( $slug ); ?>" maxlength="20" />
                        <p class="description"><?php esc_html_e( 'Unique slug/reference for the filter; must be no more than 28 characters.', 'yith-store-locator' ); ?></p>
                    </td>
                </tr>

                <tr class="form-field form-required <?php echo yith_sl_is_radius_filter( $slug ) ? 'hidden-field' : ''; ?>">
                    <th scope="row" valign="top">
                        <label for="filter_type"><?php esc_html_e( 'Type', 'yith-store-locator' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $field_type = array('id'        => 'filter_type',
                            'name'      => 'filter_type',
                            'type'      => 'select',
                            'options'   => $types,
                            'value'     => $type
                        );

                        yith_plugin_fw_get_field( $field_type, true, false );

                        ?>
                        <p class="description"><?php esc_html_e( "Determines how this filters' terms are displayed.", 'yith-store-locator' ); ?></p>
                    </td>
                </tr>

                <tr class="form-field form-required <?php echo yith_sl_is_radius_filter( $slug ) ? 'hidden-field' : ''; ?>">
                    <th scope="row" valign="top">
                        <label for="filter_icon"><?php esc_html_e( 'Custom icon', 'yith-store-locator' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $field_icon = array('id'        => 'filter_icon',
                            'name'      => 'filter_icon',
                            'type'      => 'upload',
                            'value'     =>  $icon
                        );

                        yith_plugin_fw_get_field( $field_icon, true, false );

                        ?>
                        <p class="description"><?php esc_html_e( "Upload a custom icon for this filter", 'yith-store-locator' ); ?></p>
                    </td>
                </tr>

                <tr class="form-field form-required <?php echo yith_sl_is_radius_filter( $slug ) || $filters_layout_general === 'dropdown' ? 'hidden-field' : ''; ?>"  >
                    <th scope="row" valign="top">
                        <label for="filter_show_label"><?php esc_html_e( 'Show filter name', 'yith-store-locator' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $field_icon = array(
                            'id'        =>  'filter_show_label',
                            'name'      =>  'filter_show_label',
                            'type'      =>  'checkbox',
                            'value'     =>  $show_label
                        );

                        yith_plugin_fw_get_field( $field_icon, true, false );

                        ?>
                        <p class="description"><?php esc_html_e( "Choose if you want to show the filter name", 'yith-store-locator' ); ?></p>
                    </td>
                </tr>

            </tbody>
        </table>

        <input type="hidden" name="yith-sl-edit-filter" value="<?php echo $_GET['edit-taxonomy']; ?>">

        <p class="submit">
            <button type="submit" name="add_edit_filter" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add attribute', 'yith-store-locator' ); ?>">
                <?php esc_html_e( 'Save Filter', 'yith-store-locator' ); ?>
            </button>
        </p>
    </form>
</div>
