<?php

namespace WCPM\Classes\Pixels;

use WCPM\Classes\Pixels\Google\Google;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Shortcodes extends Pixel {

	use Trait_Product;

	private $google;

	public function __construct( $options ) {

		parent::__construct($options);

		$this->google = new Google($options);

		add_shortcode('view-item', [$this, 'wpm_view_item']);
		add_shortcode('conversion-pixel', [$this, 'wpm_conversion_pixel']);
	}

	public function wpm_view_item( $attributes ) {

		$shortcode_attributes = shortcode_atts(
			[
				'product-id' => null,
			], $attributes);

		if ($shortcode_attributes['product-id']) {

			$product = wc_get_product($shortcode_attributes['product-id']);

			if (!is_object($product)) {
				wc_get_logger()->debug('get_product_data_layer_script received an invalid product', ['source' => 'wpm']);
				return;
			}

			$this->get_product_data_layer_script($product, false, false);

			?>

			<script>
				jQuery(window).on("wpmLoad", function () {
					jQuery(document).trigger("wpmViewItem", wpm.getProductDetailsFormattedForEvent(<?php echo esc_js($shortcode_attributes['product-id']); ?>))
				})
			</script>
			<?php
		}
	}

	public function wpm_conversion_pixel( $attributes ) {

		$this->function_exists_script();

		$pairs = [
			'pixel'                 => 'all',
			'gads-conversion-id'    => $this->options_obj->google->ads->conversion_id,
			'gads-conversion-label' => '',
			'fbc-event'             => 'Lead',
			'twc-event'             => 'CompleteRegistration',
			'pinc-event'            => 'lead',
			'pinc-lead-type'        => '',
			'ms-ads-event'          => 'submit',
			'ms-ads-event-category' => '',
			'ms-ads-event-label'    => 'lead',
			'ms-ads-event-value'    => 0,
			'snap-event'            => 'SIGN_UP',
			'tiktok-event'          => 'SubmitForm',
		];

		$shortcode_attributes = shortcode_atts($pairs, $attributes);

		if ('google-ads' === $shortcode_attributes['pixel']) {
			if ($this->google->is_google_ads_active()) {
				$this->conversion_html_google_ads($shortcode_attributes);
			}
		} elseif ('facebook' === $shortcode_attributes['pixel'] || 'meta' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->facebook->pixel_id) {
				$this->conversion_html_facebook($shortcode_attributes);
			}
		} elseif ('twitter' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->twitter->pixel_id) {
				$this->conversion_html_twitter($shortcode_attributes);
			}
		} elseif ('pinterest' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->pinterest->pixel_id) {
				$this->conversion_html_pinterest($shortcode_attributes);
			}
		} elseif ('ms-ads' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->bing->uet_tag_id) {
				$this->conversion_html_microsoft_ads($shortcode_attributes);
			}
		} elseif ('snapchat' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->snapchat->pixel_id) {
				$this->conversion_html_snapchat($shortcode_attributes);
			}
		} elseif ('tiktok' === $shortcode_attributes['pixel']) {
			if ($this->options_obj->tiktok->pixel_id) {
				$this->conversion_html_tiktok($shortcode_attributes);
			}
		} elseif ('all' === $shortcode_attributes['pixel']) {
			if ($this->google->is_google_ads_active()) {
				$this->conversion_html_google_ads($shortcode_attributes);
			}
			if ($this->options_obj->facebook->pixel_id) {
				$this->conversion_html_facebook($shortcode_attributes);
			}
			if ($this->options_obj->twitter->pixel_id) {
				$this->conversion_html_twitter($shortcode_attributes);
			}
			if ($this->options_obj->pinterest->pixel_id) {
				$this->conversion_html_pinterest($shortcode_attributes);
			}
			if ($this->options_obj->bing->uet_tag_id) {
				$this->conversion_html_microsoft_ads($shortcode_attributes);
			}
			if ($this->options_obj->snapchat->pixel_id) {
				$this->conversion_html_snapchat($shortcode_attributes);
			}
			if ($this->options_obj->tiktok->pixel_id) {
				$this->conversion_html_tiktok($shortcode_attributes);
			}
		}
	}

	private function conversion_html_snapchat( $shortcode_attributes ) {

		?>

		<script>
			wpmFunctionExists("snaptr").then(function () {
					snaptr("track", '<?php echo esc_js($shortcode_attributes['snap-event']); ?>')
				},
			)
		</script>
		<?php
	}

	private function conversion_html_tiktok( $shortcode_attributes ) {

		?>

		<script>
			wpmFunctionExists("ttq").then(function () {
					ttq.track('<?php echo esc_js($shortcode_attributes['tiktok-event']); ?>')
				},
			)
		</script>
		<?php
	}

	private function conversion_html_google_ads( $shortcode_attributes ) {

		?>

		<script>
			wpmFunctionExists("gtag").then(function () {
					if (wpm.googleConfigConditionsMet("ads")) gtag("event", "conversion", {"send_to": 'AW-<?php echo esc_js($shortcode_attributes['gads-conversion-id']); ?>/<?php echo esc_js($shortcode_attributes['gads-conversion-label']); ?>'})
				},
			)
		</script>
		<?php
	}

	// https://developers.facebook.com/docs/analytics/send_data/events/
	private function conversion_html_facebook( $shortcode_attributes ) {

		if ($this->options_obj->facebook->capi->token) {
			?>

			<script>
				jQuery(window).on("wpmLoad", function () {

					let eventId = wpm.getRandomEventId()

					wpmFunctionExists("fbq").then(function () {
							fbq("track", '<?php echo esc_js($shortcode_attributes['fbc-event']); ?>', {}, {
								eventID: eventId,
							})
						},
					)

					jQuery(document).trigger("wpmFbCapiEvent", {
						event_name      : "<?php echo esc_js($shortcode_attributes['fbc-event']); ?>",
						event_id        : eventId,
						user_data       : wpm.getFbUserData(),
						event_source_url: window.location.href,
					})
				})

			</script>
			<?php
		} else {
			?>

			<script>
				wpmFunctionExists("fbq").then(function () {
						fbq("track", '<?php echo esc_js($shortcode_attributes['fbc-event']); ?>')
					},
				)
			</script>
			<?php
		}
	}

	// https://business.twitter.com/en/help/campaign-measurement-and-analytics/conversion-tracking-for-websites.html
	private function conversion_html_twitter( $shortcode_attributes ) {

		?>

		<script>
			wpmFunctionExists("twq").then(function () {
					twq("track", '<?php echo esc_js($shortcode_attributes['twc-event']); ?>')
				},
			)
		</script>
		<?php
	}

	// https://help.pinterest.com/en/business/article/track-conversions-with-pinterest-tag
	// https://help.pinterest.com/en/business/article/add-event-codes
	private function conversion_html_pinterest( $shortcode_attributes ) {

		if ('' === $shortcode_attributes['pinc-lead-type']) {
			?>

			<script>
				wpmFunctionExists("pintrk").then(function () {
						pintrk("track", '<?php echo esc_js($shortcode_attributes['pinc-event']); ?>')
					},
				)
			</script>
			<?php
		} else {
			?>

			<script>
				wpmFunctionExists("pintrk").then(function () {
						pintrk("track", '<?php echo esc_js($shortcode_attributes['pinc-event']); ?>', {
							lead_type: '<?php echo esc_js($shortcode_attributes['pinc-lead-type']); ?>',
						})
					},
				)
			</script>
			<?php
		}
	}

	// https://bingadsuet.azurewebsites.net/UETDirectOnSite_ReportCustomEvents.html
	private function conversion_html_microsoft_ads( $shortcode_attributes ) {
		?>

		<script>
			wpmFunctionExists("uetq").then(function () {
					window.uetq = window.uetq || []
					window.uetq.push("event", '<?php echo esc_js($shortcode_attributes['ms-ads-event']); ?>', {
						"event_category": '<?php echo esc_js($shortcode_attributes['ms-ads-event-category']); ?>',
						"event_label"   : '<?php echo esc_js($shortcode_attributes['ms-ads-event-label']); ?>',
						"event_value"   : '<?php echo esc_js($shortcode_attributes['ms-ads-event-value']); ?>',
					})
				},
			)
		</script>
		<?php
	}

	protected function function_exists_script() {
		?>

		<script>
			if (typeof wpmFunctionExists !== "function") {
				window.wpmFunctionExists = function (functionName) {
					return new Promise(function (resolve) {
						(function waitForVar() {
							if (typeof window[functionName] !== "undefined") return resolve()
							setTimeout(waitForVar, 1000)
						})()
					})
				}
			}
		</script>
		<?php
	}
}
