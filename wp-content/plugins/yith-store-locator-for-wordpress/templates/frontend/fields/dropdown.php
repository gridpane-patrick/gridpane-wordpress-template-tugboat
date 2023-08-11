<?php extract( $args ); ?>
<li>
    <label for="yith-sl-filter-<?php echo $taxonomy ?>-all" data-title="<?php echo $label ?>"><?php esc_html_e( 'Any', 'yith-store-locator' ); ?></label>
    <input id="yith-sl-filter-<?php echo $taxonomy ?>-all" type="radio" name="filter-<?php echo $taxonomy ?>[]" value="selectall" data-taxonomy="<?php echo $taxonomy; ?>" data-value="">
</li>
<?php foreach ( $options as $option ): ?>
    <li>
        <label for="yith-sl-filter-<?php echo $option->term_id ?>" data-title="<?php echo $option->name; ?>"><?php echo $option->name; ?></label>
        <input id="yith-sl-filter-<?php echo $option->term_id ?>" type="radio" name="filter-<?php echo $taxonomy ?>[]" value="<?php echo $option->term_id ?>" data-taxonomy="<?php echo $taxonomy; ?>" data-value="<?php echo $option->term_id; ?>">
    </li>
<?php endforeach; ?>
