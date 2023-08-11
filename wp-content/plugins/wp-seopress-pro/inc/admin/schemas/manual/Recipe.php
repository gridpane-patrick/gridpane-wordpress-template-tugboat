<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_recipe($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_recipes_name = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_name'] : '';
    $seopress_pro_rich_snippets_recipes_desc = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_desc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_desc'] : '';
    $seopress_pro_rich_snippets_recipes_cat = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cat']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cat'] : '';
    $seopress_pro_rich_snippets_recipes_img = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_img'] : '';
    $seopress_pro_rich_snippets_recipes_video = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_video']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_video'] : '';
    $seopress_pro_rich_snippets_recipes_prep_time = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_prep_time']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_prep_time'] : '';
    $seopress_pro_rich_snippets_recipes_cook_time = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cook_time']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cook_time'] : '';
    $seopress_pro_rich_snippets_recipes_calories = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_calories']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_calories'] : '';
    $seopress_pro_rich_snippets_recipes_yield = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_yield']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_yield'] : '';
    $seopress_pro_rich_snippets_recipes_keywords = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_keywords']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_keywords'] : '';
    $seopress_pro_rich_snippets_recipes_cuisine = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cuisine']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_cuisine'] : '';
    $seopress_pro_rich_snippets_recipes_ingredient = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_ingredient']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_ingredient'] : '';
    $seopress_pro_rich_snippets_recipes_instructions = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_instructions']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_recipes_instructions'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-recipes">
    <div class="seopress-notice">
        <p>
            <?php _e('Mark up your recipe content with structured data to provide rich cards and host-specific lists for your recipes, such as cooking and preparation times, nutrition information...', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <div class="seopress-notice is-warning">
        <ul class="advice seopress-list">
            <li><?php _e('Use recipe markup for content about preparing a particular dish. For example, "facial scrub" or "party ideas" are not valid names for a dish.', 'wp-seopress-pro'); ?>
            </li>
        </ul>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_name_meta">
            <?php _e('Recipe name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_name]"
            placeholder="<?php echo esc_html__('The name of your dish', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_desc_meta">
            <?php _e('Short recipe description', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_recipes_desc_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_desc]"
            placeholder="<?php echo esc_html__('A short summary describing the dish.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Short recipe description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_recipes_desc; ?></textarea>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cat_meta">
            <?php _e('Recipe category', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_cat_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_cat]"
            placeholder="<?php echo esc_html__('Eg: appetizer, entree, or dessert', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe category', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_cat; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_img_meta">
            <?php _e('Image', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Minimum size: 185px by 185px, aspect ratio 1:1', 'wp-seopress-pro'); ?></span>
        <input id="seopress_pro_rich_snippets_recipes_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Image', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_img; ?>" />
        <input id="seopress_pro_rich_snippets_recipes_img"
            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_video_meta">
            <?php _e('Video URL of the recipe', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_video_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_video]"
            placeholder="<?php echo esc_html__('Eg: https://www.youtube.com/watch?v=n-a2U4_anWA', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Video URL of the recipe', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_video; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_prep_time_meta">
            <?php _e('Preparation time (in minutes)', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_recipes_prep_time_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_prep_time]"
            placeholder="<?php echo esc_html__('Eg: 30 min', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Preparation time (in minutes)', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_prep_time; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cook_time_meta">
            <?php _e('Cooking time (in minutes)', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_recipes_cook_time_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_cook_time]"
            placeholder="<?php echo esc_html__('Eg: 45 min', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Cooking time (in minutes)', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_cook_time; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_calories_meta">
            <?php _e('Calories', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_recipes_calories_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_calories]"
            placeholder="<?php echo esc_html__('Number of calories', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Calories', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_calories; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_yield_meta">
            <?php _e('Recipe yield', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_yield_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_yield]"
            placeholder="<?php echo esc_html__('Eg: number of people served, or number of servings', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe yield', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_yield; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_keywords_meta">
            <?php _e('Keywords', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_keywords_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_keywords]"
            placeholder="<?php echo esc_html__('Eg: winter apple pie, nutmeg crust (NOT recommended: dessert, American)', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Keywords', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_keywords; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cuisine_meta">
            <?php _e('Recipe cuisine', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_recipes_cuisine_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_cuisine]"
            placeholder="<?php echo esc_html__('The region associated with your recipe. For example, "French", Mediterranean", or "American"', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe cuisine', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_recipes_cuisine; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_ingredient_meta">
            <?php _e('Recipe ingredients', 'wp-seopress-pro'); ?>
        </label>
        <textarea rows="12" id="seopress_pro_rich_snippets_recipes_ingredient_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_ingredient]"
            placeholder="<?php echo esc_html__('Ingredients used in the recipe. One ingredient per line. Include only the ingredient text that is necessary for making the recipe. Don\'t include unnecessary information, such as a definition of the ingredient.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe ingredients', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_recipes_ingredient; ?></textarea>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_instructions_meta">
            <?php _e('Recipe instructions', 'wp-seopress-pro'); ?>
        </label>
        <textarea rows="12" id="seopress_pro_rich_snippets_recipes_instructions_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_recipes_instructions]"
            placeholder="<?php echo esc_html__('eg: Heat oven to 425°F. Include only text on how to make the recipe and don\'t include other text such as "Directions", "Watch the video", "Step 1".', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Recipe instructions', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_recipes_instructions; ?></textarea>
    </p>
</div>
<?php
}
