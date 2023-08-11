<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//XML

//Headers
if (function_exists('seopress_sitemaps_headers')) {
    seopress_sitemaps_headers();
}

//Remove primary category
remove_filter('post_link_category', 'seopress_titles_primary_cat_hook', 10, 3);

//WPML
add_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20, 5);

add_filter('seopress_sitemaps_single_gnews_query', function ($args) {
    global $sitepress, $sitepress_settings;

    $sitepress_settings['auto_adjust_ids'] = 0;
    remove_filter('terms_clauses', [$sitepress, 'terms_clauses']);
    remove_filter('category_link', [$sitepress, 'category_link_adjust_id'], 1);

    return $args;
});

function seopress_xml_sitemap_news() {
    //Publication Name
    function seopress_xml_sitemap_news_name_option() {
        $seopress_xml_sitemap_news_name_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_xml_sitemap_news_name_option)) {
            foreach ($seopress_xml_sitemap_news_name_option as $key => $seopress_xml_sitemap_news_name_value) {
                $options[$key] = $seopress_xml_sitemap_news_name_value;
            }
            if (isset($seopress_xml_sitemap_news_name_option['seopress_news_name'])) {
                return $seopress_xml_sitemap_news_name_option['seopress_news_name'];
            }
        }
    }
    //Include Custom Post Types
    function seopress_xml_sitemap_news_cpt_option() {
        $seopress_xml_sitemap_news_cpt_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_xml_sitemap_news_cpt_option)) {
            foreach ($seopress_xml_sitemap_news_cpt_option as $key => $seopress_xml_sitemap_news_cpt_value) {
                $options[$key] = $seopress_xml_sitemap_news_cpt_value;
            }
            if (isset($seopress_xml_sitemap_news_cpt_option['seopress_news_name_post_types_list'])) {
                return $seopress_xml_sitemap_news_cpt_option['seopress_news_name_post_types_list'];
            }
        }
    }
    if ('' != seopress_xml_sitemap_news_cpt_option()) {
        $seopress_xml_sitemap_news_cpt_array = [];
        foreach (seopress_xml_sitemap_news_cpt_option() as $cpt_key => $cpt_value) {
            foreach ($cpt_value as $_cpt_key => $_cpt_value) {
                if ('1' == $_cpt_value) {
                    array_push($seopress_xml_sitemap_news_cpt_array, $cpt_key);
                }
            }
        }
    }

    $home_url = home_url() . '/';

    if (function_exists('pll_home_url')) {
        $home_url = site_url() . '/';
    }

    $home_url = apply_filters('seopress_sitemaps_home_url', $home_url);

    $seopress_sitemaps = '<?xml version="1.0" encoding="UTF-8"?>';
    $seopress_sitemaps .= '<?xml-stylesheet type="text/xsl" href="' . $home_url . 'sitemaps_xsl.xsl"?>';
    $seopress_sitemaps .= "\n";
    $seopress_sitemaps_urlset = apply_filters('seopress_sitemaps_urlset', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-news/0.9 http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">');
    $seopress_sitemaps .= $seopress_sitemaps_urlset;
    $seopress_sitemaps .= "\n";

    $args = [
        'exclude' => '',
        'posts_per_page' => 1000,
        'order' => 'DESC',
        'orderby' => 'date',
        'post_type' => $seopress_xml_sitemap_news_cpt_array,
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_seopress_robots_index',
                'value' => '',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => '_seopress_robots_index',
                'value' => 'yes',
                'compare' => '!=',
            ],
        ],
        'date_query' => [
            [
                'after' => '2 days ago',
            ],
        ],
        'post__not_in' => get_option('sticky_posts'),
        'lang' => '',
        'has_password' => false,
    ];

    $args = apply_filters('seopress_sitemaps_single_gnews_query', $args);

    $postslist = get_posts($args);
    foreach ($postslist as $post) {
        setup_postdata($post);
        if ('yes' != get_post_meta($post->ID, '_seopress_news_disabled', true)) {
            // Extract lang
            $lang = explode('_', get_locale());
            $lang = $lang[0];

            // Extract mod
            if (get_the_modified_date('c', $post)) {
                $seopress_mod = get_the_modified_date('c', $post);
            } else {
                $seopress_mod = get_post_modified_time('c', false, $post);
            }

            // Extract keywords
            $seopress_keywords = false;
            $seopress_keywords = apply_filters('seopress_sitemaps_news_keywords', $seopress_keywords);
            if ($seopress_keywords) {
                $seopress_keywords = get_post_meta($post->ID, '_seopress_analysis_target_kw', true);
                $seopress_keywords = apply_filters('seopress_sitemaps_news_keywords_value', $seopress_keywords);
            }

            // Extract image
            $images_array = [];
            if ('1' == seopress_xml_sitemap_img_enable_option()) {
                //noimageindex?
                if ('yes' != get_post_meta($post, '_seopress_robots_imageindex', true)) {
                    //Standard images
                    if ('' != get_post_field('post_content', $post)) {
                        $dom = new domDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $run_shortcodes = apply_filters('seopress_sitemaps_single_shortcodes', true);

                        if (true === $run_shortcodes) {
                            $post_content = do_shortcode(get_post_field('post_content', $post));
                        } else {
                            $post_content = get_post_field('post_content', $post);
                        }

                        if ('' != $post_content) {
                            if (function_exists('mb_convert_encoding')) {
                                $dom->loadHTML(mb_convert_encoding($post_content, 'HTML-ENTITIES', 'UTF-8'));
                            } else {
                                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $post_content);
                            }

                            $dom->preserveWhiteSpace = false;

                            if ('' != $dom->getElementsByTagName('img')) {
                                $images = $dom->getElementsByTagName('img');
                            }
                        }
                        libxml_use_internal_errors($internalErrors);
                    }

                    //Woocommerce images
                    global $product;
                    if ('' != $product && method_exists($product, 'get_gallery_image_ids')) {
                        $product_img = $product->get_gallery_image_ids();
                    }

                    //Post Thumbnail
                    $post_thumbnail = get_the_post_thumbnail_url($post, 'full');
                    $post_thumbnail_id = get_post_thumbnail_id($post);

                    //Images
                    if ((isset($images) && ! empty($images) && $images->length >= 1) || (isset($product) && ! empty($product_img)) || '' != $post_thumbnail) {
                        $i = 0;
                        //Standard img
                        if (isset($images) && ! empty($images)) {
                            if ($images->length >= 1) {
                                foreach ($images as $img) {
                                    $url = $img->getAttribute('src');
                                    $url = apply_filters('seopress_sitemaps_single_img_url', $url);
                                    if ('' != $url) {
                                        //Exclude Base64 img
                                        if (false === strpos($url, 'data:image/')) {
                                            /*
                                            *  Initiate $seopress_url['images] and needed data for the sitemap image template
                                            */

                                            if (true === seopress_is_absolute($url)) {
                                                //do nothing
                                            } else {
                                                $url = $home_url . $url;
                                            }

                                            //cleaning url
                                            $url = htmlspecialchars(urldecode(esc_attr(wp_filter_nohtml_kses($url))));

                                            //remove query strings
                                            $parse_url = wp_parse_url($url);

                                            if ( ! empty($parse_url['scheme']) && ! empty($parse_url['host']) && ! empty($parse_url['path'])) {
                                                $images_array[$i]['loc'] = '<![CDATA[' . $parse_url['scheme'] . '://' . $parse_url['host'] . $parse_url['path'] . ']]>';
                                            } else {
                                                $images_array[$i]['loc'] = '<![CDATA[' . $url . ']]>';
                                            }
                                            ++$i;
                                        }
                                    }
                                }
                            }
                        }
                        //WooCommerce img
                        if ('' != $product && '' != $product_img) {
                            foreach ($product_img as $product_attachment_id) {
                                $images_array[$i]['loc'] = '<![CDATA[' . esc_attr(wp_filter_nohtml_kses(wp_get_attachment_url($product_attachment_id))) . ']]>';
                                ++$i;
                            }
                        }
                        //Post thumbnail
                        if ('' != $post_thumbnail) {
                            $images_array[$i]['loc'] = '<![CDATA[' . $post_thumbnail . ']]>';
                            ++$i;
                        }
                    } //...end extract image
                } //... end noimageindex?
            } // ... end seopress_xml_sitemap_img_enable_option

                        // Init return sitemap
            $seopress_sitemap_url = '';

            // array with all the information needed for a sitemap url
            $seopress_url = [
                'loc' => htmlspecialchars(urldecode(get_permalink($post))),
                'mod' => $seopress_mod,
                'images' => $images_array,
                'news' => [
                    'name' => htmlspecialchars(urldecode(esc_attr(html_entity_decode(seopress_xml_sitemap_news_name_option())))),
                    'language' => $lang,
                    'publication_date' => get_the_date('c', $post),
                    'title' => htmlspecialchars(urldecode(esc_attr(html_entity_decode(get_the_title($post))))),
                    'keywords' => $seopress_keywords,
                ],
            ];

            $seopress_sitemap_url .= '<url>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<loc>';
            $seopress_sitemap_url .= $seopress_url['loc'];
            $seopress_sitemap_url .= '</loc>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<lastmod>';
            $seopress_sitemap_url .= $seopress_url['mod'];
            $seopress_sitemap_url .= '</lastmod>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<news:news>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<news:publication>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<news:name>' . $seopress_url['news']['name'] . '</news:name>';
            $seopress_sitemap_url .= "\n";

            $seopress_sitemap_url .= '<news:language>' . $seopress_url['news']['language'] . '</news:language>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '</news:publication>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '<news:publication_date>';
            $seopress_sitemap_url .= $seopress_url['news']['publication_date'];
            $seopress_sitemap_url .= '</news:publication_date>';
            $seopress_sitemap_url .= "\n";
            if ($seopress_url['news']['keywords']) {
                $seopress_sitemap_url .= '<news:keywords>';
                $seopress_sitemap_url .= $seopress_url['news']['keywords'];
                $seopress_sitemap_url .= '</news:keywords>';
                $seopress_sitemap_url .= "\n";
            }
            $seopress_sitemap_url .= '<news:title>';
            $seopress_sitemap_url .= $seopress_url['news']['title'];
            $seopress_sitemap_url .= '</news:title>';
            $seopress_sitemap_url .= "\n";
            $seopress_sitemap_url .= '</news:news>';
            $seopress_sitemap_url .= "\n";
            if ($seopress_url['images']) {
                foreach ($seopress_url['images'] as $img) {
                    $seopress_sitemap_url .= '<image:image>';
                    $seopress_sitemap_url .= "\n";

                    if ('' != $img['loc']) {
                        $seopress_sitemap_url .= '<image:loc>';
                        $seopress_sitemap_url .= $img['loc'];
                        $seopress_sitemap_url .= '</image:loc>';
                        $seopress_sitemap_url .= "\n";
                    }

                    $seopress_sitemap_url .= '</image:image>';
                    $seopress_sitemap_url .= "\n";
                }
            }
            $seopress_sitemap_url .= '</url>';
            $seopress_sitemap_url .= "\n";

            $seopress_sitemaps .= apply_filters('seopress_sitemaps_url', $seopress_sitemap_url, $seopress_url);
        }
    }
    wp_reset_postdata();

    $seopress_sitemaps .= '</urlset>';

    $seopress_sitemaps = apply_filters('seopress_sitemaps_xml_news', $seopress_sitemaps);

    return $seopress_sitemaps;
}
echo seopress_xml_sitemap_news();
