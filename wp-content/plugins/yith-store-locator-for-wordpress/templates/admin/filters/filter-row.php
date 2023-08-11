<?php
extract( $args );
$post_type = YITH_Store_Locator_Post_Type::$post_type_name;
$prefix = YITH_Store_Locator_Filters_Taxonomies()->get_prefix();
$edit_url = admin_url( 'admin.php?page=yith_sl_panel&tab=stores&sub_tab=stores-filters' );

?>
<tr class="filter-item-row data-order="<?php echo (int)$filter_order; ?>" data-id="<?php echo esc_attr( $filter_id ); ?>">
    <td>
        <strong><a href="<?php echo $edit_url . '&edit-taxonomy=' . $filter_id ?>"><?php echo esc_html( $filter_label ); ?></a></strong>
        <div class="row-actions">
            <span class="edit"><a href="<?php echo esc_url( $edit_url . '&edit-taxonomy=' . $filter_id ); ?>"><?php esc_html_e( 'Edit', 'yith-store-locator' ); ?></a></span>
            <span class="delete"><a class="yith-sl-delete-filter" href="#" data-filter-slug="<?php echo esc_attr( $prefix . $filter_slug ); ?>" data-filter-id="<?php esc_attr_e( $filter_id ); ?>"><?php esc_html_e( 'Delete', 'yith-store-locator' ); ?></a></span>
        </div>
    </td>
    <td><?php echo esc_html( $filter_slug ); ?></td>
    <td><?php echo esc_html( $filter_type ) ?></td>
    <td class="filter-terms">

        <br>
        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=' . $prefix . $filter_slug . '&post_type=' . $post_type ) ?>" target="_blank">
            <?php esc_html_e( 'Configure terms', 'yith-store-locator' ); ?>
        </a>
    </td>
</tr>