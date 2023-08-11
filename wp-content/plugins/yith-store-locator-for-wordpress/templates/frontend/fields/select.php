<?php
extract( $args );
if ( apply_filters( 'yith_sl_show_filter_label', $show_label ) === '1' ) : ?>
    <label for="<?php echo $taxonomy?>" class="filter-label">
        <?php if( $icon != '' ): ?>
            <img class="filter-icon" src="<?php echo esc_attr( $icon ); ?>" />
        <?php endif; ?>
        <?php echo $label; ?>
    </label>
<?php endif; ?>
<select id="<?php echo $taxonomy; ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>" name="filter-<?php echo $taxonomy ?>[]">
    <option value="selectall"><?php esc_html_e( 'Any', 'yith-store-locator' ); ?></option>
    <?php foreach ( $options as $option ): ?>
            <option value="<?php echo $option->term_id ?>" data-taxonomy="<?php echo $taxonomy; ?>" data-value="<?php echo $option->term_id; ?>">
                <?php echo $option->name ?>
            </option>
    <?php endforeach; ?>

</select>
