<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Service JSON-LD
function seopress_automatic_rich_snippets_services_option($schema_datas) {
    //if no data
    if (0 != count(array_filter($schema_datas, 'strlen'))) {
        //Init
        global $product;

        $service_name 							      = $schema_datas['name'];
        $service_type 							      = $schema_datas['type'];
        $service_desc 							      = $schema_datas['description'];
        $service_img 							       = $schema_datas['img'];
        $service_area 							      = $schema_datas['area'];
        $service_provider_name					= $schema_datas['provider_name'];
        $service_lb_img							     = $schema_datas['lb_img'];
        $service_provider_mob 					= $schema_datas['provider_mobility'];
        $service_slogan 						     = $schema_datas['slogan'];
        $service_street_addr 					 = $schema_datas['street_addr'];
        $service_city 							      = $schema_datas['city'];
        $service_state 							     = $schema_datas['state'];
        $service_postal_code 					 = $schema_datas['pc'];
        $service_country 						    = $schema_datas['country'];
        $service_lat							        = $schema_datas['lat'];
        $service_lon 							       = $schema_datas['lon'];
        $service_tel 							       = $schema_datas['tel'];
        $service_price 							     = $schema_datas['price'];

        $json = [
            '@context'         => seopress_check_ssl() . 'schema.org/',
            '@type'            => 'Service',
            'name'             => $service_name,
            'serviceType'      => $service_type,
            'description'      => $service_desc,
            'image'            => $service_img,
            'areaServed'       => $service_area,
            'providerMobility' => $service_provider_mob,
            'slogan'           => $service_slogan,
        ];

        if (function_exists('seopress_rich_snippets_articles_canonical_option') && '' != seopress_rich_snippets_articles_canonical_option()) {
            $json['@id'] = seopress_rich_snippets_articles_canonical_option();
        }

        if ('' != $service_provider_name) {
            $json['provider'] = [
                '@type'      => 'LocalBusiness',
                'name'       => $service_provider_name,
                'telephone'  => $service_tel,
                'image'      => $service_lb_img,
                'priceRange' => $service_price,
            ];

            if (isset($service_street_addr) || isset($service_city) || isset($service_state) || isset($service_postal_code) || isset($service_country)) {
                $json['provider']['address'] = [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $service_street_addr,
                    'addressLocality' => $service_city,
                    'addressRegion'   => $service_state,
                    'postalCode'      => $service_postal_code,
                    'addressCountry'  => $service_country,
                ];
            }

            if ('' != $service_lat || '' != $service_lon) {
                $json['provider']['geo'] = [
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => $service_lat,
                    'longitude' => $service_lon,
                ];
            }
        }

        if (isset($product) && true === comments_open(get_the_ID())) {
            //review
            $args = [
                'meta_key'    => 'rating',
                'number'      => 1,
                'status'      => 'approve',
                'post_status' => 'publish',
                'parent'      => 0,
                'orderby'     => 'meta_value_num',
                'order'       => 'DESC',
                'post_id'     => get_the_ID(),
                'post_type'   => 'product',
            ];

            $comments = get_comments($args);

            $json['review'] = [
                '@type'        => 'Review',
                'reviewRating' => [
                    '@type'       => 'Rating',
                    'ratingValue' => get_comment_meta($comments[0]->comment_ID, 'rating', true),
                ],
                'author' => [
                    '@type' => 'Person',
                    'name'  => get_comment_author($comments[0]->comment_ID),
                ],
            ];
        }

        if (isset($product) && $product->get_review_count() >= 1) {
            $json['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $product->get_average_rating(),
                'reviewCount' => $product->get_review_count(),
            ];
        }

        $json = array_filter($json);

        $json = apply_filters('seopress_schemas_auto_service_json', $json);

        $json = '<script type="application/ld+json">' . json_encode($json) . '</script>' . "\n";

        $json = apply_filters('seopress_schemas_auto_service_html', $json);

        echo $json;
    }
}
