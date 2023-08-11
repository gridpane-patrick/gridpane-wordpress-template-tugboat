<?php extract( $args ); ?>
<?php foreach ( $options as $option ): ?>
    <li>
        <input class="filter-term" id="<?php echo esc_attr( $taxonomy . '-' . $option->term_id ) ?>" type="checkbox" name="filter-<?php echo $taxonomy ?>[]" value="<?php echo esc_attr( $option->term_id ); ?>" data-id="<?php echo esc_attr( $option->term_id ); ?>" data-taxonomy="<?php echo $taxonomy; ?>" data-value="<?php echo $option->term_id; ?>" >
        <label class="term-label" for="<?php echo esc_attr( $taxonomy . '-' . $option->term_id ) ?>"><?php echo esc_html( $option->name ); ?></label>
    </li>
<?php endforeach; ?>
