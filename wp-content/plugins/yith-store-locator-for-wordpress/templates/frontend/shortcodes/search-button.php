<?php
extract( $args );
?>
<button class="search-stores <?php echo $enable_instant_search === 'yes' ? 'hidden' : '' ; ?>" id="yith-sl-search-button" type="submit">
    <?php echo esc_html( $search_button_text ); ?>
</button>