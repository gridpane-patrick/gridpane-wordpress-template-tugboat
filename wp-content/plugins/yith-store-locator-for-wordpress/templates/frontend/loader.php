<?php
$loader_type = yith_sl_get_option( 'loader-type', 'default' );
$loader_icon = yith_sl_get_loader_icon( $loader_type );
$default_loader =   yith_sl_get_option( 'loader-icon', 'loader1' );
$loader_color                           = yith_sl_get_option( 'loader-icon-color','#18BCA9' );
$loader_size                            = yith_sl_get_loader_size();

if( $loader_type === 'default' ):

    $args = array(
        'loader_color'    =>  $loader_color,
        'loader_size'   =>  $loader_size
    );?>

    <div id="yith-sl-wrap-loader">

        <?php yith_sl_get_template( $default_loader . '.php', 'frontend/loader/', $args ); ?>

    </div>

<?php

else:
    ?>
    <div id="yith-sl-wrap-loader" class="<?php echo $loader_type ?>">
        <img id="yith-sl-loader" class="<?php echo $loader_type ?>" src="<?php echo esc_attr( $loader_icon ) ?>">
    </div>
<?php
endif;

?>

