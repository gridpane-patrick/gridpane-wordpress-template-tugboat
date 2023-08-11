<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-software-app">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Software App schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/software-app');
            ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_name_meta">
            <?php _e('Software name', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_name', 'default'); ?>
        <span class="description"><?php _e('The name of your app', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_os_meta">
            <?php _e('Operating system', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_os', 'default'); ?>
        <span class="description"><?php _e('The operating system(s) required to use the app', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_cat_meta">
            <?php _e('Application category', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_cat', 'default'); ?>
        <span class="description"><?php _e('<strong>Authorized values:</strong> "GameApplication", "SocialNetworkingApplication", "TravelApplication", "ShoppingApplication", "SportsApplication", "LifestyleApplication", "BusinessApplication", "DesignApplication", "DeveloperApplication", "DriverApplication", "EducationalApplication", "HealthApplication", "FinanceApplication", "SecurityApplication", "BrowserApplication", "CommunicationApplication", "DesktopEnhancementApplication", "EntertainmentApplication", "MultimediaApplication", "HomeApplication", "UtilitiesApplication", "ReferenceApplication"', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_price_meta">
            <?php _e('Price of your app', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_price', 'default'); ?>
        <span class="description"><?php _e('The price of your app (set "0" if the app is free of charge)', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_currency_meta">
            <?php _e('Currency', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_currency', 'default'); ?>
        <span class="description"><?php _e('Eg: USD, EUR...', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_rating_meta">
            <?php _e('Your rating', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_rating', 'rating'); ?>
        <span class="description"><?php _e('The item rating', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_max_rating_meta">
            <?php _e('Max best rating', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_softwareapp_max_rating', 'rating'); ?>
        <span class="description"><?php _e('Only required if your scale is different from 1 to 5.', 'wp-seopress-pro'); ?></span>
    </p>
</div>
