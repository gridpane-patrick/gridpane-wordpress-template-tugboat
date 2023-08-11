<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}?>
<body <?php echo body_class(); ?>>
<?php wp_head(); ?>




<div id="yith-sl-full-page">

    <?php echo do_shortcode("[yith_store_locator]"); ?>
</div>

<?php wp_footer(); ?>

</body>


