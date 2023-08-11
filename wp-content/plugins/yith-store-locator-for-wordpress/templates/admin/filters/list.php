<?php
$post_type = YITH_Store_Locator_Post_Type::$post_type_name;
$prefix = YITH_Store_Locator_Filters_Taxonomies()->get_prefix();
$filter_radius_slug = YITH_Store_Locator_Filters_Taxonomies()->get_radius_filter_slug();
$edit_url = admin_url( 'admin.php?page=yith_sl_panel&tab=stores&sub_tab=stores-filters' );

?>
<div id="yith-sl-filters-list">
    <table class="widefat filters-table wp-list-table ui-sortable" style="width:100%">
        <thead>
        <tr>
            <th scope="col"><?php esc_html_e( 'Label', 'yith-store-locator' ); ?></th>
            <th scope="col"><?php esc_html_e( 'Slug', 'yith-store-locator' ); ?></th>
            <th scope="col"><?php esc_html_e( 'Type', 'yith-store-locator' ); ?></th>
            <th scope="col"><?php esc_html_e( 'Values', 'yith-store-locator' ); ?></th>
        </tr>
        </thead>
        <tbody id="filters-sortable-container">
        <?php
        $filter_taxonomies = YITH_Store_Locator()->filters->get_filters();
        $i = 0;
        if ( is_array( $filter_taxonomies ) ) :
            foreach ( $filter_taxonomies as $slug => $tax ) :
                ?>
                <tr class="filter-item-row" data-order="<?php echo esc_attr($i); ?>" data-id="<?php echo esc_attr( $tax['id'] ); ?>">
                    <td>
                        <strong><a href="<?php echo $edit_url . '&edit-taxonomy=' . $tax['id'] ?>"><?php echo esc_html( $tax['label'] ); ?></a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="<?php echo esc_url( $edit_url . '&edit-taxonomy=' . $tax['id'] ); ?>"><?php esc_html_e( 'Edit', 'yith-store-locator' ); ?></a></span>
                            <?php if( ! yith_sl_is_radius_filter( $slug ) ): ?>
                                <span><a class="yith-sl-delete-filter" href="#" data-filter-slug="<?php echo esc_attr( $prefix . $slug ); ?>" data-filter-id="<?php esc_attr_e( $tax['id'] ); ?>"><?php esc_html_e( 'Delete', 'yith-store-locator' ); ?></a></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo esc_html( $slug ); ?></td>
                    <td><?php echo esc_html( $tax['type'] ) ?></td>
                    <td class="filter-terms">
                        <?php
                        $terms        = get_terms( $prefix . $slug, 'hide_empty=0' );
                        $terms_string = implode( ', ', wp_list_pluck( $terms, 'name' ) );
                        if ( $terms_string ) {
                            echo esc_html( $terms_string );
                        } else {
                            echo '<span class="na">&ndash;</span>';
                        }
                        ?>
                        <br>
                        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=' . $prefix . $slug . '&post_type=' . $post_type ) ?>">
                            <?php esc_html_e( 'Configure terms', 'yith-store-locator' ); ?>
                        </a>
                    </td>
                </tr>
            <?php
            $i++;
            endforeach;
        else :
            ?>
            <tr class="no-filters">
                <td colspan="6"><?php esc_html_e( 'No filters currently exist.', 'yith-store-locator' ); ?></td>
            </tr>
        <?php
        endif;
        ?>
        </tbody>
    </table>
    <div id="yith-sl-loader"></div>
</div>

