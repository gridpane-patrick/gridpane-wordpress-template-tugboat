<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_review($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_review_item                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_item']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_item'] : '';
    $seopress_pro_rich_snippets_review_item_type                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_item_type']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_item_type'] : '';
    $seopress_pro_rich_snippets_review_img                          = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_img'] : '';
    $seopress_pro_rich_snippets_review_rating                       = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_rating']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_rating'] : '';
    $seopress_pro_rich_snippets_review_max_rating                   = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_max_rating']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_max_rating'] : '';
    $seopress_pro_rich_snippets_review_body                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_body']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_review_body'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-review">
    <div class="seopress-notice">
        <p>
            <?php _e('A simple review about something. When Google finds valid reviews or ratings markup, they may show a rich snippet that includes stars and other summary info from reviews or ratings.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_review_item_meta">
            <?php _e('Review item name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_review_item_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_item]"
            placeholder="<?php echo esc_html__('The item name reviewed', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Review item name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_review_item; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_review_item_type_meta">
            <?php _e('Review item type', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_review_item_type_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_item_type]">
            <option <?php selected('CreativeWorkSeason', $seopress_pro_rich_snippets_review_item_type); ?>
                value="CreativeWorkSeason"><?php _e('CreativeWorkSeason', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('CreativeWorkSeries', $seopress_pro_rich_snippets_review_item_type); ?>
                value="CreativeWorkSeries"><?php _e('CreativeWorkSeries', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('Episode', $seopress_pro_rich_snippets_review_item_type); ?>
                value="Episode"><?php _e('Episode', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('Game', $seopress_pro_rich_snippets_review_item_type); ?>
                value="Game"><?php _e('Game', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('MediaObject', $seopress_pro_rich_snippets_review_item_type); ?>
                value="MediaObject"><?php _e('MediaObject', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('MusicPlaylist', $seopress_pro_rich_snippets_review_item_type); ?>
                value="MusicPlaylist"><?php _e('MusicPlaylist', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('MusicRecording', $seopress_pro_rich_snippets_review_item_type); ?>
                value="MusicRecording"><?php _e('MusicRecording', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('Organization', $seopress_pro_rich_snippets_review_item_type); ?>
                value="Organization"><?php _e('Organization', 'wp-seopress-pro'); ?>
            </option>
        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_review_img_meta">
            <?php _e('Review item image', 'wp-seopress-pro'); ?>
        </label>
        <input id="seopress_pro_rich_snippets_review_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Review item name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_review_img; ?>" />
        <input id="seopress_pro_rich_snippets_review_img" class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_review_rating_meta">
            <?php _e('Your rating', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_review_rating_meta" min="1" step="0.1"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_rating]"
            placeholder="<?php echo esc_html__('The item rating', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Your rating', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_review_rating; ?>" />
        <span class="description"><?php _e('Your rating: scale from 1 to 5.','wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_review_max_rating_meta">
            <?php _e('Max best rating', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_review_max_rating_meta" min="1" step="0.1"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_max_rating]"
            placeholder="<?php echo esc_html__('Max best rating', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Max best rating', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_review_max_rating; ?>" />
        <span class="description"><?php _e('Only required if your scale is different from 1 to 5.','wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_review_body_meta">
            <?php _e('Review body', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_review_body_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_review_body]"
            placeholder="<?php echo esc_html__('Enter your review body', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Review body', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_review_body; ?></textarea>
    </p>
</div>
<?php
}
