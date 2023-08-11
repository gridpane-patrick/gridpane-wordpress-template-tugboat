<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>
<div class="wrap-rich-snippets-recipes">
    <div class="seopress-notice">
        <p>
            <?php
                                                /* translators: %s: link documentation */
                                                printf(__('Learn more about the <strong>Recipe schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/recipe'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_name_meta">
            <?php _e('Recipe name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_name', 'default'); ?>
        <span class="description"><?php _e('The name of your dish', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_desc_meta"><?php _e('Short recipe description', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_desc', 'default'); ?>
        <span class="description"><?php _e('A short summary describing the dish.', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cat_meta">
            <?php _e('Recipe category', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_cat', 'default'); ?>
        <span class="description"><?php _e('Eg: appetizer, entree, or dessert', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_img_meta"><?php _e('Image', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_img', 'image'); ?>
        <span class="description"><?php _e('Minimum size: 185px by 185px, aspect ratio 1:1', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_video_meta"><?php _e('Video URL of the recipe', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_video', 'default'); ?>
        <span class="description"><?php _e('A video URL describing the recipe preparation', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_prep_time_meta">
            <?php _e('Preparation time (in minutes)', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_prep_time', 'default'); ?>
        <span class="description"><?php _e('Eg: 30 min', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cook_time_meta">
            <?php _e('Cooking time (in minutes)', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_cook_time', 'default'); ?>
        <span class="description"><?php _e('Eg: 45 min', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_calories_meta">
            <?php _e('Calories', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_calories', 'default'); ?>
        <span class="description"><?php _e('Number of calories', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_yield_meta">
            <?php _e('Recipe yield', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_yield', 'default'); ?>
        <span class="description"><?php _e('Eg: number of people served, or number of servings', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_keywords_meta">
            <?php _e('Keywords', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_keywords', 'default'); ?>
        <span class="description"><?php _e('Eg: winter apple pie, nutmeg crust (NOT recommended: dessert, American)', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_cuisine_meta">
            <?php _e('Recipe cuisine', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_cuisine', 'default'); ?>
        <span class="description"><?php _e('The region associated with your recipe. For example, "French", Mediterranean", or "American".', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_ingredient_meta">
            <?php _e('Recipe ingredients', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_ingredient', 'default'); ?>
        <span class="description"><?php _e('Ingredients used in the recipe. One ingredient per line. Include only the ingredient text that is necessary for making the recipe. Don\'t include unnecessary information, such as a definition of the ingredient.', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_recipes_instructions_meta">
            <?php _e('Recipe instructions', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_recipes_instructions', 'default'); ?>
        <span class="description"><?php _e('eg: Heat oven to 425°F. Include only text on how to make the recipe and don\'t include other text such as "Directions", "Watch the video", "Step 1".', 'wp-seopress-pro'); ?></span>
    </p>
</div>
