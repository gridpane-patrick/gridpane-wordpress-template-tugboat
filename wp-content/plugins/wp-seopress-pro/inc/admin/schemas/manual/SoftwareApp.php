<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_software($seopress_pro_rich_snippets_data, $key_schema = 0)
{
    $options_software = [
        ['value' => 'GameApplication', 'label' => __('GameApplication', 'wp-seopress-pro')],
        ['value' => 'SocialNetworkingApplication', 'label' => __('SocialNetworkingApplication', 'wp-seopress-pro')],
        ['value' => 'TravelApplication', 'label' => __('TravelApplication', 'wp-seopress-pro')],
        ['value' => 'ShoppingApplication', 'label' => __('ShoppingApplication', 'wp-seopress-pro')],
        ['value' => 'SportsApplication', 'label' => __('SportsApplication', 'wp-seopress-pro')],
        ['value' => 'LifestyleApplication', 'label' => __('LifestyleApplication', 'wp-seopress-pro')],
        ['value' => 'BusinessApplication', 'label' => __('BusinessApplication', 'wp-seopress-pro')],
        ['value' => 'DesignApplication', 'label' => __('DesignApplication', 'wp-seopress-pro')],
        ['value' => 'DeveloperApplication', 'label' => __('DeveloperApplication', 'wp-seopress-pro')],
        ['value' => 'DriverApplication', 'label' => __('DriverApplication', 'wp-seopress-pro')],
        ['value' => 'EducationalApplication', 'label' => __('EducationalApplication', 'wp-seopress-pro')],
        ['value' => 'HealthApplication', 'label' => __('HealthApplication', 'wp-seopress-pro')],
        ['value' => 'FinanceApplication', 'label' => __('FinanceApplication', 'wp-seopress-pro')],
        ['value' => 'SecurityApplication', 'label' => __('SecurityApplication', 'wp-seopress-pro')],
        ['value' => 'BrowserApplication', 'label' => __('BrowserApplication', 'wp-seopress-pro')],
        ['value' => 'CommunicationApplication', 'label' => __('CommunicationApplication', 'wp-seopress-pro')],
        ['value' => 'DesktopEnhancementApplication', 'label' => __('DesktopEnhancementApplication', 'wp-seopress-pro')],
        ['value' => 'EntertainmentApplication', 'label' => __('EntertainmentApplication', 'wp-seopress-pro')],
        ['value' => 'MultimediaApplication', 'label' => __('MultimediaApplication', 'wp-seopress-pro')],
        ['value' => 'HomeApplication', 'label' => __('HomeApplication', 'wp-seopress-pro')],
        ['value' => 'UtilitiesApplication', 'label' => __('UtilitiesApplication', 'wp-seopress-pro')],
        ['value' => 'ReferenceApplication', 'label' => __('ReferenceApplication', 'wp-seopress-pro')],
    ];

    $seopress_pro_rich_snippets_softwareapp_name                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_name'] : '';
    $seopress_pro_rich_snippets_softwareapp_os                      = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_os']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_os'] : '';
    $seopress_pro_rich_snippets_softwareapp_cat                     = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_cat']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_cat'] : '';
    $seopress_pro_rich_snippets_softwareapp_price                   = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_price']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_price'] : '';
    $seopress_pro_rich_snippets_softwareapp_currency                = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_currency']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_currency'] : '';
    $seopress_pro_rich_snippets_softwareapp_rating                  = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_rating']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_rating'] : '';
    $seopress_pro_rich_snippets_softwareapp_max_rating              = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_max_rating']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_softwareapp_max_rating'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-software-app">
    <div class="seopress-notice">
        <p>
            <?php _e('Mark up software application information so that Google can provide detailed service information in rich Search results.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_name_meta">
            <?php _e('Software name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_softwareapp_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_name]"
            placeholder="<?php echo esc_html__('The name of your app', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('App name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_os_meta">
            <?php _e('Operating system', 'wp-seopress-pro'); ?>'</label>
        <input type="text" id="seopress_pro_rich_snippets_softwareapp_os_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_os]"
            placeholder="<?php echo esc_html__('The operating system(s) required to use the app', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Operating system', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_os; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_cat_meta">
            <?php _e('Application category', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_softwareapp_cat_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_cat]">
            <?php foreach ($options_software as $item) { ?>
            <option <?php selected($item['value'], $seopress_pro_rich_snippets_softwareapp_cat); ?>value="<?php echo $item['value']; ?>">
                <?php echo $item['label']; ?>
            </option>
            <?php } ?>

        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_price_meta">
            <?php _e('Price of your app', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_softwareapp_price_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_price]"
            placeholder="<?php echo esc_html__('The price of your app (set "0" if the app is free of charge)', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Price', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_price; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_currency_meta">
            <?php _e('Currency', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_softwareapp_currency_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_currency]"
            placeholder="<?php echo esc_html__('Currency: USD, EUR...', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Currency', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_currency; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_rating_meta">
            <?php _e('Your rating', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_softwareapp_rating_meta" min="1" step="0.1"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_rating]"
            placeholder="<?php echo esc_html__('The item rating', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Your rating', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_rating; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_softwareapp_max_rating_meta">
            <?php _e('Max best rating', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_softwareapp_max_rating_meta" min="1" step="0.1"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_softwareapp_max_rating]"
            placeholder="<?php echo esc_html__('Max best rating', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Max best rating', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_softwareapp_max_rating; ?>" />
    </p>
</div>
<?php
}
