<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "Generator" for 8theme dashboard.
 *
 * @since   7.0.0
 * @version 1.0.0
 */

$max_size = 0;

$Etheme_Generator = new Etheme_Generator;
$data = $Etheme_Generator->get_data();

ob_start();
    $subsections = $Etheme_Generator->generate_section($data['response']['css'], 'opt-section','input-section  ', true);
    $max_size = $max_size + $subsections['max_size'];
$css_modules = ob_get_clean();

?>
<div class="etheme-generator-section">
    <div class="etheme-generator-head">
        <h1>Files Generator</h1>
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="240" height="240" class="counter">
            <defs>
                <linearGradient id="circle-gradient" x1="0%" y1="0%" x2="100%" y2="20%">
                    <stop offset="0%" style="stop-color: #ff4301;"></stop>
                    <stop offset="100%" style="stop-color: #79d70f;"></stop>
                </linearGradient>
            </defs>
            <circle cx="120" cy="120" r="110" stroke="#e1e1e1" stroke-width="16" fill="#fff" style="stroke-dashoffset: 0; stroke-dasharray: 480px 1000px; stroke-linecap: round;" transform="rotate(145, 120 120)"></circle>
            <circle cx="120" cy="120" r="110" stroke="url(#circle-gradient)" class="counter-size" data-max_size="<?php echo esc_html($max_size); ?>" data-size="0" data-stroke-dasharray-start="480" data-stroke-dasharray-end="1000" stroke-width="16" fill="transparent" style="transition: all 0.7s ease; stroke-dashoffset: 0; stroke-dasharray: 480px 1000px; stroke-linecap: round;"
                    transform="rotate(145, 120 120)"></circle>
            <text fill="#222" font-size="46" x="120" y="130" class="counter-value" font-family="Arial" text-anchor="middle">~<?php echo esc_html($max_size); ?>Kb</text>
            <text fill="#222" font-size="50" x="120" y="130" class="counter-value-percent" font-family="Arial" text-anchor="middle">~100%</text>
            <text fill="#888" font-size="24" x="124" y="168" class="counter-value-suffix" font-family="Arial" text-anchor="middle">file size
            </text>
            <text fill="#7f8ba2" font-size="22" x="23" y="235"><?php echo esc_html__('Max file size: ', 'xstore') . $max_size . 'KB'; ?></text>

        </svg>
    </div>
	<div class="et-col-12 etheme-css-generator">
		<?php
            $generated_css_js = get_option('etheme_generated_css_js');
            $empty_file = false;
            $text = '';
            if ( isset($generated_css_js['css']) ){
                if ( isset($generated_css_js['css']['is_enabled']) && $generated_css_js['css']['is_enabled'] ){
                    if ( file_exists ($generated_css_js['css']['path']) ){
                        ob_start();
                        ?>
                        <h2><?php echo esc_html__('Latest generated file: ', 'xstore'); ?></h2>
                        <p>
                            <?php echo '<strong>' . esc_html__('File location:', 'xstore') . ' </strong>' . $generated_css_js['css']['path']; ?>
                        </p>
                        <p>
                            <?php echo '<strong>' . esc_html__('File size:', 'xstore') . ' </strong>' . $Etheme_Generator->file_size(filesize($generated_css_js['css']['path'])); ?>
                        </p>
                        <?php
                        $text = ob_get_clean();
                    } else {
                        $empty_file = true;
                    }
                } else {
                    $empty_file = true;
                }
            } else {
                $empty_file = true;
            }
            if ( $empty_file ) {
                $text = '';// '<p class="et-message et-info">' . esc_html__('There is no generated file yet.', 'xstore') . '</p>';
            }
		    
            if ( isset($generated_css_js['css']['is_enabled']) && $generated_css_js['css']['is_enabled'] ):
                $text .= '<span class="et-css-remove et-generated-remove"><span class="dashicons dashicons-trash"></span>' .  esc_html__('Remove', 'xstore') . '</span>';
            endif;
		
		echo '<p class="et-message et-info">' .
                esc_html__('Important: The File generator option is created to speed up the site performance and avoid calling the styles of elements that you don\'t use on your site. Use this tool if you are an advanced user ( it may cause style issues on the frontend )', 'xstore') .
             '</p>' .
             '<div class="et_info-block css_generator-info '.($empty_file ? ' no-file-generated' : '').'">' .
                $text .
             '</div>';
        
        ?>

		<form class="et_generator-form" action="" type="css">
            <?php echo '<div class="et_generator-form-inner">' . $css_modules . '</div>'; ?>
            <div class="etheme-generator-footer">
                <button type="submit" class="et-button et-button-green no-loader"><?php echo esc_html__('Generate', 'xstore'); ?></button>
            </div>
		</form>

	</div>
	<div class="et-col-5 et-col-6 etheme-js-generator" style="width: 50%; display: none">
		<h3>js-generator</h3>
        <form class="et_generator-form" action="" type="js">
            <p>Please select your preferred js</p>
            <div>
                <input type="checkbox" id="test"
                       name="js-part" value="testr">
                <label for="old-header">test</label>

                <input type="checkbox" id="test2"
                       name="js-part" value="test2"
                       checked>
                <label for="builders-global">test2</label>
            </div>
            <div>
                <button type="submit"><?php echo esc_html__('Generate', 'xstore'); ?></button>
            </div>
        </form>
	</div>
</div>
