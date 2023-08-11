<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Local Business JSON-LD
function seopress_automatic_rich_snippets_lb_option($schema_datas) {
    $lb_name 							            = $schema_datas['name'];
    $lb_type 							            = $schema_datas['type'];
    $lb_img 							             = $schema_datas['img'];
    $lb_street_addr 					       = $schema_datas['street_addr'];
    $lb_city 							            = $schema_datas['city'];
    $lb_state 							           = $schema_datas['state'];
    $lb_pc 								             = $schema_datas['pc'];
    $lb_country 						          = $schema_datas['country'];
    $lb_lat 							             = $schema_datas['lat'];
    $lb_lon 							             = $schema_datas['lon'];
    $lb_website 						          = $schema_datas['website'];
    $lb_tel 							             = $schema_datas['tel'];
    $lb_price 							           = $schema_datas['price'];
    $lb_serves_cuisine 					    = $schema_datas['serves_cuisine'];
    $lb_menu 					              = $schema_datas['menu'];
    $lb_accepts_reservations 			= $schema_datas['accepts_reservations'];
    $lb_opening_hours 					     = $schema_datas['opening_hours'];

    if ('' == $lb_name) {
        $lb_name = get_bloginfo('name');
    }

    if ('' == $lb_type) {
        $lb_type = 'LocalBusiness';
    }

    if ('' == $lb_menu && '' != $lb_website) {
        $lb_menu = $lb_website;
    }

    $json = [
        '@context'   => seopress_check_ssl() . 'schema.org',
        '@type'      => $lb_type,
        'name'       => $lb_name,
        'image'      => $lb_img,
        'url'        => $lb_website,
        'telephone'  => $lb_tel,
        'priceRange' => $lb_price,
    ];

    if (function_exists('seopress_pro_rich_snippets_lb_id_option') && '' != seopress_pro_rich_snippets_lb_id_option()) {
        $json['@id'] = seopress_pro_rich_snippets_lb_id_option();
    }

    if (isset($lb_street_addr) || isset($lb_city) || isset($lb_state) || isset($lb_pc) || isset($lb_country)) {
        $json['address'] = [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $lb_street_addr,
            'addressLocality' => $lb_city,
            'addressRegion'   => $lb_state,
            'postalCode'      => $lb_pc,
            'addressCountry'  => $lb_country,
        ];
    }

    if (isset($lb_lat) || isset($lb_lon)) {
        $json['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $lb_lat,
            'longitude' => $lb_lon,
        ];
    }

    if (isset($lb_serves_cuisine) &&
        (
            'FoodEstablishment' == $lb_type
            || 'Bakery' == $lb_type
            || 'BarOrPub' == $lb_type
            || 'Brewery' == $lb_type
            || 'CafeOrCoffeeShop' == $lb_type
            || 'FastFoodRestaurant' == $lb_type
            || 'IceCreamShop' == $lb_type
            || 'Restaurant' == $lb_type
            || 'Winery' == $lb_type
        )
    ) {
        $json['servesCuisine']                = $lb_serves_cuisine;
        $json['menu']                         = $lb_menu;
        $json['acceptsReservations']          = $lb_accepts_reservations;
    }

    if ('' != $lb_opening_hours) {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($lb_opening_hours as $key => $day) {//DAY
            if ( ! array_key_exists('open', $day)) {//CLOSED?
                foreach ($day as $keys => $ampm) {//AM/PM
                    $hours = [
                        '@type'     => 'OpeningHoursSpecification',
                        'dayOfWeek' => $days[$key],
                    ];
                    if (array_key_exists('open', $ampm)) {//OPEN?

                        foreach ($ampm as $_key => $value) {//HOURS
                            $i         = 0;
                            $full_time = null;
                            if ('start' == $_key) {//START AM/PM
                                foreach ($value as $__key => $time) {
                                    $full_time .= $time;
                                    if (0 === $i) {
                                        $full_time .= ':';
                                    }
                                    ++$i;
                                }
                                $hours['opens'][] = $full_time;
                            }
                            if ('end' == $_key) {//CLOSE AM/PM
                                foreach ($value as $__key => $time) {
                                    $full_time .= $time;
                                    if (0 === $i) {
                                        $full_time .= ':';
                                    }
                                    ++$i;
                                }
                                $hours['closes'][] = $full_time;
                            }
                        }
                    }

                    $json['openingHoursSpecification'][] = $hours;
                }
            }
        }
    }

    $json = array_filter($json);

    $json = apply_filters('seopress_schemas_auto_lb_json', $json);

    $json = '<script type="application/ld+json">' . json_encode($json) . '</script>' . "\n";

    $json = apply_filters('seopress_schemas_auto_lb_html', $json);

    echo $json;
}
