<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-events">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Events schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/event');
            ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_events_type_meta"><?php _e('Select your event type', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_type', ['default', 'events']); ?>
        <span class="description"><?php _e('<strong>Authorized values:</strong> "BusinessEvent", "ChildrensEvent", "ComedyEvent", "CourseInstance", "DanceEvent", "DeliveryEvent", "EducationEvent", "ExhibitionEvent", "Festival", "FoodEvent", "LiteraryEvent", "MusicEvent", "PublicationEvent", "SaleEvent", "ScreeningEvent", "SocialEvent", "SportsEvent", "TheaterEvent", "VisualArtsEvent"', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_name_meta">
            <?php _e('Event name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_name', ['default', 'events']); ?>
        <span class="description"><?php _e('The name of your event', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_desc_meta">
            <?php _e('Event description (default excerpt, or beginning of the content)', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_desc', ['default', 'events']); ?>
        <span class="description"><?php _e('Enter your event description', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_img_meta"><?php _e('Image thumbnail', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_img', ['image', 'events']); ?>
        <span class="description"><?php _e('Minimum width: 720px - Recommended size: 1920px -  .jpg, .png, or. gif format - crawlable and indexable', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_start_date_meta">
            <?php _e('Start date', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_start_date', ['date', 'events']); ?>
        <span class="description"><?php _e('Eg: YYYY-MM-DD', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_start_date_timezone_meta">
            <?php _e('Timezone start date', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_start_date_timezone', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: -4:00', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_start_time_meta">
            <?php _e('Start time', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_start_time', ['time', 'events']); ?>
        <span class="description"><?php _e('Eg: HH:MM', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_end_date_meta">
            <?php _e('End date', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_end_date', ['date', 'events']); ?>
        <span class="description"><?php _e('Eg: YYYY-MM-DD', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_end_time_meta">
            <?php _e('End time', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_end_time', ['time', 'events']); ?>
        <span class="description"><?php _e('Eg: HH:MM', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_previous_start_date_meta">
            <?php _e('Previous Start date', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_previous_start_date', ['date', 'events']); ?>
        <span class="description"><?php _e('Eg: YYYY-MM-DD', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_previous_start_time_meta">
            <?php _e('Previous Start time', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_previous_start_time', ['time', 'events']); ?>
        <span class="description"><?php _e('Eg: HH:MM', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_location_name_meta">
            <?php _e('Location name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_location_name', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: Hotel du Palais', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_location_url_meta">
            <?php _e('Location Website', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_location_url', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: http://www.hotel-du-palais.com/', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_location_address_meta">
            <?php _e('Location Address', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_location_address', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: 1 Avenue de l\'Imperatrice, 64200 Biarritz', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_name_meta">
            <?php _e('Offer name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_name', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: General admission', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_cat_meta"><?php _e('Select your offer category', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_cat', ['default', 'events']); ?>
        <span class="description"><?php _e('<strong>Authorized values: </strong>"Primary", "Secondary", "Presale", "Premium"', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_price_meta">
            <?php _e('Price', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_price', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: 10', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_price_currency_meta"><?php _e('Select your currency', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_price_currency', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: USD, EUR...', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_availability_meta"><?php _e('Availability', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_availability', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: InStock, SoldOut, PreOrder', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_rich_snippets_events_offers_valid_from_meta_date"><?php _e('Valid From', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_valid_from_date', ['date', 'events']); ?>
        <span class="description"><?php _e('The date when tickets go on sale', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_rich_snippets_events_offers_valid_from_meta_time"><?php _e('Time', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_valid_from_time', ['time', 'events']); ?>
        <span class="description"><?php _e('The time when tickets go on sale', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_offers_url_meta">
            <?php _e('Website to buy tickets', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_offers_url', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: https://fnac.com/', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_performer_meta">
            <?php _e('Performer name', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_performer', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: Lana Del Rey', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_organizer_name_meta">
            <?php _e('Organizer name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_organizer_name', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: Apple', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_organizer_url_meta">
            <?php _e('Organizer URL', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_organizer_url', ['default', 'events']); ?>
        <span class="description"><?php _e('Eg: https://www.apple.com/apple-events/', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_status_meta">
            <?php _e('Event status', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_status', ['default', 'events']); ?>
        <span class="description"><?php _e('<strong>Authorized values:</strong> "EventCancelled", "EventMovedOnline", "EventPostponed", "EventRescheduled", "EventScheduled"', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_events_attendance_mode_meta">
            <?php _e('Event attendance mode', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_events_attendance_mode', ['default', 'events']); ?>
        <span class="description"><?php _e('<strong>Authorized values:</strong> "OfflineEventAttendanceMode", "OnlineEventAttendanceMode", "MixedEventAttendanceMode"', 'wp-seopress-pro'); ?></span>
    </p>
</div>
