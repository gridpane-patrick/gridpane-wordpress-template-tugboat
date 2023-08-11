<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_all_pro_rich_snippets_manual($post_id) {
    return get_post_meta($post_id, '_seopress_pro_schemas_manual', true);
}

function seopress_pro_rich_snippets_is_multidimensional($post_id) {
    if (apply_filters('seopress_get_pro_schemas_manual', true)) {
        $seopress_pro_rich_snippets_data = seopress_get_all_pro_rich_snippets_manual($post_id);
    } else {
        $post = get_post($post_id);
        $seopress_pro_rich_snippets_data = seopress_get_rich_snippets_fallback($post);
    }

    $is_multidimensional = false;
    if (isset($seopress_pro_rich_snippets_data[0]) && is_array($seopress_pro_rich_snippets_data[0])) {
        $is_multidimensional = true;
    }

    return apply_filters('seopress_pro_rich_snippets_data_is_multidimensional', $is_multidimensional);
}

function seopress_get_pro_rich_snippets_by_key($post_id, $key, $key_number_schema = 0) {
    // SEOPress < 3.9
    if ( ! apply_filters('seopress_get_pro_schemas_manual', true, $post_id, $key)) {
        return get_post_meta($post_id, $key, true);
    }

    $schemas = seopress_get_all_pro_rich_snippets_manual($post_id);

    if ( ! $schemas) {
        return '';
    }

    $is_multidimensional = false;
    if (isset($schemas[0]) && is_array($schemas[0])) {
        $is_multidimensional = true;
    }

    $is_multidimensional = apply_filters('seopress_pro_rich_snippets_data_is_multidimensional', $is_multidimensional);

    // Before the management of multiple schemas
    if ( ! $is_multidimensional) {
        if ( ! array_key_exists($key, $schemas)) {
            return '';
        }
    }

    if ( ! array_key_exists($key_number_schema, $schemas)) {
        return '';
    }

    $schemas_number = $schemas[$key_number_schema];

    if ( ! array_key_exists($key, $schemas_number)) {
        return '';
    }

    return $schemas_number[$key];
}

//Rich Snippets
//=================================================================================================
//Rich Snippets JSON-LD

