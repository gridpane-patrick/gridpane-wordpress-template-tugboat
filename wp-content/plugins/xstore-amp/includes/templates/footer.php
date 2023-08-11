<?php
/**
 * Footer template
 *
 * @package    footer.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.1
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_vars, $xstore_amp_settings;

do_action( 'xstore_amp_after_main_content' );
?>
</main>
<footer>
    <div class="footer-top">
        <div class="amp-container">
            <div>
                <?php if ( isset($xstore_amp_settings['footer']['title']) ) {
                    echo '<p class="widget-title">' . $xstore_amp_settings['footer']['title'] . '</p>';
                }
                else {
                    echo '<p class="widget-title">'.esc_html__('Find us:', 'xstore-amp').'</p>';
                } ?>
	            <?php if ( isset($xstore_amp_settings['footer']['content']) ) {
		            echo do_shortcode(stripslashes($xstore_amp_settings['footer']['content']));
	            }
	            else { ?>
                    <p style="margin-bottom: 10px;"><i class="et-icon et-internet" style="margin-right: 5px;"></i> East 21st Street / 304 New York</p>
                    <p style="margin-bottom: 10px;"><i class="et-icon et-message" style="margin-right: 5px;"></i> Email: youremail@site.com</p>
                    <p style="margin-bottom: 10px;"><i class="et-icon et-phone-call" style="margin-right: 5px;"></i> Phone: +1 408 996 1010</p>
	            <?php } ?>
            </div>
			<?php
			 
				$socials = array();
				if ( isset($xstore_amp_settings['footer']['socials']) && !empty($xstore_amp_settings['footer']['socials']) ) {
                    $socials_options = explode( ',', $xstore_amp_settings['footer']['socials'] );
                    foreach ( $socials_options as $social ) {
//							if ( !$xstore_amp_settings['home_page'][$social.'_image'] ) continue;
                        $socials[ $social ] = array(
                            'social' => str_replace('et_icon-', '', $xstore_amp_settings['footer'][$social.'_name']),
                            'link' => $xstore_amp_settings['footer'][$social.'_link']??'#',
                        );
                    }
				}
			$socials_rendered = array(
				'facebook'  => '#',
				'twitter'   => '#',
				'instagram' => '#',
			);
			
			if ( count($socials)) {
				$socials_rendered = array();
                foreach ( $socials as $social ) {
	                $socials_rendered[$social['social']] = $social['link'];
                }
			}
			
			$Follow = ETC\App\Controllers\Shortcodes\Follow::get_instance();
			
			echo $Follow->follow_shortcode(
                    $socials_rendered
            );
			
			?>
        </div>
    </div>
	<?php if ( !isset($xstore_amp_settings['footer']['copyrights_content']) ) {
		$xstore_amp_settings['footer']['copyrights_content'] = esc_html__('Ⓒ Created by 8theme - Power Elite ThemeForest Author.', 'xstore-amp');
	} ?>
    <?php if ( !empty($xstore_amp_settings['footer']['copyrights_content'])) { ?>
        <div class="copyrights">
            <div class="amp-container">
                <?php echo do_shortcode(stripslashes($xstore_amp_settings['footer']['copyrights_content'])); ?>
            </div>
        </div>
    <?php } ?>
    <?php if (!isset($xstore_amp_settings['footer']['link_no_amp']) || $xstore_amp_settings['footer']['link_no_amp']) {
	    global $wp; ?>
        <div class="text-center no-amp-link-section">
            <a class="no-amp-link" href="<?php echo esc_url(remove_query_arg('amp', add_query_arg('no-amp', 'true', home_url( $wp->request )))); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 24 24" width="1em" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/></svg>
                <span><?php echo esc_html__('View non-AMP version', 'xstore-amp'); ?></span>
            </a>
        </div>
    <?php } ?>
</footer>

<?php do_action( 'xstore_amp_after_footer', $this ); ?>

<amp-install-serviceworker src="<?php echo preg_replace('#^https?:#', '', XStore_AMP_URL); ?>extensions/sw/sw.js" data-iframe-src="<?php echo preg_replace('#^https?:#', '', XStore_AMP_URL); ?>extensions/sw/sw.html" layout="nodisplay"></amp-install-serviceworker>
</body>
</html>