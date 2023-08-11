<?php
/**
 * Shop sidebar template.
 *
 * @package    sidebar.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_icons;

if ( is_active_sidebar( 'xstore-amp-shop-sidebar' ) ) {
	?>
	<amp-sidebar id="shopSidebar" layout="nodisplay" side="left">
        <button class="close" on="tap:shopSidebar.close"><?php echo $xstore_amp_icons['et_icon-close']; ?></button>
		<?php
            ob_start();
			    dynamic_sidebar( 'xstore-amp-shop-sidebar' );
			$content = ob_get_clean();
		    $content = str_replace('<form', '<form target="_top"', $content);
		    $content = str_replace('https://', '//', $content);
		    $content = str_replace('http://', '//', $content);
//		    $content = $this->render_amp_content($content);
			echo $content;
		?>
	</amp-sidebar>
    <amp-animation id="shopSidebarShowAnim" layout="nodisplay">
        <script type="application/json">
            {
                "duration": "200ms",
                "fill": "both",
                "iterations": "1",
                "direction": "alternate",
                "animations": [
                    {
                        "selector": "#shopSidebarButton",
                        "keyframes": [
                            { "opacity": "1", "visibility": "visible", "transform": "translate(-50%)" }
                        ]
                    }
                ]
            }
        </script>
    </amp-animation>
    <amp-animation id="shopSidebarHideAnim" layout="nodisplay">
        <script type="application/json">
            {
                "duration": "200ms",
                "fill": "both",
                "iterations": "1",
                "direction": "alternate",
                "animations": [
                    {
                        "selector": "#shopSidebarButton",
                        "keyframes": [
                            { "opacity": "0", "visibility": "hidden", "transform": "translate(-50%, -30px)" }
                        ]
                    }
                ]
            }
        </script>
    </amp-animation>
	<?php
}