<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;
use SEOPressPro\Helpers\Schemas\Currencies;

class FormSchemaEvent extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_events_type':
            case '_seopress_pro_rich_snippets_events_offers_cat':
            case '_seopress_pro_rich_snippets_events_offers_price_currency':
            case '_seopress_pro_rich_snippets_events_offers_availability':
            case '_seopress_pro_rich_snippets_events_status':
            case '_seopress_pro_rich_snippets_events_attendance_mode':
                return 'select';
            case '_seopress_pro_rich_snippets_events_desc':
                return 'textarea';
            case '_seopress_pro_rich_snippets_events_img':
                return 'upload';
            case '_seopress_pro_rich_snippets_events_start_date':
            case '_seopress_pro_rich_snippets_events_end_date':
            case '_seopress_pro_rich_snippets_events_previous_start_date':
            case '_seopress_pro_rich_snippets_events_offers_valid_from_date':
                return 'date';
            case '_seopress_pro_rich_snippets_events_start_time':
            case '_seopress_pro_rich_snippets_events_end_time':
                return 'time';
            case '_seopress_pro_rich_snippets_events_name':
            case '_seopress_pro_rich_snippets_events_start_date_timezone':
            case '_seopress_pro_rich_snippets_events_previous_start_time':
            case '_seopress_pro_rich_snippets_events_location_name':
            case '_seopress_pro_rich_snippets_events_location_url':
            case '_seopress_pro_rich_snippets_events_location_address':
            case '_seopress_pro_rich_snippets_events_offers_name':
            case '_seopress_pro_rich_snippets_events_offers_price':
            case '_seopress_pro_rich_snippets_events_offers_valid_from_time':
            case '_seopress_pro_rich_snippets_events_offers_url':
            case '_seopress_pro_rich_snippets_events_performer':
            case '_seopress_pro_rich_snippets_events_organizer_name':
            case '_seopress_pro_rich_snippets_events_organizer_url':
                return 'input';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_events_type':
                return __('Select your event type', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_name':
                return __('Event name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_desc':
                return __('Event description (default excerpt, or beginning of the content)', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_img':
                return __('Image thumbnail', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_date':
                return __('Start date', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_date_timezone':
                return __('Timezone', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_time':
                return __('Start time', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_end_date':
                return __('End date', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_end_time':
                return __('End time', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_previous_start_date':
                return __('Previous start date', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_previous_start_time':
                return __('Previous start time', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_name':
                return __('Location name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_url':
                return __('Location Website', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_address':
                return __('Location Address', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_name':
                return __('Offer name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_cat':
                return __('Select your offer category', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_price':
                return __('Price', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_price_currency':
                return __('Select your currency', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_availability':
                return __('Availability', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_valid_from_date':
                return __('Valid From', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_valid_from_time':
                return __('Time', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_url':
                return __('Website to buy tickets', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_performer':
                return __('Performer name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_organizer_name':
                return __('Organizer name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_organizer_url':
                return __('Organizer URL', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_status':
                return __('Select your event status', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_attendance_mode':
                return __('Select your event attendance mode', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_events_name':
                return __('The name of your event', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_desc':
                return __('Enter your event description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_img':
                return __('Select your image', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_date':
                return __('Eg: YYYY-MM-DD', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_date_timezone':
                return __('Timezone start date', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_start_time':
                return __('Eg: HH:MM', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_end_date':
                return __('Eg: YYYY-MM-DD', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_end_time':
                return __('Eg: HH:MM', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_previous_start_date':
                return __('Eg: YYYY-MM-DD', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_previous_start_time':
                return __('Eg: HH:MM', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_name':
                return __('Eg: Hotel du Palais', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_url':
                return __('Eg: http://www.hotel-du-palais.com/', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_location_address':
                return __("Eg: 1 Avenue de l'Imperatrice, 64200 Biarritz", 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_name':
                return __('Eg: General admission', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_price':
                return __('Eg: 10', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_url':
                return __('Eg: https://fnac.com/', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_performer':
                return __('Eg: Lana Del Rey', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_organizer_name':
                return __('Eg: Apple', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_organizer_url':
                return __('Eg: https://www.apple.com/apple-events/', 'wp-seopress-pro');
        }
    }

    protected function getDescriptionByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_events_offers_valid_from_date':
                return __('The date when tickets go on sale', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_valid_from_time':
                return __('The time when tickets go on sale', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_offers_price':
                return __('The lowest available price, including service charges and fees, of this type of ticket.', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_events_img':
                return __('Minimum width: 720px - Recommended size: 1920px - .jpg, .png, or. gif format - crawlable and indexable', 'wp-seopress-pro');
        }
    }

    protected function getOptions($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_events_type':
                return [['value' => 'BusinessEvent', 'label' => __('Business Event', 'wp-seopress-pro')],
                    ['value' => 'ChildrensEvent',  'label' => __('Children\'s Event', 'wp-seopress-pro')],
                    ['value' => 'ComedyEvent',  'label' => __('Comedy Event', 'wp-seopress-pro')],
                    ['value' => 'CourseInstance',  'label' => __('Course Instance', 'wp-seopress-pro')],
                    ['value' => 'DanceEvent',  'label' => __('Dance Event', 'wp-seopress-pro')],
                    ['value' => 'DeliveryEvent',  'label' => __('Delivery Event', 'wp-seopress-pro')],
                    ['value' => 'EducationEvent',  'label' => __('Education Event', 'wp-seopress-pro')],
                    ['value' => 'ExhibitionEvent',  'label' => __('Exhibition Event', 'wp-seopress-pro')],
                    ['value' => 'Festival',  'label' => __('Festival', 'wp-seopress-pro')],
                    ['value' => 'FoodEvent',  'label' => __('Food Event', 'wp-seopress-pro')],
                    ['value' => 'LiteraryEvent',  'label' => __('Literary Event', 'wp-seopress-pro')],
                    ['value' => 'MusicEvent',  'label' => __('Music Event', 'wp-seopress-pro')],
                    ['value' => 'PublicationEvent',  'label' => __('Publication Event', 'wp-seopress-pro')],
                    ['value' => 'SaleEvent',  'label' => __('Sale Event', 'wp-seopress-pro')],
                    ['value' => 'ScreeningEvent',  'label' => __('Screening Event', 'wp-seopress-pro')],
                    ['value' => 'SocialEvent',  'label' => __('Social Event', 'wp-seopress-pro')],
                    ['value' => 'SportsEvent',  'label' => __('Sports Event', 'wp-seopress-pro')],
                    ['value' => 'TheaterEvent',  'label' => __('Theater Event', 'wp-seopress-pro')],
                    ['value' => 'VisualArtsEvent',  'label' => __('Visual Arts Event', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_events_offers_cat':
                return [
                    ['value' => 'Primary',  'label' => __('Primary', 'wp-seopress-pro')],
                    ['value' => 'Secondary',  'label' => __('Secondary', 'wp-seopress-pro')],
                    ['value' => 'Presale',  'label' => __('Presale', 'wp-seopress-pro')],
                    ['value' => 'Premium',  'label' => __('Premium', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_events_offers_price_currency':
                return Currencies::getOptions();
            case '_seopress_pro_rich_snippets_events_offers_availability':
                return [
                    ['value' => 'InStock', 'label' => __('In Stock', 'wp-seopress-pro')],
                    ['value' => 'SoldOut', 'label' => __('Sold Out', 'wp-seopress-pro')],
                    ['value' => 'PreOrder', 'label' => __('Pre Order', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_events_status':
                return [
                    ['value' => 'none', 'label' => __('Select a status event', 'wp-seopress-pro')],
                    ['value' => 'EventCancelled', 'label' => __('Event cancelled', 'wp-seopress-pro')],
                    ['value' => 'EventMovedOnline', 'label' => __('Event moved online', 'wp-seopress-pro')],
                    ['value' => 'EventPostponed', 'label' => __('Event postponed', 'wp-seopress-pro')],
                    ['value' => 'EventRescheduled', 'label' => __('Event rescheduled', 'wp-seopress-pro')],
                    ['value' => 'EventScheduled', 'label' => __('Event scheduled', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_events_attendance_mode':
                return [
                    ['value' => 'none', 'label' => __('Select your event attendance mode', 'wp-seopress-pro')],
                    ['value' => 'OfflineEventAttendanceMode', 'label' => __('Offline event', 'wp-seopress-pro')],
                    ['value' => 'OnlineEventAttendanceMode', 'label' => __('Online event', 'wp-seopress-pro')],
                    ['value' => 'MixedEventAttendanceMode', 'label' => __('Mixed event', 'wp-seopress-pro')],
                ];
                break;
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_events_type',
                'value' => 'BusinessEvent',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_desc',
                'class' => 'seopress-textarea-high-size'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_img',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_start_date',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_start_date_timezone',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_start_time',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_end_date',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_end_time',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_previous_start_date',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_previous_start_time',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_location_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_location_url',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_location_address',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_cat',
                'value' => 'Primary',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_price',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_price_currency',
                'value' => 'none'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_availability',
                'value' => 'InStock'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_valid_from_date',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_valid_from_time',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_offers_url',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_performer',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_organizer_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_organizer_url',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_status',
                'value' => 'none',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_events_attendance_mode',
                'value' => 'none'
            ],
        ];
    }
}