//Check if Type !='' or !='none'
if ('1' == seopress_rich_snippets_enable_option()) { //Is RS enable
    //Articles
    //=========================================================================================
    //Type
    function seopress_rich_snippets_articles_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_type) {
            return $_seopress_pro_rich_snippets_article_type;
        } else { //Default
            return 'NewsArticle';
        }
    }
    //Title
    function seopress_rich_snippets_articles_title_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_title = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_title', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_title) {
            return $_seopress_pro_rich_snippets_article_title;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Description
    function seopress_rich_snippets_articles_desc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_desc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_desc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_desc) {
            return $_seopress_pro_rich_snippets_article_desc;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Img
    function seopress_rich_snippets_articles_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_img) {
            return $_seopress_pro_rich_snippets_article_img;
        } elseif (has_post_thumbnail(get_the_ID())) {//Post thumbnail
            $_seopress_pro_rich_snippets_article_img = get_the_post_thumbnail_url(get_the_ID(), 'large');

            return $_seopress_pro_rich_snippets_article_img;
        }
    }
    //Img Width
    function seopress_rich_snippets_articles_img_width_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_img_width = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_img_width', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_img_width) {
            return $_seopress_pro_rich_snippets_article_img_width;
        } elseif (has_post_thumbnail(get_the_ID())) {//Post thumbnail
            $_seopress_pro_rich_snippets_article_img_width = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');

            return $_seopress_pro_rich_snippets_article_img_width[1];
        }
    }
    //Img Height
    function seopress_rich_snippets_articles_img_height_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_img_height = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_img_height', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_img_height) {
            return $_seopress_pro_rich_snippets_article_img_height;
        } elseif (has_post_thumbnail(get_the_ID())) {//Post thumbnail
            $_seopress_pro_rich_snippets_article_img_height = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');

            return $_seopress_pro_rich_snippets_article_img_height[2];
        }
    }
    //Canonical
    function seopress_rich_snippets_articles_canonical_option($key_schema = 0) {
        $_seopress_robots_canonical = get_post_meta(get_the_ID(), '_seopress_robots_canonical', true);
        if ('' != $_seopress_robots_canonical) {
            return $_seopress_robots_canonical;
        } else {
            global $wp;

            return home_url(add_query_arg([], $wp->request));
        }
    }
    //Person name
    function seopress_rich_snippets_articles_publisher_option($key_schema = 0) {
        $seopress_rich_snippets_articles_publisher_option = get_option('seopress_social_option_name');
        if ( ! empty($seopress_rich_snippets_articles_publisher_option)) {
            foreach ($seopress_rich_snippets_articles_publisher_option as $key => $seopress_rich_snippets_articles_publisher_value) {
                $options[$key] = $seopress_rich_snippets_articles_publisher_value;
            }
            if (isset($seopress_rich_snippets_articles_publisher_option['seopress_social_knowledge_name'])) {
                return $seopress_rich_snippets_articles_publisher_option['seopress_social_knowledge_name'];
            }
        }
    }
    //Logo
    function seopress_rich_snippets_articles_publisher_logo_option($key_schema = 0) {
        $seopress_rich_snippets_articles_publisher_logo_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_rich_snippets_articles_publisher_logo_option)) {
            foreach ($seopress_rich_snippets_articles_publisher_logo_option as $key => $seopress_rich_snippets_articles_publisher_logo_value) {
                $options[$key] = $seopress_rich_snippets_articles_publisher_logo_value;
            }
            if (isset($seopress_rich_snippets_articles_publisher_logo_option['seopress_rich_snippets_publisher_logo'])) {
                return $seopress_rich_snippets_articles_publisher_logo_option['seopress_rich_snippets_publisher_logo'];
            }
        }
    }
    //Logo width
    function seopress_rich_snippets_articles_publisher_logo_width_option($key_schema = 0) {
        $seopress_rich_snippets_articles_publisher_logo_width_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_rich_snippets_articles_publisher_logo_width_option)) {
            foreach ($seopress_rich_snippets_articles_publisher_logo_width_option as $key => $seopress_rich_snippets_articles_publisher_logo_width_value) {
                $options[$key] = $seopress_rich_snippets_articles_publisher_logo_width_value;
            }
            if (isset($seopress_rich_snippets_articles_publisher_logo_width_option['seopress_rich_snippets_publisher_logo_width'])) {
                return $seopress_rich_snippets_articles_publisher_logo_width_option['seopress_rich_snippets_publisher_logo_width'];
            }
        }
    }
    //Logo height
    function seopress_rich_snippets_articles_publisher_logo_height_option($key_schema = 0) {
        $seopress_rich_snippets_articles_publisher_logo_height_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_rich_snippets_articles_publisher_logo_height_option)) {
            foreach ($seopress_rich_snippets_articles_publisher_logo_height_option as $key => $seopress_rich_snippets_articles_publisher_logo_height_value) {
                $options[$key] = $seopress_rich_snippets_articles_publisher_logo_height_value;
            }
            if (isset($seopress_rich_snippets_articles_publisher_logo_height_option['seopress_rich_snippets_publisher_logo_height'])) {
                return $seopress_rich_snippets_articles_publisher_logo_height_option['seopress_rich_snippets_publisher_logo_height'];
            }
        }
    }
    //Start Coverage Date
    function seopress_pro_rich_snippets_article_coverage_start_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_coverage_start_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_coverage_start_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_coverage_start_date) {
            return $_seopress_pro_rich_snippets_article_coverage_start_date;
        }
    }
    //Start Coverage Time
    function seopress_pro_rich_snippets_article_coverage_start_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_coverage_start_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_coverage_start_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_coverage_start_time) {
            return $_seopress_pro_rich_snippets_article_coverage_start_time;
        }
    }
    //End Coverage Date
    function seopress_pro_rich_snippets_article_coverage_end_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_coverage_end_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_coverage_end_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_coverage_end_date) {
            return $_seopress_pro_rich_snippets_article_coverage_end_date;
        }
    }
    //End Coverage Time
    function seopress_pro_rich_snippets_article_coverage_end_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_article_coverage_end_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_article_coverage_end_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_article_coverage_end_time) {
            return $_seopress_pro_rich_snippets_article_coverage_end_time;
        }
    }

    function seopress_rich_snippets_articles_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				"@context": "' . seopress_check_ssl() . 'schema.org",';
        if ('' != seopress_rich_snippets_articles_type_option($key_schema)) {
            $html .= '"@type": ' . json_encode(seopress_rich_snippets_articles_type_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_articles_canonical_option($key_schema)) {
            $html .= '"mainEntityOfPage": {
						"@type": "WebPage",
						"@id": ' . json_encode(seopress_rich_snippets_articles_canonical_option($key_schema)) . '
					},';
        }
        if ('' != seopress_rich_snippets_articles_title_option($key_schema)) {
            $html .= '"headline": ' . json_encode(seopress_rich_snippets_articles_title_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_articles_img_option($key_schema)) {
            $html .= '"image": {
						"@type": "ImageObject",
						"url": ' . json_encode(seopress_rich_snippets_articles_img_option($key_schema)) . ',
						"height": ' . json_encode(seopress_rich_snippets_articles_img_width_option($key_schema)) . ',
						"width": ' . json_encode(seopress_rich_snippets_articles_img_height_option($key_schema)) . '
					},';
        }
        $html .= '"datePublished": "' . get_the_date('c') . '",
				"dateModified": ' . json_encode(get_the_modified_date('c')) . ',
				"author": {
					"@type": "Person",
					"name": ' . json_encode(get_the_author()) . '
				},';

        if ('' != seopress_rich_snippets_articles_publisher_option($key_schema)) {
            $html .= '"publisher": {
						"@type": "Organization",
						"name": ' . json_encode(seopress_rich_snippets_articles_publisher_option($key_schema)) . ',';
            if ('' != seopress_rich_snippets_articles_publisher_logo_option($key_schema)) {
                $html .= '"logo": {
								  "@type": "ImageObject",
								  "url": ' . json_encode(seopress_rich_snippets_articles_publisher_logo_option($key_schema)) . ',
								  "width": ' . json_encode(seopress_rich_snippets_articles_publisher_logo_width_option($key_schema)) . ',
								  "height": ' . json_encode(seopress_rich_snippets_articles_publisher_logo_height_option($key_schema)) . '
							  }';
            }
            $html .= '},';
        }

        if (seopress_pro_rich_snippets_article_coverage_start_date_option($key_schema) && seopress_pro_rich_snippets_article_coverage_start_time_option($key_schema) && 'LiveBlogPosting' == seopress_rich_snippets_articles_type_option($key_schema)) {
            $html .= '"coverageStartTime": "' . seopress_pro_rich_snippets_article_coverage_start_date_option($key_schema) . 'T' . seopress_pro_rich_snippets_article_coverage_start_time_option($key_schema) . '",';
        }

        if (seopress_pro_rich_snippets_article_coverage_end_date_option($key_schema) && seopress_pro_rich_snippets_article_coverage_end_time_option($key_schema) && 'LiveBlogPosting' == seopress_rich_snippets_articles_type_option($key_schema)) {
            $html .= '"coverageEndTime": "' . seopress_pro_rich_snippets_article_coverage_end_date_option($key_schema) . 'T' . seopress_pro_rich_snippets_article_coverage_end_time_option($key_schema) . '",';
        }

        if ('ReviewNewsArticle' == seopress_rich_snippets_articles_type_option($key_schema)) {
            $html .= '"itemReviewed": {"@type": "Thing", "name":"' . get_the_title() . '"},';
        }

        if ('' != seopress_rich_snippets_articles_desc_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_articles_desc_option($key_schema));
        }

        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_article_html', $html);

        echo $html;
    }

    //Local Business
    //=========================================================================================
    //ID
    function seopress_pro_rich_snippets_lb_id_option($key_schema = 0) {
        $_seopress_robots_canonical = get_post_meta(get_the_ID(), '_seopress_robots_canonical', true);
        if ('' != $_seopress_robots_canonical) {
            return $_seopress_robots_canonical;
        } else {
            global $wp;

            return home_url(add_query_arg([], $wp->request));
        }
    }
    //Name
    function seopress_pro_rich_snippets_lb_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_name) {
            return $_seopress_pro_rich_snippets_lb_name;
        }
    }
    //Type
    function seopress_pro_rich_snippets_lb_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_type) {
            return $_seopress_pro_rich_snippets_lb_type;
        } else {
            return 'LocalBusiness';
        }
    }
    //ServedCuisine
    function seopress_pro_rich_snippets_lb_cuisine_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_cuisine = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_cuisine', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_cuisine) {
            return $_seopress_pro_rich_snippets_lb_cuisine;
        }
    }
    //Img
    function seopress_pro_rich_snippets_lb_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_img) {
            return $_seopress_pro_rich_snippets_lb_img;
        }
    }
    //Img width
    function seopress_pro_rich_snippets_lb_img_width_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_img_width = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_img_width', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_img_width) {
            return $_seopress_pro_rich_snippets_lb_img_width;
        }
    }
    //Img height
    function seopress_pro_rich_snippets_lb_img_height_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_img_height = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_img_height', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_img_height) {
            return $_seopress_pro_rich_snippets_lb_img_height;
        }
    }
    //Street addr
    function seopress_pro_rich_snippets_lb_street_addr_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_street_addr = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_street_addr', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_street_addr) {
            return $_seopress_pro_rich_snippets_lb_street_addr;
        }
    }
    //City
    function seopress_pro_rich_snippets_lb_city_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_city = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_city', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_city) {
            return $_seopress_pro_rich_snippets_lb_city;
        }
    }
    //State
    function seopress_pro_rich_snippets_lb_state_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_state = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_state', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_state) {
            return $_seopress_pro_rich_snippets_lb_state;
        }
    }
    //Postal Code
    function seopress_pro_rich_snippets_lb_pc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_pc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_pc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_pc) {
            return $_seopress_pro_rich_snippets_lb_pc;
        }
    }
    //Country
    function seopress_pro_rich_snippets_lb_country_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_country = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_country', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_country) {
            return $_seopress_pro_rich_snippets_lb_country;
        }
    }
    //Lat
    function seopress_pro_rich_snippets_lb_lat_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_lat = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_lat', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_lat) {
            return $_seopress_pro_rich_snippets_lb_lat;
        }
    }
    //Lon
    function seopress_pro_rich_snippets_lb_lon_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_lon = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_lon', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_lon) {
            return $_seopress_pro_rich_snippets_lb_lon;
        }
    }
    //Website
    function seopress_pro_rich_snippets_lb_website_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_website = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_website', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_website) {
            return $_seopress_pro_rich_snippets_lb_website;
        }
    }
    //Tel
    function seopress_pro_rich_snippets_lb_tel_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_tel = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_tel', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_tel) {
            return $_seopress_pro_rich_snippets_lb_tel;
        }
    }
    //Price
    function seopress_pro_rich_snippets_lb_price_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_price = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_price', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_price) {
            return $_seopress_pro_rich_snippets_lb_price;
        }
    }
    //Opening Hours
    function seopress_pro_rich_snippets_lb_opening_hours_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_lb_opening_hours = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_lb_opening_hours', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_lb_opening_hours) {
            return $_seopress_pro_rich_snippets_lb_opening_hours;
        }
    }

    function seopress_rich_snippets_local_business_option($key_schema = 0) {
        if ('' != seopress_pro_rich_snippets_lb_img_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_img_option = json_encode(seopress_pro_rich_snippets_lb_img_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_name_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_name_option = json_encode(seopress_pro_rich_snippets_lb_name_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_type_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_type_option = json_encode(seopress_pro_rich_snippets_lb_type_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_cuisine_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_cuisine_option = json_encode(seopress_pro_rich_snippets_lb_cuisine_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_street_addr_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_street_addr_option = json_encode(seopress_pro_rich_snippets_lb_street_addr_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_city_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_city_option = json_encode(seopress_pro_rich_snippets_lb_city_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_state_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_state_option = json_encode(seopress_pro_rich_snippets_lb_state_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_pc_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_pc_option = json_encode(seopress_pro_rich_snippets_lb_pc_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_country_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_country_option = json_encode(seopress_pro_rich_snippets_lb_country_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_lat_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_lat_option = json_encode(seopress_pro_rich_snippets_lb_lat_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_lon_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_lon_option = json_encode(seopress_pro_rich_snippets_lb_lon_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_website_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_website_option = json_encode(seopress_pro_rich_snippets_lb_website_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_tel_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_tel_option = json_encode(seopress_pro_rich_snippets_lb_tel_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_lb_price_option($key_schema)) {
            $seopress_pro_rich_snippets_lb_price_option = json_encode(seopress_pro_rich_snippets_lb_price_option($key_schema));
        }

        if ('' != seopress_pro_rich_snippets_lb_opening_hours_option($key_schema)) {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            $seopress_pro_rich_snippets_lb_opening_hours_option = '';

            foreach (seopress_pro_rich_snippets_lb_opening_hours_option($key_schema) as $oh) {//OPENING HOURS
                foreach ($oh as $key => $day) {//DAY
                    if ( ! array_key_exists('open', $day)) {//CLOSED?
                        foreach ($day as $keys => $ampm) {//AM/PM
                            if (array_key_exists('open', $ampm)) {//OPEN?
                                $seopress_pro_rich_snippets_lb_opening_hours_option .= '{ ';
                                $seopress_pro_rich_snippets_lb_opening_hours_option .= '"@type": "OpeningHoursSpecification",';
                                $seopress_pro_rich_snippets_lb_opening_hours_option .= '"dayOfWeek": "' . $days[$key] . '", ';

                                foreach ($ampm as $_key => $value) {//HOURS
                                    if ('start' == $_key) {//START AM/PM
                                        $seopress_pro_rich_snippets_lb_opening_hours_option .= '"opens": "';
                                        foreach ($value as $__key => $time) {
                                            $seopress_pro_rich_snippets_lb_opening_hours_option .= $time;
                                            if ('hours' == $__key) {
                                                $seopress_pro_rich_snippets_lb_opening_hours_option .= ':';
                                            }
                                        }
                                        $seopress_pro_rich_snippets_lb_opening_hours_option .= '",';
                                    }
                                    if ('end' == $_key) {//CLOSE AM/PM
                                        $seopress_pro_rich_snippets_lb_opening_hours_option .= '"closes": "';
                                        foreach ($value as $__key => $time) {
                                            $seopress_pro_rich_snippets_lb_opening_hours_option .= $time;
                                            if ('hours' == $__key) {
                                                $seopress_pro_rich_snippets_lb_opening_hours_option .= ':';
                                            }
                                        }
                                        $seopress_pro_rich_snippets_lb_opening_hours_option .= '"';
                                    }
                                }

                                $seopress_pro_rich_snippets_lb_opening_hours_option .= '|';
                            }
                        }
                    }
                }
            }
        }

        $html = '<script type="application/ld+json">';
        $html .= '{"@context" : "' . seopress_check_ssl() . 'schema.org","@type" : ' . $seopress_pro_rich_snippets_lb_type_option . ',';
        if (isset($seopress_pro_rich_snippets_lb_img_option)) {
            $html .= '"image": ' . $seopress_pro_rich_snippets_lb_img_option . ', ';
        }
        $html .= '"@id": ' . json_encode(seopress_pro_rich_snippets_lb_id_option($key_schema)) . ',';

        if (isset($seopress_pro_rich_snippets_lb_street_addr_option) || isset($seopress_pro_rich_snippets_lb_city_option) || isset($seopress_pro_rich_snippets_lb_state_option) || isset($seopress_pro_rich_snippets_lb_pc_option) || isset($seopress_local_business_address_country_option)) {
            $html .= '"address": {
				"@type": "PostalAddress",';
            if (isset($seopress_pro_rich_snippets_lb_street_addr_option)) {
                $html .= '"streetAddress": ' . $seopress_pro_rich_snippets_lb_street_addr_option . ',';
            }
            if (isset($seopress_pro_rich_snippets_lb_city_option)) {
                $html .= '"addressLocality": ' . $seopress_pro_rich_snippets_lb_city_option . ',';
            }
            if (isset($seopress_pro_rich_snippets_lb_state_option)) {
                $html .= '"addressRegion": ' . $seopress_pro_rich_snippets_lb_state_option . ',';
            }
            if (isset($seopress_pro_rich_snippets_lb_pc_option)) {
                $html .= '"postalCode": ' . $seopress_pro_rich_snippets_lb_pc_option . ',';
            }
            if (isset($seopress_pro_rich_snippets_lb_country_option)) {
                $html .= '"addressCountry": ' . $seopress_pro_rich_snippets_lb_country_option;
            }
            $html .= '},';
        }

        if (isset($seopress_pro_rich_snippets_lb_lat_option) || isset($seopress_pro_rich_snippets_lb_lon_option)) {
            $html .= '"geo": {
				"@type": "GeoCoordinates",';
            if (isset($seopress_pro_rich_snippets_lb_lat_option)) {
                $html .= '"latitude": ' . $seopress_pro_rich_snippets_lb_lat_option . ',';
            }
            if (isset($seopress_pro_rich_snippets_lb_lon_option)) {
                $html .= '"longitude": ' . $seopress_pro_rich_snippets_lb_lon_option;
            }
            $html .= '},';
        }

        if (isset($seopress_pro_rich_snippets_lb_website_option)) {
            $html .= '"url": ' . $seopress_pro_rich_snippets_lb_website_option . ',';
        }
        if (isset($seopress_pro_rich_snippets_lb_tel_option)) {
            $html .= '"telephone": ' . $seopress_pro_rich_snippets_lb_tel_option . ',';
        }
        if (isset($seopress_pro_rich_snippets_lb_price_option)) {
            $html .= '"priceRange": ' . $seopress_pro_rich_snippets_lb_price_option . ',';
        }
        if (isset($seopress_pro_rich_snippets_lb_cuisine_option) &&
            (
                '"FoodEstablishment"' == $seopress_pro_rich_snippets_lb_type_option
                || '"Bakery"' == $seopress_pro_rich_snippets_lb_type_option
                || '"BarOrPub"' == $seopress_pro_rich_snippets_lb_type_option
                || '"Brewery"' == $seopress_pro_rich_snippets_lb_type_option
                || '"CafeOrCoffeeShop"' == $seopress_pro_rich_snippets_lb_type_option
                || '"FastFoodRestaurant"' == $seopress_pro_rich_snippets_lb_type_option
                || '"IceCreamShop"' == $seopress_pro_rich_snippets_lb_type_option
                || '"Restaurant"' == $seopress_pro_rich_snippets_lb_type_option
                || '"Winery"' == $seopress_pro_rich_snippets_lb_type_option
            )
        ) {
            $html .= '"servesCuisine": ' . $seopress_pro_rich_snippets_lb_cuisine_option . ',';
        }
        if (isset($seopress_pro_rich_snippets_lb_opening_hours_option)) {
            $html .= '"openingHoursSpecification": [';

            $explode = array_filter(explode('|', $seopress_pro_rich_snippets_lb_opening_hours_option));
            $seopress_comma_count = count($explode);
            for ($i = 0; $i < $seopress_comma_count; ++$i) {
                $html .= $explode[$i];
                if ($i < ($seopress_comma_count - 1)) {
                    $html .= '}, ';
                } else {
                    $html .= '} ';
                }
            }

            $html .= '],';
        }
        if (isset($seopress_pro_rich_snippets_lb_name_option)) {
            $html .= '"name": ' . $seopress_pro_rich_snippets_lb_name_option;
        } else {
            $html .= '"name": "' . get_bloginfo('name') . '"';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_lb_html', $html);

        echo $html;
    }

    function seopress_rich_snippets_faq_option($key_schema = 0) {
        //Question
        $seopress_pro_rich_snippets_faq = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_faq', $key_schema);

        // Double dimension required as a result of migration 3.9
        $seopress_pro_rich_snippets_faq = ['0' => $seopress_pro_rich_snippets_faq];

        if ( ! empty($seopress_pro_rich_snippets_faq[0][0]['question']) && ! empty($seopress_pro_rich_snippets_faq[0][0]['answer'])) {
            //Init
            $seopress_pro_rich_snippets_faq_questions = '';
            $i = '0';
            $count = count($seopress_pro_rich_snippets_faq[0]);

            foreach ($seopress_pro_rich_snippets_faq[0] as $key => $value) {
                //Question + Answer
                if ('' != $seopress_pro_rich_snippets_faq[0][$key]['question'] && '' != $seopress_pro_rich_snippets_faq[0][$key]['answer']) {
                    $seopress_pro_rich_snippets_faq_questions .= '{';
                    $seopress_pro_rich_snippets_faq_questions .= '"@type": "Question",';
                    $seopress_pro_rich_snippets_faq_questions .= '"name": ' . json_encode($seopress_pro_rich_snippets_faq[0][$key]['question']) . ',';
                    $seopress_pro_rich_snippets_faq_questions .= '"answerCount": 1,';
                    $seopress_pro_rich_snippets_faq_questions .= '"acceptedAnswer": {
					"@type": "Answer",
					"text": ' . json_encode($seopress_pro_rich_snippets_faq[0][$key]['answer']) . '
					}';
                    $seopress_pro_rich_snippets_faq_questions .= '}';
                    if ($i < $count - 1) {
                        $seopress_pro_rich_snippets_faq_questions .= ',';
                    }
                }
                ++$i;
            }

            $html = '<script type="application/ld+json">';
            $html .= '{
				  "@context": "' . seopress_check_ssl() . 'schema.org",
				  "@type": "FAQPage",
				  "name": "FAQ",
				  "mainEntity": [' . $seopress_pro_rich_snippets_faq_questions . ']
				}';
            $html .= '</script>';
            $html .= "\n";

            $html = apply_filters('seopress_schemas_faq_html', $html);

            echo $html;
        }
    }

    //Courses
    //=========================================================================================
    //Title
    function seopress_rich_snippets_courses_title_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_courses_title = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_courses_title', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_courses_title) {
            return $_seopress_pro_rich_snippets_courses_title;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Description
    function seopress_rich_snippets_courses_desc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_courses_desc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_courses_desc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_courses_desc) {
            return $_seopress_pro_rich_snippets_courses_desc;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //School
    function seopress_rich_snippets_courses_school_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_courses_school = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_courses_school', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_courses_school) {
            return $_seopress_pro_rich_snippets_courses_school;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Website
    function seopress_rich_snippets_courses_website_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_courses_website = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_courses_website', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_courses_website) {
            return $_seopress_pro_rich_snippets_courses_website;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }

    function seopress_rich_snippets_courses_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				  "@context": "' . seopress_check_ssl() . 'schema.org",
				  "@type": "Course",';
        if ('' != seopress_rich_snippets_courses_title_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_courses_title_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_courses_desc_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_courses_desc_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_courses_school_option($key_schema)) {
            $html .= '"provider": {
						"@type": "Organization",
						"name": ' . json_encode(seopress_rich_snippets_courses_school_option($key_schema)) . ',
						"sameAs": ' . json_encode(seopress_rich_snippets_courses_website_option($key_schema)) . '
					  }';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_course_html', $html);

        echo $html;
    }

    //Recipes
    //=========================================================================================
    //Recipe name
    function seopress_rich_snippets_recipes_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_name) {
            return $_seopress_pro_rich_snippets_recipes_name;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Description
    function seopress_rich_snippets_recipes_desc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_desc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_desc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_desc) {
            return $_seopress_pro_rich_snippets_recipes_desc;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Categories
    function seopress_rich_snippets_recipes_cat_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_cat = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_cat', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_cat) {
            return $_seopress_pro_rich_snippets_recipes_cat;
        }
    }
    //Image
    function seopress_rich_snippets_recipes_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_img) {
            return $_seopress_pro_rich_snippets_recipes_img;
        }
    }
    //Prep Time
    function seopress_rich_snippets_recipes_prep_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_prep_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_prep_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_prep_time) {
            return $_seopress_pro_rich_snippets_recipes_prep_time;
        }
    }
    //Cook Time
    function seopress_rich_snippets_recipes_cook_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_cook_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_cook_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_cook_time) {
            return $_seopress_pro_rich_snippets_recipes_cook_time;
        }
    }
    //Total Time
    function seopress_rich_snippets_recipes_total_time_option($key_schema = 0) {
        $seopress_pro_rich_snippets_recipes_total_time = seopress_rich_snippets_recipes_cook_time_option() + seopress_rich_snippets_recipes_prep_time_option();

        return $seopress_pro_rich_snippets_recipes_total_time;
    }
    //Calories
    function seopress_rich_snippets_recipes_calories_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_calories = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_calories', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_calories) {
            return $_seopress_pro_rich_snippets_recipes_calories;
        }
    }
    //Yield
    function seopress_rich_snippets_recipes_yield_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_yield = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_yield', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_yield) {
            return $_seopress_pro_rich_snippets_recipes_yield;
        }
    }
    //Keywords
    function seopress_rich_snippets_recipes_keywords_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_keywords = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_keywords', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_keywords) {
            return $_seopress_pro_rich_snippets_recipes_keywords;
        }
    }
    //Recipe Cuisine
    function seopress_rich_snippets_recipes_cuisine_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_cuisine = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_cuisine', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_cuisine) {
            return $_seopress_pro_rich_snippets_recipes_cuisine;
        }
    }
    //Recipe Ingredients
    function seopress_rich_snippets_recipes_ingredient_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_ingredient = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_ingredient', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_ingredient) {
            return $_seopress_pro_rich_snippets_recipes_ingredient;
        }
    }
    //Recipe Instructions
    function seopress_rich_snippets_recipes_instructions_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_recipes_instructions = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_recipes_instructions', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_recipes_instructions) {
            return $_seopress_pro_rich_snippets_recipes_instructions;
        }
    }

    function seopress_rich_snippets_recipes_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				  "@context": "' . seopress_check_ssl() . 'schema.org/",';
        $sp_recipe = '"@type": "Recipe",';

        if ('' != seopress_rich_snippets_recipes_name_option($key_schema)) {
            $sp_recipe .= '"name": ' . json_encode(seopress_rich_snippets_recipes_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_recipes_cat_option($key_schema)) {
            $sp_recipe .= '"recipeCategory": ' . json_encode(seopress_rich_snippets_recipes_cat_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_recipes_img_option($key_schema)) {
            $sp_recipe .= '"image": ' . json_encode(seopress_rich_snippets_recipes_img_option($key_schema)) . ',';
        }
        if (get_the_author()) {
            $sp_recipe .= '"author": {
						  "@type": "Person",
						  "name": ' . json_encode(get_the_author()) . '
					 },';
        }
        if (get_the_date()) {
            $sp_recipe .= '"datePublished": "' . get_the_date('Y-m-j') . '",';
        }
        if ('' != seopress_rich_snippets_recipes_desc_option($key_schema)) {
            $sp_recipe .= '"description": ' . json_encode(seopress_rich_snippets_recipes_desc_option($key_schema)) . ',';
        }
        if (seopress_rich_snippets_recipes_prep_time_option($key_schema)) {
            $sp_recipe .= '"prepTime": ' . json_encode('PT' . seopress_rich_snippets_recipes_prep_time_option($key_schema) . 'M') . ',';
        }
        if ('' != seopress_rich_snippets_recipes_total_time_option($key_schema)) {
            $sp_recipe .= '"totalTime": ' . json_encode('PT' . seopress_rich_snippets_recipes_total_time_option($key_schema) . 'M') . ',';
        }
        if ('' != seopress_rich_snippets_recipes_yield_option($key_schema)) {
            $sp_recipe .= '"recipeYield": ' . json_encode(seopress_rich_snippets_recipes_yield_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_recipes_keywords_option($key_schema)) {
            $sp_recipe .= '"keywords": ' . json_encode(seopress_rich_snippets_recipes_keywords_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_recipes_cuisine_option($key_schema)) {
            $sp_recipe .= '"recipeCuisine": ' . json_encode(seopress_rich_snippets_recipes_cuisine_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_recipes_ingredient_option($key_schema)) {
            $recipes_ingredient = preg_split('/\r\n|[\r\n]/', seopress_rich_snippets_recipes_ingredient_option($key_schema));
            if ( ! empty($recipes_ingredient)) {
                $i = '0';
                $count = count($recipes_ingredient);

                $sp_recipe .= '"recipeIngredient": [';
                foreach ($recipes_ingredient as $value) {
                    $sp_recipe .= json_encode($value);
                    if ($i < $count - 1) {
                        $sp_recipe .= ',';
                    }
                    ++$i;
                }
                $sp_recipe .= '],';
            }
        }
        if ('' != seopress_rich_snippets_recipes_instructions_option($key_schema)) {
            $recipes_instructions = preg_split('/\r\n|[\r\n]/', seopress_rich_snippets_recipes_instructions_option($key_schema));
            if ( ! empty($recipes_instructions)) {
                $i = '0';
                $count = count($recipes_instructions);

                $sp_recipe .= '"recipeInstructions": [';
                foreach ($recipes_instructions as $value) {
                    $sp_recipe .= '{"@type": "HowToStep","text":' . json_encode($value) . '}';
                    if ($i < $count - 1) {
                        $sp_recipe .= ',';
                    }
                    ++$i;
                }
                $sp_recipe .= '],';
            }
        }
        if ('' != seopress_rich_snippets_recipes_calories_option($key_schema)) {
            $sp_recipe .= '"nutrition": {
						   "@type": "NutritionInformation",
						   "calories": ' . json_encode(seopress_rich_snippets_recipes_calories_option($key_schema)) . '
					}';
        }
        $sp_recipe = trim($sp_recipe, ',');
        $sp_recipe .= '}';
        $html .= $sp_recipe;
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_recipe_html', $html);

        echo $html;
    }

    //Jobs
    //=========================================================================================
    //Job title
    function seopress_pro_rich_snippets_jobs_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_name) {
            return $_seopress_pro_rich_snippets_jobs_name;
        }
    }
    //Job description
    function seopress_pro_rich_snippets_jobs_desc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_desc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_desc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_desc) {
            return $_seopress_pro_rich_snippets_jobs_desc;
        }
    }
    //Job date posted
    function seopress_pro_rich_snippets_jobs_date_posted_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_date_posted = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_date_posted', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_date_posted) {
            return $_seopress_pro_rich_snippets_jobs_date_posted;
        }
    }
    //Job valid through
    function seopress_pro_rich_snippets_jobs_valid_through_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_valid_through = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_valid_through', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_valid_through) {
            return $_seopress_pro_rich_snippets_jobs_valid_through;
        }
    }
    //Job employment type
    function seopress_pro_rich_snippets_jobs_employment_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_employment_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_employment_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_employment_type) {
            return $_seopress_pro_rich_snippets_jobs_employment_type;
        }
    }
    //Job ID name
    function seopress_pro_rich_snippets_jobs_identifier_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_identifier_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_identifier_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_identifier_name) {
            return $_seopress_pro_rich_snippets_jobs_identifier_name;
        }
    }
    //Job ID value
    function seopress_pro_rich_snippets_jobs_identifier_value_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_identifier_value = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_identifier_value', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_identifier_value) {
            return $_seopress_pro_rich_snippets_jobs_identifier_value;
        }
    }
    //Job hiring organization
    function seopress_pro_rich_snippets_jobs_hiring_organization_default_name_option($key_schema = 0) {
        $seopress_local_business_img_option = get_option('seopress_social_option_name');
        if ( ! empty($seopress_local_business_img_option)) {
            foreach ($seopress_local_business_img_option as $key => $seopress_local_business_img_value) {
                $options[$key] = $seopress_local_business_img_value;
            }
            if (isset($seopress_local_business_img_option['seopress_social_knowledge_name'])) {
                return $seopress_local_business_img_option['seopress_social_knowledge_name'];
            }
        }
    }
    function seopress_pro_rich_snippets_jobs_hiring_organization_default_logo_option($key_schema = 0) {
        $seopress_local_business_img_option = get_option('seopress_social_option_name');
        if ( ! empty($seopress_local_business_img_option)) {
            foreach ($seopress_local_business_img_option as $key => $seopress_local_business_img_value) {
                $options[$key] = $seopress_local_business_img_value;
            }
            if (isset($seopress_local_business_img_option['seopress_social_knowledge_img'])) {
                return $seopress_local_business_img_option['seopress_social_knowledge_img'];
            }
        }
    }
    function seopress_pro_rich_snippets_jobs_hiring_organization_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_hiring_organization = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_hiring_organiz', $key_schema, true);
        if ('' != $_seopress_pro_rich_snippets_jobs_hiring_organization) {
            return $_seopress_pro_rich_snippets_jobs_hiring_organization;
        } elseif ('' != seopress_pro_rich_snippets_jobs_hiring_organization_default_name_option()) {
            return seopress_pro_rich_snippets_jobs_hiring_organization_default_name_option();
        }
    }
    //Job hiring same as
    function seopress_pro_rich_snippets_jobs_hiring_same_as_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_hiring_same_as = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_hiring_same_as', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_hiring_same_as) {
            return $_seopress_pro_rich_snippets_jobs_hiring_same_as;
        } else {
            return get_home_url();
        }
    }
    //Job hiring logo
    function seopress_pro_rich_snippets_jobs_hiring_logo_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_hiring_logo = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_hiring_logo', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_hiring_logo) {
            return $_seopress_pro_rich_snippets_jobs_hiring_logo;
        } elseif ('' != seopress_pro_rich_snippets_jobs_hiring_organization_default_logo_option()) {
            return seopress_pro_rich_snippets_jobs_hiring_organization_default_logo_option();
        }
    }
    //Job hiring logo width
    function seopress_pro_rich_snippets_jobs_hiring_logo_width_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_hiring_logo_width = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_hiring_logo_width', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_hiring_logo_width) {
            return $_seopress_pro_rich_snippets_jobs_hiring_logo_width;
        }
    }
    //Job hiring logo height
    function seopress_pro_rich_snippets_jobs_hiring_logo_height_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_hiring_logo_height = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_hiring_logo_height', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_hiring_logo_height) {
            return $_seopress_pro_rich_snippets_jobs_hiring_logo_height;
        }
    }
    //Job street address
    function seopress_pro_rich_snippets_jobs_address_street_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_address_street = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_address_street', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_address_street) {
            return $_seopress_pro_rich_snippets_jobs_address_street;
        }
    }
    //Job address locality
    function seopress_pro_rich_snippets_jobs_address_locality_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_address_locality = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_address_locality', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_address_locality) {
            return ', ' . $_seopress_pro_rich_snippets_jobs_address_locality;
        }
    }
    //Job address region
    function seopress_pro_rich_snippets_jobs_address_region_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_address_region = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_address_region', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_address_region) {
            return $_seopress_pro_rich_snippets_jobs_address_region;
        }
    }
    //Job postal code
    function seopress_pro_rich_snippets_jobs_postal_code_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_postal_code = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_postal_code', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_postal_code) {
            return $_seopress_pro_rich_snippets_jobs_postal_code;
        }
    }
    //Job country
    function seopress_pro_rich_snippets_jobs_country_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_country = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_country', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_country) {
            return $_seopress_pro_rich_snippets_jobs_country;
        }
    }
    //Job remote
    function seopress_pro_rich_snippets_jobs_remote_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_remote = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_remote', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_remote) {
            return $_seopress_pro_rich_snippets_jobs_remote;
        }
    }
    //Job direct apply
    function seopress_pro_rich_snippets_jobs_direct_apply_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_direct_apply = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_direct_apply', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_direct_apply) {
            return $_seopress_pro_rich_snippets_jobs_direct_apply;
        }
    }
    //Job salary
    function seopress_pro_rich_snippets_jobs_salary_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_salary = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_salary', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_salary) {
            return $_seopress_pro_rich_snippets_jobs_salary;
        }
    }
    //Job salary currency
    function seopress_pro_rich_snippets_jobs_salary_currency_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_salary_currency = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_salary_currency', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_salary_currency) {
            return $_seopress_pro_rich_snippets_jobs_salary_currency;
        }
    }
    //Job salary unit
    function seopress_pro_rich_snippets_jobs_salary_unit_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_jobs_salary_unit = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_jobs_salary_unit', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_jobs_salary_unit) {
            return $_seopress_pro_rich_snippets_jobs_salary_unit;
        }
    }

    function seopress_rich_snippets_jobs_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				  "@context": "' . seopress_check_ssl() . 'schema.org/",';
        $sp_job = '"@type": "JobPosting",';

        if ('' != seopress_pro_rich_snippets_jobs_name_option($key_schema)) {
            $sp_job .= '"title": ' . json_encode(seopress_pro_rich_snippets_jobs_name_option($key_schema)) . ',';
        }
        if ('' != seopress_pro_rich_snippets_jobs_desc_option($key_schema)) {
            $sp_job .= '"description": ' . json_encode(seopress_pro_rich_snippets_jobs_desc_option($key_schema)) . ',';
        }
        if ('' != seopress_pro_rich_snippets_jobs_identifier_name_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_identifier_value_option($key_schema)) {
            $sp_job .= '"identifier": {
						"@type": "PropertyValue",
						"name": ' . json_encode(seopress_pro_rich_snippets_jobs_identifier_name_option($key_schema)) . ',
						"value": ' . json_encode(seopress_pro_rich_snippets_jobs_identifier_value_option($key_schema)) . '
					},';
        }
        if ('' != seopress_pro_rich_snippets_jobs_date_posted_option($key_schema)) {
            $sp_job .= '"datePosted": ' . json_encode(seopress_pro_rich_snippets_jobs_date_posted_option($key_schema)) . ',';
        }
        if ('' != seopress_pro_rich_snippets_jobs_valid_through_option($key_schema)) {
            $sp_job .= '"validThrough": ' . json_encode(seopress_pro_rich_snippets_jobs_valid_through_option($key_schema)) . ',';
        }
        if ('' != seopress_pro_rich_snippets_jobs_employment_type_option($key_schema)) {
            $sp_job .= '"employmentType": ' . json_encode(seopress_pro_rich_snippets_jobs_employment_type_option($key_schema)) . ',';
        }
        if ('' != seopress_pro_rich_snippets_jobs_hiring_organization_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_hiring_same_as_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_hiring_logo_option($key_schema)) {
            $sp_job .= '"hiringOrganization" : {
						"@type" : "Organization",
						"name" : ' . json_encode(seopress_pro_rich_snippets_jobs_hiring_organization_option($key_schema)) . ',
						"sameAs" : ' . json_encode(seopress_pro_rich_snippets_jobs_hiring_same_as_option($key_schema)) . ',
						"logo" : ' . json_encode(seopress_pro_rich_snippets_jobs_hiring_logo_option($key_schema)) . '
					  },';
        }
        if ('' != seopress_pro_rich_snippets_jobs_address_street_option($key_schema) || '' != seopress_pro_rich_snippets_jobs_address_locality_option($key_schema) || '' != seopress_pro_rich_snippets_jobs_address_region_option($key_schema) || '' != seopress_pro_rich_snippets_jobs_postal_code_option($key_schema) || '' != seopress_pro_rich_snippets_jobs_country_option($key_schema)) {
            $sp_job .= '"jobLocation": {
						"@type": "Place",
							"address": {
							"@type": "PostalAddress",';
            if ('' != seopress_pro_rich_snippets_jobs_address_street_option($key_schema)) {
                $sp_job .= '"streetAddress": ' . json_encode(seopress_pro_rich_snippets_jobs_address_street_option($key_schema)) . ',';
            }
            if ('' != seopress_pro_rich_snippets_jobs_address_locality_option($key_schema)) {
                $sp_job .= '"addressLocality": ' . json_encode(seopress_pro_rich_snippets_jobs_address_locality_option($key_schema)) . ',';
            }
            if ('' != seopress_pro_rich_snippets_jobs_address_region_option($key_schema)) {
                $sp_job .= '"addressRegion": ' . json_encode(seopress_pro_rich_snippets_jobs_address_region_option($key_schema)) . ',';
            }
            if ('' != seopress_pro_rich_snippets_jobs_postal_code_option($key_schema)) {
                $sp_job .= '"postalCode": ' . json_encode(seopress_pro_rich_snippets_jobs_postal_code_option($key_schema)) . ',';
            }
            if ('' != seopress_pro_rich_snippets_jobs_country_option($key_schema)) {
                $sp_job .= '"addressCountry": ' . json_encode(seopress_pro_rich_snippets_jobs_country_option($key_schema));
            }
            $sp_job .= '}
						},';
        }
        if ('' != seopress_pro_rich_snippets_jobs_remote_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_country_option($key_schema)) {
            $sp_job .= '"jobLocationType": "TELECOMMUTE",';
        }
        if ('' != seopress_pro_rich_snippets_jobs_salary_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_salary_currency_option($key_schema) && '' != seopress_pro_rich_snippets_jobs_salary_unit_option($key_schema)) {
            $sp_job .= '"baseSalary": {
						"@type": "MonetaryAmount",
						"currency": ' . json_encode(seopress_pro_rich_snippets_jobs_salary_currency_option($key_schema)) . ',
						"value": {
						  "@type": "QuantitativeValue",
						  "value": ' . json_encode(seopress_pro_rich_snippets_jobs_salary_option($key_schema)) . ',
						  "unitText": ' . json_encode(seopress_pro_rich_snippets_jobs_salary_unit_option($key_schema)) . '
						}
					  }';
        }
        $sp_job = trim($sp_job, ',');
        $sp_job .= '}';
        $html .= $sp_job;
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_job_html', $html);

        echo $html;
    }

    //Videos
    //=========================================================================================
    //Video name
    function seopress_rich_snippets_videos_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_name) {
            return $_seopress_pro_rich_snippets_videos_name;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Description
    function seopress_rich_snippets_videos_desc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_description = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_description', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_description) {
            return $_seopress_pro_rich_snippets_videos_description;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Thumbnail
    function seopress_rich_snippets_videos_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_img) {
            return $_seopress_pro_rich_snippets_videos_img;
        }
    }
    //Thumbnail width
    function seopress_rich_snippets_videos_img_width_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_img_width = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_img_width', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_img_width) {
            return $_seopress_pro_rich_snippets_videos_img_width;
        }
    }
    //Thumbnail Height
    function seopress_rich_snippets_videos_img_height_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_img_height = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_img_height', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_img_height) {
            return $_seopress_pro_rich_snippets_videos_img_height;
        }
    }
    //Duration
    function seopress_rich_snippets_videos_duration_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_duration = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_duration', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_duration) {
            $time = explode(':', $_seopress_pro_rich_snippets_videos_duration);
            $sec = isset($time[2]) ? $time[2] : 00;
            $min = isset($time[0]) && isset($time[1]) ? $time[0] * 60.0 + $time[1] * 1.0 : $_seopress_pro_rich_snippets_videos_duration;

            return 'PT' . $min . 'M' . $sec . 'S';
        }
    }
    //URL
    function seopress_rich_snippets_videos_url_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_videos_url = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_videos_url', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_videos_url) {
            return $_seopress_pro_rich_snippets_videos_url;
        }
    }
    //Publisher name
    function seopress_rich_snippets_videos_publisher_option($key_schema = 0) {
        $seopress_rich_snippets_videos_publisher_option = get_option('seopress_social_option_name');
        if ( ! empty($seopress_rich_snippets_videos_publisher_option)) {
            foreach ($seopress_rich_snippets_videos_publisher_option as $key => $seopress_rich_snippets_videos_publisher_value) {
                $options[$key] = $seopress_rich_snippets_videos_publisher_value;
            }
            if (isset($seopress_rich_snippets_videos_publisher_option['seopress_social_knowledge_name'])) {
                return $seopress_rich_snippets_videos_publisher_option['seopress_social_knowledge_name'];
            }
        }
    }
    //Publisher Logo
    function seopress_rich_snippets_videos_publisher_logo_option($key_schema = 0) {
        $seopress_local_business_img_option = get_option('seopress_social_option_name');
        if ( ! empty($seopress_local_business_img_option)) {
            foreach ($seopress_local_business_img_option as $key => $seopress_local_business_img_value) {
                $options[$key] = $seopress_local_business_img_value;
            }
            if (isset($seopress_local_business_img_option['seopress_social_knowledge_img'])) {
                return $seopress_local_business_img_option['seopress_social_knowledge_img'];
            }
        }
    }

    function seopress_rich_snippets_videos_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				"@context": "' . seopress_check_ssl() . 'schema.org",
				"@type": "VideoObject",';
        if ('' != seopress_rich_snippets_videos_name_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_videos_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_videos_desc_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_videos_desc_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_videos_img_option($key_schema)) {
            $html .= '"thumbnailUrl": ' . json_encode(seopress_rich_snippets_videos_img_option($key_schema)) . ',';
        }
        if (get_the_date()) {
            $html .= '"uploadDate": "' . get_the_date('c') . '",';
        }
        if ('' != seopress_rich_snippets_videos_duration_option($key_schema)) {
            $html .= '"duration": ' . json_encode(seopress_rich_snippets_videos_duration_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_videos_publisher_option($key_schema)) {
            $html .= '"publisher": {
						"@type": "Organization",
						"name": ' . json_encode(seopress_rich_snippets_videos_publisher_option($key_schema)) . ',
						"logo": {
							"@type": "ImageObject",
							"url": ' . json_encode(seopress_rich_snippets_videos_publisher_logo_option($key_schema)) . '
						}
					},';
        }
        if ('' != seopress_rich_snippets_videos_url_option($key_schema)) {
            $html .= '"contentUrl": ' . json_encode(seopress_rich_snippets_videos_url_option($key_schema)) . ',
					"embedUrl": ' . json_encode(seopress_rich_snippets_videos_url_option($key_schema)) . '';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_video_html', $html);

        echo $html;
    }

    //Events
    //=========================================================================================
    //Event type
    function seopress_rich_snippets_events_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_type) {
            return $_seopress_pro_rich_snippets_events_type;
        }
    }
    //Event name
    function seopress_rich_snippets_events_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_name) {
            return $_seopress_pro_rich_snippets_events_name;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Event Description
    function seopress_rich_snippets_events_description_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_description = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_desc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_description) {
            return $_seopress_pro_rich_snippets_events_description;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Event Thumbnail
    function seopress_rich_snippets_events_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_img) {
            return $_seopress_pro_rich_snippets_events_img;
        }
    }
    //Start Date
    function seopress_rich_snippets_events_start_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_start_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_start_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_start_date) {
            return $_seopress_pro_rich_snippets_events_start_date;
        }
    }
    //Start time
    function seopress_rich_snippets_events_start_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_start_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_start_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_start_time) {
            return $_seopress_pro_rich_snippets_events_start_time;
        }
    }
    //Start time + Start date
    function seopress_rich_snippets_events_date_time_start_option($key_schema = 0) {
        if ('' != seopress_rich_snippets_events_start_date_option() && '' != seopress_rich_snippets_events_start_time_option()) {
            return seopress_rich_snippets_events_start_date_option() . 'T' . seopress_rich_snippets_events_start_time_option();
        } else {
            return seopress_rich_snippets_events_start_date_option();
        }
    }
    //End Date
    function seopress_rich_snippets_events_end_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_end_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_end_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_end_date) {
            return $_seopress_pro_rich_snippets_events_end_date;
        }
    }
    //End time
    function seopress_rich_snippets_events_end_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_end_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_end_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_end_time) {
            return $_seopress_pro_rich_snippets_events_end_time;
        }
    }
    //End time + End date
    function seopress_rich_snippets_events_date_time_end_option($key_schema = 0) {
        if ('' != seopress_rich_snippets_events_end_date_option() && '' != seopress_rich_snippets_events_end_time_option()) {
            return seopress_rich_snippets_events_end_date_option() . 'T' . seopress_rich_snippets_events_end_time_option();
        } else {
            return seopress_rich_snippets_events_end_date_option();
        }
    }
    //Previous start date
    function seopress_rich_snippets_events_previous_start_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_previous_start_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_previous_start_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_previous_start_date) {
            return $_seopress_pro_rich_snippets_events_previous_start_date;
        }
    }
    //Previous Start time
    function seopress_rich_snippets_events_previous_start_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_previous_start_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_previous_start_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_previous_start_time) {
            return $_seopress_pro_rich_snippets_events_previous_start_time;
        }
    }
    //Previous start time + Start date
    function seopress_rich_snippets_events_previous_date_time_start_option($key_schema = 0) {
        if ('' != seopress_rich_snippets_events_previous_start_date_option($key_schema) && '' != seopress_rich_snippets_events_previous_start_time_option($key_schema)) {
            return seopress_rich_snippets_events_previous_start_date_option($key_schema) . 'T' . seopress_rich_snippets_events_previous_start_time_option($key_schema);
        } else {
            return seopress_rich_snippets_events_previous_start_date_option($key_schema);
        }
    }
    //Location name
    function seopress_rich_snippets_events_location_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_location_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_location_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_location_name) {
            return $_seopress_pro_rich_snippets_events_location_name;
        }
    }
    //Location URL
    function seopress_rich_snippets_events_location_url_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_location_url = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_location_url', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_location_url) {
            return $_seopress_pro_rich_snippets_events_location_url;
        }
    }
    //Location Address
    function seopress_rich_snippets_events_location_address_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_location_address = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_location_address', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_location_address) {
            return $_seopress_pro_rich_snippets_events_location_address;
        }
    }
    //Offer name
    function seopress_rich_snippets_events_offers_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_name) {
            return $_seopress_pro_rich_snippets_events_offers_name;
        }
    }
    //Offer category
    function seopress_rich_snippets_events_offers_cat_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_cat = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_cat', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_cat) {
            return $_seopress_pro_rich_snippets_events_offers_cat;
        }
    }
    //Offer price
    function seopress_rich_snippets_events_offers_price_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_price = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_price', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_price) {
            return $_seopress_pro_rich_snippets_events_offers_price;
        }
    }
    //Offer price currency
    function seopress_rich_snippets_events_offers_price_currency_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_price_currency = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_price_currency', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_price_currency) {
            return $_seopress_pro_rich_snippets_events_offers_price_currency;
        }
    }
    //Offer availability
    function seopress_rich_snippets_events_offers_availability_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_availability = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_availability', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_availability) {
            return $_seopress_pro_rich_snippets_events_offers_availability;
        }
    }
    //Offer ValidFrom Date
    function seopress_rich_snippets_events_offers_valid_from_date_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_valid_from_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_valid_from_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_valid_from_date) {
            return $_seopress_pro_rich_snippets_events_offers_valid_from_date;
        }
    }
    //Offer ValidFrom Time
    function seopress_rich_snippets_events_offers_valid_from_time_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_valid_from_time = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_valid_from_time', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_valid_from_time) {
            return $_seopress_pro_rich_snippets_events_offers_valid_from_time;
        }
    }
    //Offer ValidFrom Date+Time+Timezone
    function seopress_rich_snippets_events_offers_valid_from($key_schema = 0) {
        if ('' != seopress_rich_snippets_events_offers_valid_from_date_option() && '' != seopress_rich_snippets_events_offers_valid_from_time_option()) {
            $date = seopress_rich_snippets_events_offers_valid_from_date_option() . 'T' . seopress_rich_snippets_events_offers_valid_from_time_option();

            if ('' != get_option('gmt_offset')) {
                $timezone = sprintf('%+d', get_option('gmt_offset'));
                $date = $date . $timezone . ':00';
            }

            return $date;
        }
    }

    //Offer URL
    function seopress_rich_snippets_events_offers_url_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_offers_url = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_offers_url', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_offers_url) {
            return $_seopress_pro_rich_snippets_events_offers_url;
        }
    }
    //Performer name
    function seopress_pro_rich_snippets_events_performer_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_performer_option = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_performer', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_performer_option) {
            return $_seopress_pro_rich_snippets_events_performer_option;
        }
    }
    //Event status
    function seopress_rich_snippets_events_status_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_status_option = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_status', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_status_option && 'none' != $_seopress_pro_rich_snippets_events_status_option) {
            return seopress_check_ssl() . 'schema.org/' . $_seopress_pro_rich_snippets_events_status_option;
        }
    }
    //Event attendance mode
    function seopress_rich_snippets_events_attendance_mode_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_events_attendance_mode_option = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_events_attendance_mode', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_events_attendance_mode_option) {
            return $_seopress_pro_rich_snippets_events_attendance_mode_option;
        }
    }

    function seopress_rich_snippets_events_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
				"@context": "' . seopress_check_ssl() . 'schema.org",';
        if ('' != seopress_rich_snippets_events_type_option($key_schema)) {
            $html .= '"@type": ' . json_encode(seopress_rich_snippets_events_type_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_name_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_events_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_description_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_events_description_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_img_option($key_schema)) {
            $html .= '"image": ' . json_encode(seopress_rich_snippets_events_img_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_location_url_option($key_schema)) {
            $html .= '"url": ' . json_encode(seopress_rich_snippets_events_location_url_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_previous_date_time_start_option($key_schema) && seopress_rich_snippets_events_status_option($key_schema) == seopress_check_ssl($key_schema) . 'schema.org/EventRescheduled') {
            $html .= '"previousStartDate": ' . json_encode(seopress_rich_snippets_events_previous_date_time_start_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_date_time_start_option($key_schema)) {
            $html .= '"startDate": ' . json_encode(seopress_rich_snippets_events_date_time_start_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_date_time_end_option($key_schema)) {
            $html .= '"endDate": ' . json_encode(seopress_rich_snippets_events_date_time_end_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_status_option($key_schema)) {
            $html .= '"eventStatus": ' . json_encode(seopress_rich_snippets_events_status_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_events_attendance_mode_option($key_schema) && 'none' != seopress_rich_snippets_events_attendance_mode_option($key_schema)) {
            if (
                        ('OnlineEventAttendanceMode' == seopress_rich_snippets_events_attendance_mode_option($key_schema) && '' != seopress_rich_snippets_events_location_url_option($key_schema))
                        ||
                        ('MixedEventAttendanceMode' == seopress_rich_snippets_events_attendance_mode_option($key_schema) && '' != seopress_rich_snippets_events_location_url_option($key_schema))
                    ) {
                $html .= '"eventAttendanceMode": ' . json_encode(seopress_rich_snippets_events_attendance_mode_option($key_schema)) . ',';
            } else {
                $html .= '"eventAttendanceMode": ' . json_encode(seopress_rich_snippets_events_attendance_mode_option($key_schema)) . ',';
            }
        }
        if ('' != seopress_rich_snippets_events_location_name_option($key_schema) && '' != seopress_rich_snippets_events_location_address_option($key_schema)) {
            if ('OnlineEventAttendanceMode' == seopress_rich_snippets_events_attendance_mode_option($key_schema) && '' != seopress_rich_snippets_events_location_url_option($key_schema)) {
                $html .= '"location": {
							"@type":"VirtualLocation",
							  "url": ' . json_encode(seopress_rich_snippets_events_location_url_option($key_schema)) . '
						},';
            } elseif ('MixedEventAttendanceMode' == seopress_rich_snippets_events_attendance_mode_option($key_schema) && '' != seopress_rich_snippets_events_location_url_option($key_schema)) {
                $html .= '"location": [{
							"@type":"VirtualLocation",
							  "url": ' . json_encode(seopress_rich_snippets_events_location_url_option($key_schema)) . '
						},
						{
							"@type": "Place",
							"name": ' . json_encode(seopress_rich_snippets_events_location_name_option($key_schema)) . ',
							"address": ' . json_encode(seopress_rich_snippets_events_location_address_option($key_schema)) . '
						}],';
            } else {
                $html .= '"location": {
							"@type": "Place",
							"name": ' . json_encode(seopress_rich_snippets_events_location_name_option($key_schema)) . ',
							"address": ' . json_encode(seopress_rich_snippets_events_location_address_option($key_schema)) . '
						},';
            }
        }
        if ('' != seopress_rich_snippets_events_offers_name_option($key_schema)) {
            $sp_offers = '"offers": [{
						"@type": "Offer",
						"name": ' . json_encode(seopress_rich_snippets_events_offers_name_option($key_schema)) . ',';
            if ('' != seopress_rich_snippets_events_offers_cat_option($key_schema)) {
                $sp_offers .= '"category": ' . json_encode(seopress_rich_snippets_events_offers_cat_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_events_offers_price_option($key_schema)) {
                $sp_offers .= '"price": ' . json_encode(seopress_rich_snippets_events_offers_price_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_events_offers_price_currency_option($key_schema)) {
                $sp_offers .= '"priceCurrency": ' . json_encode(seopress_rich_snippets_events_offers_price_currency_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_events_offers_url_option($key_schema)) {
                $sp_offers .= '"url": ' . json_encode(seopress_rich_snippets_events_offers_url_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_events_offers_availability_option($key_schema)) {
                $sp_offers .= '"availability": ' . json_encode(seopress_rich_snippets_events_offers_availability_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_events_offers_valid_from($key_schema)) {
                $sp_offers .= '"validFrom": ' . json_encode(seopress_rich_snippets_events_offers_valid_from($key_schema));
            }
            $sp_offers = trim($sp_offers, ',');
            if ('' != seopress_pro_rich_snippets_events_performer_option($key_schema)) {
                $sp_offers .= '}],';
            } else {
                $sp_offers .= '}]';
            }
            $html .= $sp_offers;
        }
        if ('' != seopress_pro_rich_snippets_events_performer_option($key_schema)) {
            $html .= '"performer": {
						"@type": "Person",
						"name": ' . json_encode(seopress_pro_rich_snippets_events_performer_option($key_schema)) . '
					}';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_event_html', $html);

        echo $html;
    }

    //Products
    //=========================================================================================
    //Init
    global $product;

    //Product name
    function seopress_rich_snippets_product_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_name) {
            return $_seopress_pro_rich_snippets_product_name;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Description
    function seopress_rich_snippets_product_description_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_description = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_description', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_description) {
            return $_seopress_pro_rich_snippets_product_description;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Img
    function seopress_rich_snippets_product_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_img) {
            return $_seopress_pro_rich_snippets_product_img;
        } elseif ('' != get_the_post_thumbnail_url(get_the_ID(), 'large')) {
            return get_the_post_thumbnail_url(get_the_ID(), 'large');
        }
    }
    //Price
    function seopress_rich_snippets_product_price_option($product, $key_schema = 0) {
        $_seopress_pro_rich_snippets_product_price = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_price', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_price) {
            return $_seopress_pro_rich_snippets_product_price;
        } elseif (isset($product) && '' != $product->get_price()) {
            return $product->get_price();
        }
    }
    //Price valid until
    function seopress_pro_rich_snippets_product_price_valid_date_option($product, $key_schema = 0) {
        $_seopress_pro_rich_snippets_product_price_valid_date = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_price_valid_date', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_price_valid_date) {
            return $_seopress_pro_rich_snippets_product_price_valid_date;
        } elseif (isset($product) && '' != $product->get_date_on_sale_to()) {
            $date = $product->get_date_on_sale_to();

            return $date->date('m-d-Y');
        }
    }
    //SKU
    function seopress_rich_snippets_product_sku_option($product, $key_schema = 0) {
        $_seopress_pro_rich_snippets_product_sku = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_sku', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_sku) {
            return $_seopress_pro_rich_snippets_product_sku;
        } elseif (isset($product) && '' != $product->get_sku()) {
            return $product->get_sku();
        }
    }
    //Product Brand
    function seopress_rich_snippets_product_brand_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_brand = '';
        $_seopress_pro_rich_snippets_product_brand = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_brand', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_brand) {
            $term_list = wp_get_post_terms(get_the_ID(), $_seopress_pro_rich_snippets_product_brand, ['fields' => 'names']);
            if ( ! empty($term_list) && ! is_wp_error($term_list)) {
                return $term_list[0];
            }
        }
    }
    //gtin8 | gtin12 | gtin13 | gtin14 | mpn | isbn
    function seopress_rich_snippets_product_global_ids_option($key_schema = 0) {
        $_seopress_rich_snippets_product_global_ids = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_global_ids', $key_schema);
        if ('' != $_seopress_rich_snippets_product_global_ids) {
            return $_seopress_rich_snippets_product_global_ids;
        }
    }
    //global identifiers value
    function seopress_rich_snippets_product_global_ids_value_option($key_schema = 0) {
        $_seopress_rich_snippets_product_global_ids_value = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_global_ids_value', $key_schema);
        if ('' != $_seopress_rich_snippets_product_global_ids_value) {
            return $_seopress_rich_snippets_product_global_ids_value;
        }
    }
    //Price currency
    function seopress_rich_snippets_product_price_currency_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_price_currency = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_price_currency', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_price_currency) {
            return $_seopress_pro_rich_snippets_product_price_currency;
        }
    }
    //Item Condition
    function seopress_rich_snippets_product_condition_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_condition = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_condition', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_condition) {
            return seopress_check_ssl() . 'schema.org/' . $_seopress_pro_rich_snippets_product_condition;
        } else {
            return seopress_check_ssl() . 'schema.org/NewCondition';
        }
    }
    //Availability
    function seopress_rich_snippets_product_availability_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_product_availability = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_product_availability', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_product_availability) {
            return seopress_check_ssl() . 'schema.org/' . $_seopress_pro_rich_snippets_product_availability;
        }
    }

    function seopress_rich_snippets_product_option($product, $key_schema = 0) {
        //Init
        global $product;

        $html = '<script type="application/ld+json">';
        $html .= '{
			"@context": "' . seopress_check_ssl() . 'schema.org/",
			"@type": "Product",';
        if (seopress_rich_snippets_product_name_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_product_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_product_img_option($key_schema)) {
            $html .= '"image": ' . json_encode(seopress_rich_snippets_product_img_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_product_description_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_product_description_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_product_sku_option($product)) {
            $html .= '"sku": ' . json_encode(seopress_rich_snippets_product_sku_option($product)) . ',';
        }
        if ('' != seopress_rich_snippets_product_global_ids_option($key_schema) && '' != seopress_rich_snippets_product_global_ids_value_option($key_schema)) {
            $html .= json_encode(seopress_rich_snippets_product_global_ids_option($key_schema)) . ': ' . json_encode(seopress_rich_snippets_product_global_ids_value_option($key_schema)) . ',';
        }
        //brand
        if ('' != seopress_rich_snippets_product_brand_option($key_schema)) {
            $html .= '"brand": {
					"@type": "Brand",
					"name": ' . json_encode(seopress_rich_snippets_product_brand_option($key_schema)) . '
				  },';
        }
        if (isset($product) && true === comments_open(get_the_ID())) {//If Reviews is true
            //review
            $args = [
                'meta_key' => 'rating',
                'number' => 1,
                'status' => 'approve',
                'post_status' => 'publish',
                'parent' => 0,
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'post_id' => get_the_ID(),
                'post_type' => 'product',
            ];

            $comments = get_comments($args);

            if ( ! empty($comments)) {
                $html .= '"review": {
						"@type": "Review",
						"reviewRating": {
							  "@type": "Rating",
							"ratingValue": ' . json_encode(get_comment_meta($comments[0]->comment_ID, 'rating', true)) . '
						},
						"author": {
							"@type": "Person",
							  "name": ' . json_encode(get_comment_author($comments[0]->comment_ID)) . '
						}
					  },';
            }
            //aggregateRating
            if (isset($product) && method_exists($product, 'get_review_count') && $product->get_review_count() >= 1) {
                $html .= '"aggregateRating": {
						"@type": "AggregateRating",
						"ratingValue": "' . $product->get_average_rating() . '",
						"reviewCount": "' . json_encode($product->get_review_count()) . '"
					  },';
            }
        }
        //offers

        if ('' != seopress_rich_snippets_product_price_option($product)) {
            $html .= '"offers": {
                        "@type": "Offer",
                        "url": ' . json_encode(get_permalink()) . ',
                        "priceCurrency": ' . json_encode(seopress_rich_snippets_product_price_currency_option($key_schema)) . ',
                        "price": ' . json_encode(seopress_rich_snippets_product_price_option($product)) . ',
                        "priceValidUntil": ' . json_encode(seopress_pro_rich_snippets_product_price_valid_date_option($product)) . ',
                        "itemCondition": ' . json_encode(seopress_rich_snippets_product_condition_option($key_schema)) . ',
                        "availability": ' . json_encode(seopress_rich_snippets_product_availability_option($key_schema)) . '
                    }';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_product_html', $html);

        echo $html;
    }

    //Services
    //=========================================================================================

    //Service name
    function seopress_rich_snippets_service_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_name) {
            return $_seopress_pro_rich_snippets_service_name;
        } else { //Default
            return the_title_attribute('echo=0');
        }
    }
    //Service type
    function seopress_rich_snippets_service_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_type) {
            return $_seopress_pro_rich_snippets_service_type;
        }
    }
    //Service description
    function seopress_rich_snippets_service_description_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_description = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_description', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_description) {
            return $_seopress_pro_rich_snippets_service_description;
        } else { //Default
            return wp_trim_words(esc_html(get_the_excerpt()), 30);
        }
    }
    //Img
    function seopress_rich_snippets_service_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_img) {
            return $_seopress_pro_rich_snippets_service_img;
        } elseif ('' != get_the_post_thumbnail_url(get_the_ID(), 'large')) {
            return get_the_post_thumbnail_url(get_the_ID(), 'large');
        }
    }
    //Area served
    function seopress_rich_snippets_service_area_served_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_area = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_area', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_area) {
            return $_seopress_pro_rich_snippets_service_area;
        }
    }
    //Provider name
    function seopress_rich_snippets_service_provider_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_provider_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_provider_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_provider_name) {
            return $_seopress_pro_rich_snippets_service_provider_name;
        }
    }
    //Location img
    function seopress_rich_snippets_service_lb_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_lb_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_lb_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_lb_img) {
            return $_seopress_pro_rich_snippets_service_lb_img;
        }
    }
    //Provider mobility
    function seopress_rich_snippets_service_provider_mobility_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_provider_mobility = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_provider_mobility', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_provider_mobility) {
            return $_seopress_pro_rich_snippets_service_provider_mobility;
        }
    }
    //Slogan
    function seopress_rich_snippets_service_slogan_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_slogan = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_slogan', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_slogan) {
            return $_seopress_pro_rich_snippets_service_slogan;
        }
    }
    //Street addr
    function seopress_rich_snippets_service_street_addr_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_street_addr = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_street_addr', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_street_addr) {
            return $_seopress_pro_rich_snippets_service_street_addr;
        }
    }
    //City
    function seopress_rich_snippets_service_city_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_city = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_city', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_city) {
            return $_seopress_pro_rich_snippets_service_city;
        }
    }
    //State
    function seopress_rich_snippets_service_state_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_state = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_state', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_state) {
            return $_seopress_pro_rich_snippets_service_state;
        }
    }
    //PC
    function seopress_rich_snippets_service_pc_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_pc = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_pc', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_pc) {
            return $_seopress_pro_rich_snippets_service_pc;
        }
    }
    //Country
    function seopress_rich_snippets_service_country_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_country = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_country', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_country) {
            return $_seopress_pro_rich_snippets_service_country;
        }
    }
    //Lat
    function seopress_rich_snippets_service_lat_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_lat = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_lat', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_lat) {
            return $_seopress_pro_rich_snippets_service_lat;
        }
    }
    //Lon
    function seopress_rich_snippets_service_lon_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_lon = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_lon', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_lon) {
            return $_seopress_pro_rich_snippets_service_lon;
        }
    }
    //Provider name
    function seopress_pro_rich_snippets_service_provider_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_provider_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_provider_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_provider_name) {
            return $_seopress_pro_rich_snippets_service_provider_name;
        }
    }
    //Tel
    function seopress_rich_snippets_service_tel_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_tel = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_tel', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_tel) {
            return $_seopress_pro_rich_snippets_service_tel;
        }
    }
    //Price
    function seopress_rich_snippets_service_price_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_service_price = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_service_price', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_service_price) {
            return $_seopress_pro_rich_snippets_service_price;
        }
    }

    function seopress_rich_snippets_service_option($key_schema = 0) {
        //Init
        global $product;

        $html = '<script type="application/ld+json">';
        $html .= '{
			"@context": "' . seopress_check_ssl() . 'schema.org/",
			"@type": "Service",';
        if ('' != seopress_rich_snippets_articles_canonical_option($key_schema)) {
            $html .= '"@id": ' . json_encode(seopress_rich_snippets_articles_canonical_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_name_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_service_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_type_option($key_schema)) {
            $html .= '"serviceType": ' . json_encode(seopress_rich_snippets_service_type_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_description_option($key_schema)) {
            $html .= '"description": ' . json_encode(seopress_rich_snippets_service_description_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_img_option($key_schema)) {
            $html .= '"image": ' . json_encode(seopress_rich_snippets_service_img_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_area_served_option($key_schema)) {
            $html .= '"areaServed": ' . json_encode(seopress_rich_snippets_service_area_served_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_provider_mobility_option($key_schema)) {
            $html .= '"providerMobility": ' . json_encode(seopress_rich_snippets_service_provider_mobility_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_service_slogan_option($key_schema)) {
            $html .= '"slogan": ' . json_encode(seopress_rich_snippets_service_slogan_option($key_schema)) . ',';
        }
        //Provider
        if ('' != seopress_rich_snippets_service_lb_img_option($key_schema)) {
            $html .= '"provider": {
					"@type": "LocalBusiness",';
            if ('' != seopress_pro_rich_snippets_service_provider_name_option($key_schema)) {
                $html .= '"name": ' . json_encode(seopress_pro_rich_snippets_service_provider_name_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_service_tel_option($key_schema)) {
                $html .= '"telephone": ' . json_encode(seopress_rich_snippets_service_tel_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_service_lb_img_option($key_schema)) {
                $html .= '"image": ' . json_encode(seopress_rich_snippets_service_lb_img_option($key_schema)) . ',';
            }
            if ('' != seopress_rich_snippets_service_price_option($key_schema)) {
                $html .= '"priceRange": ' . json_encode(seopress_rich_snippets_service_price_option($key_schema)) . ',';
            }

            //Address
            if ('' != seopress_rich_snippets_service_street_addr_option($key_schema) || '' != seopress_rich_snippets_service_city_option($key_schema) || '' != seopress_rich_snippets_service_state_option($key_schema) || '' != seopress_rich_snippets_service_pc_option($key_schema) || '' != seopress_rich_snippets_service_country_option($key_schema)) {
                $html .= '"address": {
						"@type": "PostalAddress",';
                if ('' != seopress_rich_snippets_service_street_addr_option($key_schema)) {
                    $html .= '"streetAddress": ' . json_encode(seopress_rich_snippets_service_street_addr_option($key_schema)) . ',';
                }
                if ('' != seopress_rich_snippets_service_city_option($key_schema)) {
                    $html .= '"addressLocality": ' . json_encode(seopress_rich_snippets_service_city_option($key_schema)) . ',';
                }
                if ('' != seopress_rich_snippets_service_state_option($key_schema)) {
                    $html .= '"addressRegion": ' . json_encode(seopress_rich_snippets_service_state_option($key_schema)) . ',';
                }
                if ('' != seopress_rich_snippets_service_pc_option($key_schema)) {
                    $html .= '"postalCode": ' . json_encode(seopress_rich_snippets_service_pc_option($key_schema)) . ',';
                }
                if ('' != seopress_rich_snippets_service_country_option($key_schema)) {
                    $html .= '"addressCountry": ' . json_encode(seopress_rich_snippets_service_country_option($key_schema));
                }
                $html .= '},';
            }
            //GPS
            if ('' != seopress_rich_snippets_service_lat_option($key_schema) || '' != seopress_rich_snippets_service_lon_option($key_schema)) {
                $html .= '"geo": {
						"@type": "GeoCoordinates",';
                if ('' != seopress_rich_snippets_service_lat_option($key_schema)) {
                    $html .= '"latitude": ' . json_encode(seopress_rich_snippets_service_lat_option($key_schema)) . ',';
                }
                if ('' != seopress_rich_snippets_service_lon_option($key_schema)) {
                    $html .= '"longitude": ' . json_encode(seopress_rich_snippets_service_lon_option($key_schema));
                }
                $html .= '}';
            }
            if (isset($product) && true === comments_open(get_the_ID())) {//If Reviews is true
                $html .= '},';
            } else {
                $html .= '}';
            }
        }

        if (isset($product) && true === comments_open(get_the_ID())) {//If Reviews is true
            //review
            $args = [
                'meta_key' => 'rating',
                'number' => 1,
                'status' => 'approve',
                'post_status' => 'publish',
                'parent' => 0,
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'post_id' => get_the_ID(),
                'post_type' => 'product',
            ];

            $comments = get_comments($args);

            if ( ! empty($comments)) {
                $html .= '"review": {
						"@type": "Review",
						"reviewRating": {
								"@type": "Rating",
							"ratingValue": ' . json_encode(get_comment_meta($comments[0]->comment_ID, 'rating', true)) . '
						},
						"author": {
							"@type": "Person",
								"name": ' . json_encode(get_comment_author($comments[0]->comment_ID)) . '
						}
						},';
            }

            //aggregateRating
            if (isset($product) && $product->get_review_count() >= 1) {
                $html .= '"aggregateRating": {
						"@type": "AggregateRating",
						"ratingValue": "' . $product->get_average_rating() . '",
						"reviewCount": "' . json_encode($product->get_review_count()) . '"
						}';
            }
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_service_html', $html);

        echo $html;
    }

    //Software App
    //=========================================================================================

    //Software name
    function seopress_rich_snippets_softwareapp_name_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_name = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_name', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_name) {
            return $_seopress_pro_rich_snippets_softwareapp_name;
        }
    }
    //OS
    function seopress_rich_snippets_softwareapp_os_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_os = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_os', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_os) {
            return $_seopress_pro_rich_snippets_softwareapp_os;
        }
    }
    //Category
    function seopress_rich_snippets_softwareapp_cat_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_cat = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_cat', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_cat) {
            return $_seopress_pro_rich_snippets_softwareapp_cat;
        }
    }
    //Price
    function seopress_rich_snippets_softwareapp_price_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_price = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_price', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_price) {
            return $_seopress_pro_rich_snippets_softwareapp_price;
        }
    }
    //Currency
    function seopress_rich_snippets_softwareapp_currency_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_currency = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_currency', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_currency) {
            return $_seopress_pro_rich_snippets_softwareapp_currency;
        }
    }
    //Rating
    function seopress_rich_snippets_softwareapp_rating_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_softwareapp_rating = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_softwareapp_rating', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_softwareapp_rating) {
            return $_seopress_pro_rich_snippets_softwareapp_rating;
        }
    }

    function seopress_rich_snippets_softwareapp_option($key_schema = 0) {
        $html = '<script type="application/ld+json">';
        $html .= '{
			"@context": "' . seopress_check_ssl() . 'schema.org/",
			"@type": "SoftwareApplication",';
        if ('' != seopress_rich_snippets_softwareapp_name_option($key_schema)) {
            $html .= '"name": ' . json_encode(seopress_rich_snippets_softwareapp_name_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_softwareapp_os_option($key_schema)) {
            $html .= '"operatingSystem": ' . json_encode(seopress_rich_snippets_softwareapp_os_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_softwareapp_cat_option()) {
            $html .= '"applicationCategory": ' . json_encode(seopress_check_ssl() . 'schema.org/' . seopress_rich_snippets_softwareapp_cat_option($key_schema)) . ',';
        }
        if ('' != seopress_rich_snippets_softwareapp_rating_option($key_schema)) {
            $html .= '"review": {
					"@type": "Review",
					"reviewRating": {
							"@type": "Rating",
							"ratingValue": ' . json_encode(seopress_rich_snippets_softwareapp_rating_option($key_schema)) . '
						},
						"author": {
							"@type": "Person",
							"name": ' . json_encode(get_the_author()) . '
						}
					},';
        }
        if ('' != seopress_rich_snippets_softwareapp_price_option($key_schema) && '' != seopress_rich_snippets_softwareapp_currency_option($key_schema)) {
            $html .= '"offers": {
				"@type": "Offer",';
            $html .= '"price": ' . json_encode(seopress_rich_snippets_softwareapp_price_option($key_schema)) . ',';
            $html .= '"priceCurrency": ' . json_encode(seopress_rich_snippets_softwareapp_currency_option($key_schema));
            $html .= '}';
        }
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';
        $html .= "\n";

        $html = apply_filters('seopress_schemas_softwareapp_html', $html);

        echo $html;
    }

    //Review
    //=========================================================================================

    //Review item name
    function seopress_pro_rich_snippets_review_item_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_review_item = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_review_item', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_review_item) {
            return $_seopress_pro_rich_snippets_review_item;
        }
    }
    //Review item type
    function seopress_pro_rich_snippets_review_item_type_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_review_item_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_review_item_type', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_review_item_type) {
            return $_seopress_pro_rich_snippets_review_item_type;
        }
    }
    //Review item img
    function seopress_pro_rich_snippets_review_img_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_review_img = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_review_img', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_review_img) {
            return $_seopress_pro_rich_snippets_review_img;
        }
    }
    //Review rating
    function seopress_pro_rich_snippets_review_rating_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_review_rating = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_review_rating', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_review_rating) {
            return $_seopress_pro_rich_snippets_review_rating;
        }
    }
    //Review max rating
    function seopress_pro_rich_snippets_review_max_rating_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_review_max_rating = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_review_max_rating', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_review_max_rating) {
            return $_seopress_pro_rich_snippets_review_max_rating;
        }
    }

    function seopress_rich_snippets_review_option($key_schema = 0) {
        if (seopress_pro_rich_snippets_review_item_type_option($key_schema)) {
            $type = seopress_pro_rich_snippets_review_item_type_option($key_schema);
        } else {
            $type = 'Thing';
        }

        $html = '<script type="application/ld+json">';
        $html .= '{
			"@context": "' . seopress_check_ssl() . 'schema.org/",
			"@type": "Review",';
        if (seopress_pro_rich_snippets_review_item_option($key_schema)) {
            $html .= '"itemReviewed":{"@type":' . json_encode($type) . ',"name":' . json_encode(seopress_pro_rich_snippets_review_item_option($key_schema));
        }
        if ('' != seopress_pro_rich_snippets_review_item_option($key_schema) && '' == seopress_pro_rich_snippets_review_img_option($key_schema)) {
            $html .= '},';
        } else {
            $html .= ',';
        }
        if ('' != seopress_pro_rich_snippets_review_img_option($key_schema)) {
            $html .= '"image": {"@type":"ImageObject","url":' . json_encode(seopress_pro_rich_snippets_review_img_option($key_schema)) . '}';
        }
        if ('' != seopress_pro_rich_snippets_review_item_option($key_schema) && '' != seopress_pro_rich_snippets_review_img_option($key_schema)) {
            $html .= '},';
        }
        if ('' != seopress_pro_rich_snippets_review_rating_option($key_schema)) {
            $html .= '"reviewRating":{"@type":"Rating","ratingValue":' . json_encode(seopress_pro_rich_snippets_review_rating_option($key_schema)) . '},';
        }
        $html .= '"datePublished":"' . get_the_date('c') . '",';
        $html .= '"author":{"@type":"Person","name":' . json_encode(get_the_author()) . '}';
        $html = trim($html, ',');
        $html .= '}';
        $html .= '</script>';

        $html .= "\n";

        $html = apply_filters('seopress_schemas_review_html', $html);

        echo $html;
    }

    //Custom schema
    //=========================================================================================
    //Custom
    function seopress_pro_rich_snippets_custom_option($key_schema = 0) {
        $_seopress_pro_rich_snippets_custom = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_custom', $key_schema);
        if ('' != $_seopress_pro_rich_snippets_custom) {
            return $_seopress_pro_rich_snippets_custom;
        }
    }

    function seopress_rich_snippets_custom_option($key_schema = 0) {
        $html = '';
        if (seopress_pro_rich_snippets_custom_option($key_schema)) {
            $variables = null;
            $variables = apply_filters('seopress_dyn_variables_fn', $variables);

            $post = $variables['post'];
            $term = $variables['term'];
            $seopress_titles_title_template = $variables['seopress_titles_title_template'];
            $seopress_titles_description_template = $variables['seopress_titles_description_template'];
            $seopress_paged = $variables['seopress_paged'];
            $the_author_meta = $variables['the_author_meta'];
            $sep = $variables['sep'];
            $seopress_excerpt = $variables['seopress_excerpt'];
            $post_category = $variables['post_category'];
            $post_tag = $variables['post_tag'];
            $post_thumbnail_url = $variables['post_thumbnail_url'];
            $get_search_query = $variables['get_search_query'];
            $woo_single_cat_html = $variables['woo_single_cat_html'];
            $woo_single_tag_html = $variables['woo_single_tag_html'];
            $woo_single_price = $variables['woo_single_price'];
            $woo_single_price_exc_tax = $variables['woo_single_price_exc_tax'];
            $woo_single_sku = $variables['woo_single_sku'];
            $author_bio = $variables['author_bio'];
            $seopress_get_the_excerpt = $variables['seopress_get_the_excerpt'];
            $seopress_titles_template_variables_array = $variables['seopress_titles_template_variables_array'];
            $seopress_titles_template_replace_array = $variables['seopress_titles_template_replace_array'];
            $seopress_excerpt_length = $variables['seopress_excerpt_length'];

            $custom = seopress_pro_rich_snippets_custom_option($key_schema);

            preg_match_all('/%%_cf_(.*?)%%/', $custom, $matches); //custom fields

            if ( ! empty($matches)) {
                $seopress_titles_cf_template_variables_array = [];
                $seopress_titles_cf_template_replace_array = [];

                foreach ($matches['0'] as $key => $value) {
                    $seopress_titles_cf_template_variables_array[] = $value;
                }

                foreach ($matches['1'] as $key => $value) {
                    $seopress_titles_cf_template_replace_array[] = esc_attr(get_post_meta($post->ID, $value, true));
                }
            }

            preg_match_all('/%%_ct_(.*?)%%/', $custom, $matches2); //custom terms taxonomy

            if ( ! empty($matches2)) {
                $seopress_titles_ct_template_variables_array = [];
                $seopress_titles_ct_template_replace_array = [];

                foreach ($matches2['0'] as $key => $value) {
                    $seopress_titles_ct_template_variables_array[] = $value;
                }

                foreach ($matches2['1'] as $key => $value) {
                    $term = wp_get_post_terms($post->ID, $value);
                    if ( ! is_wp_error($term)) {
                        $seopress_titles_ct_template_replace_array[] = esc_attr($term[0]->name);
                    }
                }
            }

            //Default
            $custom = str_replace($seopress_titles_template_variables_array, $seopress_titles_template_replace_array, $custom);

            //Custom fields
            if ( ! empty($matches) && ! empty($seopress_titles_cf_template_variables_array) && ! empty($seopress_titles_cf_template_replace_array)) {
                $custom = str_replace($seopress_titles_cf_template_variables_array, $seopress_titles_cf_template_replace_array, $custom);
            }

            //Custom terms taxonomy
            if ( ! empty($matches2) && ! empty($seopress_titles_ct_template_variables_array) && ! empty($seopress_titles_ct_template_replace_array)) {
                $custom = str_replace($seopress_titles_ct_template_variables_array, $seopress_titles_ct_template_replace_array, $custom);
            }

            $html .= wp_specialchars_decode($custom, ENT_COMPAT);
        }

        $html .= "\n";

        $html = apply_filters('seopress_schemas_custom_html', $html);

        echo $html;
    }

    //SiteNavigationElement schema

    //=========================================================================================
    //SiteNavigationElement?
    function seopress_rich_snippets_site_nav_option() {
        $seopress_rich_snippets_site_nav_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_rich_snippets_site_nav_option)) {
            foreach ($seopress_rich_snippets_site_nav_option as $key => $seopress_rich_snippets_site_nav_value) {
                $options[$key] = $seopress_rich_snippets_site_nav_value;
            }
            if (isset($seopress_rich_snippets_site_nav_option['seopress_rich_snippets_site_nav'])) {
                return $seopress_rich_snippets_site_nav_option['seopress_rich_snippets_site_nav'];
            }
        }
    }

    function seopress_rich_snippets_site_nav() {
        if (function_exists('wp_get_nav_menu_items')) {
            $menu_items = wp_get_nav_menu_items(seopress_rich_snippets_site_nav_option());

            if ( ! empty($menu_items)) {
                $html = '<script type="application/ld+json">';
                $html .= '{
					"@context": "' . seopress_check_ssl() . 'schema.org/",
					"@type": "SiteNavigationElement",';

                $i = '0';
                $count = count($menu_items);
                $html .= '"name":[';
                foreach ($menu_items as $item) {
                    $html .= json_encode($item->title);
                    if ($i < $count - 1) {
                        $html .= ',';
                    }
                    ++$i;
                }
                $i = '0';
                $html .= '],';
                $html .= '"url":[';
                foreach ($menu_items as $item) {
                    $html .= json_encode($item->url);
                    if ($i < $count - 1) {
                        $html .= ',';
                    }
                    ++$i;
                }
                $html .= ']';
                $html .= '}';
                $html .= '</script>';

                $html .= "\n";

                $html = apply_filters('seopress_schemas_site_navigation_element_html', $html);

                echo $html;
            }
        }
    }

    //=========================================================================================
    //  Hooks schemas
    //=========================================================================================

    if (apply_filters('seopress_fallback_option_rich_snippets', false)) {
        $is_multidimensional = seopress_pro_rich_snippets_is_multidimensional(get_the_ID());

        if ( ! $is_multidimensional) {
            $_seopress_pro_rich_snippets_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_type', 0);

            //Articles JSON-LD
            if ('articles' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_articles_option');
                }
            }

            //Local Business
            //=========================================================================================

            //Local Business JSON-LD
            if ('localbusiness' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_local_business_option');
                }
            }
            //FAQ
            //=========================================================================================
            if ('faq' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_faq_option');
                }
            }
            //Courses
            //=========================================================================================

            //Courses JSON-LD
            if ('courses' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_courses_option');
                }
            }

            //Recipes
            //=========================================================================================

            //Recipes JSON-LD
            if ('recipes' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_recipes_option');
                }
            }

            //Jobs
            //=========================================================================================

            //Jobs JSON-LD
            if ('jobs' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_jobs_option');
                }
            }

            //Videos
            //=========================================================================================

            //Videos JSON-LD
            if ('videos' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_videos_option');
                }
            }

            //Events
            //=========================================================================================

            //Events JSON-LD
            if ('events' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_events_option');
                }
            }

            //Products
            //=========================================================================================

            //Products JSON-LD
            if ('products' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_product_option');
                }
            }

            //Services
            //=========================================================================================

            //Services JSON-LD
            if ('services' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_service_option');
                }
            }

            //Software App
            //=========================================================================================

            //Software App JSON-LD
            if ('softwareapp' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_softwareapp_option');
                }
            }

            //Review
            //=========================================================================================

            //Review JSON-LD
            if ('review' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_review_option');
                }
            }

            //Custom schema
            //=========================================================================================

            //Custom JSON-LD
            if ('custom' == $_seopress_pro_rich_snippets_type) {
                if (is_singular()) {
                    add_action('wp_head', 'seopress_rich_snippets_custom_option');
                }
            }
        } else {
            $schemas = seopress_get_all_pro_rich_snippets_manual(get_the_ID());
            foreach ($schemas as $key => $schema) {
                $_seopress_pro_rich_snippets_type = seopress_get_pro_rich_snippets_by_key(get_the_ID(), '_seopress_pro_rich_snippets_type', $key);

                //Articles JSON-LD
                if ('articles' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_articles_option($key);
                        });
                    }
                }

                //Local Business
                //=========================================================================================

                //Local Business JSON-LD
                if ('localbusiness' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_local_business_option($key);
                        });
                    }
                }
                //FAQ
                //=========================================================================================
                if ('faq' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_faq_option($key);
                        });
                    }
                }
                //Courses
                //=========================================================================================

                //Courses JSON-LD
                if ('courses' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_courses_option($key);
                        });
                    }
                }

                //Recipes
                //=========================================================================================

                //Recipes JSON-LD
                if ('recipes' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_recipes_option($key);
                        });
                    }
                }

                //Jobs
                //=========================================================================================

                //Jobs JSON-LD
                if ('jobs' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_jobs_option($key);
                        });
                    }
                }

                //Videos
                //=========================================================================================

                //Videos JSON-LD
                if ('videos' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_videos_option($key);
                        });
                    }
                }

                //Events
                //=========================================================================================

                //Events JSON-LD
                if ('events' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_events_option($key);
                        });
                    }
                }

                //Products
                //=========================================================================================

                //Products JSON-LD
                if ('products' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_product_option($key);
                        });
                    }
                }

                //Services
                //=========================================================================================

                //Services JSON-LD
                if ('services' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_service_option($key);
                        });
                    }
                }

                //Software App
                //=========================================================================================

                //Software App JSON-LD
                if ('softwareapp' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_softwareapp_option($key);
                        });
                    }
                }

                //Review
                //=========================================================================================

                //Review JSON-LD
                if ('review' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_review_option($key);
                        });
                    }
                }

                //Custom schema
                //=========================================================================================

                //Custom JSON-LD
                if ('custom' == $_seopress_pro_rich_snippets_type) {
                    if (is_singular()) {
                        add_action('wp_head', function () use ($key) {
                            seopress_rich_snippets_custom_option($key);
                        });
                    }
                }
            }
        }

        //SiteNavigationElement schema

        //=========================================================================================

        if ('' != seopress_rich_snippets_site_nav_option() && 'none' != seopress_rich_snippets_site_nav_option()) {
            add_action('wp_head', 'seopress_rich_snippets_site_nav');
        }
    }
}
