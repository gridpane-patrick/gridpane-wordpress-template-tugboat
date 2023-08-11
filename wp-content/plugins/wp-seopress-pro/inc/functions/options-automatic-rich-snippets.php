<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Rich Snippets
//=================================================================================================
//Rich Snippets JSON-LD
if ('1' == seopress_rich_snippets_enable_option()) { //Is RS enable
    if (is_single() || is_singular()) {
        //If Disable all automatic schemas doesn't exist, then continue
        if ( ! get_post_meta(get_the_ID(), '_seopress_pro_rich_snippets_disable_all', true)) {
            //Manual option
            function seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace) {
                if ( ! empty($post_meta_key)) {
                    foreach ($post_meta_key as $key => $value) {
                        //Init
                        $_post_meta_value = null;


                        //Single datas
                        if ('opening_hours' == $key) {
                            if ( ! empty($seopress_pro_schemas[0][$id]['rich_snippets_' . $schema_name][$key]) && function_exists('seopress_if_key_exists') && true === seopress_if_key_exists($seopress_pro_schemas[0][$id]['rich_snippets_' . $schema_name][$key], 'open')) {
                                $_post_meta_value = $seopress_pro_schemas[0][$id]['rich_snippets_' . $schema_name][$key];
                            } else {
                                $_post_meta_value = get_post_meta($id, $value, true);
                                $_post_meta_value = $_post_meta_value['seopress_pro_rich_snippets_lb_opening_hours'];
                            }
                        } else {
                            $post_meta_value = get_post_meta($id, $value, true);
                        }

                        //Global datas
                        $manual_global = get_post_meta($id, $value . '_manual_global', true);

                        $manual_img_global = get_post_meta($id, $value . '_manual_img_global', true);
                        $manual_img_library_global = get_post_meta($id, $value . '_manual_img_library_global', true);

                        $manual_date_global = get_post_meta($id, $value . '_manual_date_global', true);

                        $manual_time_global = get_post_meta($id, $value . '_manual_time_global', true);

                        $manual_rating_global = get_post_meta($id, $value . '_manual_rating_global', true);

                        $manual_custom_global = get_post_meta($id, $value . '_manual_custom_global', true);

                        $cf = get_post_meta($id, $value . '_cf', true);

                        $tax = get_post_meta($id, $value . '_tax', true);

                        $lb = get_post_meta($id, $value . '_lb', true);

                        //From current single post
                        if ( ! empty($_post_meta_value) && 7 === count($_post_meta_value)) {
                            $_post_meta_value = $_post_meta_value;
                        } elseif ('manual_single' == $post_meta_value || 'manual_img_single' == $post_meta_value || 'manual_date_single' == $post_meta_value || 'manual_time_single' == $post_meta_value || 'manual_rating_single' == $post_meta_value || 'manual_custom_single' == $post_meta_value) {
                            if (isset($seopress_pro_schemas[0][$id]['rich_snippets_' . $schema_name][$key])) {
                                $_post_meta_value = $seopress_pro_schemas[0][$id]['rich_snippets_' . $schema_name][$key];
                            }
                        } elseif ('manual_global' == $post_meta_value) {
                            if ('' != $manual_global) {
                                $_post_meta_value = $manual_global;
                            }
                        } elseif ('manual_img_global' == $post_meta_value) {
                            if ('' != $manual_img_global) {
                                $_post_meta_value = $manual_img_global;
                            }
                        } elseif ('manual_img_library_global' == $post_meta_value) {
                            if ('' != $manual_img_library_global) {
                                $_post_meta_value = $manual_img_library_global;
                            }
                        } elseif ('manual_date_global' == $post_meta_value) {
                            if ('' != $manual_date_global) {
                                $_post_meta_value = $manual_date_global;
                            }
                        } elseif ('manual_time_global' == $post_meta_value) {
                            if ('' != $manual_time_global) {
                                $_post_meta_value = $manual_time_global;
                            }
                        } elseif ('manual_rating_global' == $post_meta_value) {
                            if ('' != $manual_rating_global) {
                                $_post_meta_value = $manual_rating_global;
                            }
                        } elseif ('manual_custom_global' == $post_meta_value) {
                            if ('' != $manual_custom_global) {
                                $_post_meta_value = $manual_custom_global;
                            }
                        } elseif ('manual_lb_global' == $post_meta_value) {
                            if ('' != $lb) {
                                $_post_meta_value = $lb;
                            }
                        } elseif ('custom_fields' == $post_meta_value) {
                            if ('' != $cf) {
                                $_post_meta_value = get_post_meta(get_the_ID(), $cf, true);
                            }
                        } elseif ('custom_taxonomy' == $post_meta_value) {
                            if ('' != $tax) {
                                $_post_meta_value = '';
                                if (taxonomy_exists($tax)) {
                                    $terms = wp_get_post_terms(get_the_ID(), $tax, ['fields' => 'names']);
                                    if ( ! empty($terms) && ! is_wp_error($terms)) {
                                        $_post_meta_value = $terms[0];
                                    }
                                }
                            }
                        } elseif ('none' != $post_meta_value) { //From schema single post
                            $_post_meta_value = str_replace($sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace, $post_meta_value);
                        }

                        //Push value to array
                        $schema_datas[$key] = $_post_meta_value;
                    }

                    return $schema_datas;
                }
            }

            require_once dirname(__FILE__) . '/schemas/Article.php';
            require_once dirname(__FILE__) . '/schemas/LocalBusiness.php';
            require_once dirname(__FILE__) . '/schemas/Faq.php';
            require_once dirname(__FILE__) . '/schemas/Course.php';
            require_once dirname(__FILE__) . '/schemas/Recipe.php';
            require_once dirname(__FILE__) . '/schemas/Job.php';
            require_once dirname(__FILE__) . '/schemas/Video.php';
            require_once dirname(__FILE__) . '/schemas/Event.php';
            require_once dirname(__FILE__) . '/schemas/Product.php';
            require_once dirname(__FILE__) . '/schemas/SoftwareApp.php';
            require_once dirname(__FILE__) . '/schemas/Service.php';
            require_once dirname(__FILE__) . '/schemas/Review.php';
            require_once dirname(__FILE__) . '/schemas/Custom.php';

            //Dynamic variables
            global $post;
            global $product;

            /*Excerpt length*/
            $seopress_excerpt_length = 50;
            $seopress_excerpt_length = apply_filters('seopress_excerpt_length', $seopress_excerpt_length);

            /*Excerpt*/
            $seopress_excerpt = '';
            if ( ! is_404() && '' != $post) {
                if (has_excerpt($post->ID)) {
                    $seopress_excerpt = get_the_excerpt();
                }
            }
            if ('' != $seopress_excerpt) {
                $seopress_get_the_excerpt = wp_trim_words(esc_attr(stripslashes_deep(wp_filter_nohtml_kses(wp_strip_all_tags(strip_shortcodes($seopress_excerpt), true)))), $seopress_excerpt_length);
            } elseif ('' != $post) {
                if ('' != get_post_field('post_content', $post->ID)) {
                    $seopress_get_the_excerpt = wp_trim_words(esc_attr(stripslashes_deep(wp_filter_nohtml_kses(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $post->ID), true))))), $seopress_excerpt_length);
                } else {
                    $seopress_get_the_excerpt = null;
                }
            } else {
                $seopress_get_the_excerpt = null;
            }

            if ('' != $post) {
                if ('' != get_post_field('post_content', $post->ID)) {
                    $seopress_get_the_content = wp_trim_words(esc_attr(stripslashes_deep(wp_filter_nohtml_kses(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $post->ID), true))))), $seopress_excerpt_length);
                } else {
                    $seopress_get_the_content = null;
                }
            } else {
                $seopress_get_the_content = null;
            }

            /*Author name*/
            $the_author_meta = '';
            $the_author_meta = get_the_author_meta('display_name', $post->post_author);

            if ( ! function_exists('seopress_social_knowledge_img_option')) {
                function seopress_social_knowledge_img_option() {
                    $seopress_social_knowledge_img_option = get_option('seopress_social_option_name');
                    if ( ! empty($seopress_social_knowledge_img_option)) {
                        foreach ($seopress_social_knowledge_img_option as $key => $seopress_social_knowledge_img_value) {
                            $options[$key] = $seopress_social_knowledge_img_value;
                        }
                        if (isset($seopress_social_knowledge_img_option['seopress_social_knowledge_img'])) {
                            return $seopress_social_knowledge_img_option['seopress_social_knowledge_img'];
                        }
                    }
                }
            }

            /*Date on sale from*/
            $get_date_on_sale_from = '';
            if (isset($product) && method_exists($product, 'get_date_on_sale_from')) {
                $get_date_on_sale_from = $product->get_date_on_sale_from();
                if ('' != $get_date_on_sale_from) {
                    $get_date_on_sale_from = $get_date_on_sale_from->date('m-d-Y');
                }
            }

            /*Date on sale to*/
            $get_date_on_sale_to = '';
            if (isset($product) && method_exists($product, 'get_date_on_sale_to')) {
                $get_date_on_sale_to = $product->get_date_on_sale_to();
                if ('' != $get_date_on_sale_to) {
                    $get_date_on_sale_to = $get_date_on_sale_to->date('m-d-Y');
                }
            }

            /*product cat*/
            $product_cat_term_list = '';
            if (taxonomy_exists('product_cat')) {
                $terms = wp_get_post_terms(get_the_ID(), 'product_cat', ['fields' => 'names']);
                if ( ! empty($terms) && ! is_wp_error($terms)) {
                    $product_cat_term_list = $terms[0];
                }
            }

            /*regular price*/
            $get_regular_price = '';
            if (isset($product) && method_exists($product, 'get_regular_price')) {
                $get_regular_price = $product->get_regular_price();
            }

            /*sale price*/
            $get_sale_price = '';
            if (isset($product) && method_exists($product, 'get_sale_price')) {
                $get_sale_price = $product->get_sale_price();
            }

            /*sale price with tax (regular price as fallback if not available)*/
            $get_sale_price_with_tax = '';
            if (isset($product) && method_exists($product, 'get_price') && function_exists('wc_get_price_including_tax')) {
                $get_sale_price_with_tax = wc_get_price_including_tax($product, ['price' => $get_sale_price]);
            }

            /*sku*/
            $get_sku = '';
            if (isset($product) && method_exists($product, 'get_sku')) {
                $get_sku = $product->get_sku();
            }

            /*barcode type*/
            $get_barcode_type = '';
            if (isset($product) && method_exists($product, 'get_id') && get_post_meta($product->get_id(), 'sp_wc_barcode_type_field', true)) {
                $get_barcode_type = get_post_meta($product->get_id(), 'sp_wc_barcode_type_field', true);
            }

            /*barcode*/
            $get_barcode = '';
            if (isset($product) && method_exists($product, 'get_id') && get_post_meta($product->get_id(), 'sp_wc_barcode_field', true)) {
                $get_barcode = get_post_meta($product->get_id(), 'sp_wc_barcode_field', true);
            }

            /*stock*/
            $get_stock = '';
            if (isset($product) && method_exists($product, 'managing_stock') && true === $product->managing_stock()) { //if managing stock
                if (method_exists($product, 'is_in_stock') && true === $product->is_in_stock()) {
                    $get_stock = seopress_check_ssl() . 'schema.org/InStock';
                } else { //OutOfStock
                    $get_stock = seopress_check_ssl() . 'schema.org/OutOfStock';
                }
            } elseif (isset($product) && method_exists($product, 'managing_stock') && false === $product->managing_stock() && method_exists($product, 'get_stock_status') && $product->get_stock_status()) {
                if ('instock' == $product->get_stock_status()) {
                    $get_stock = seopress_check_ssl() . 'schema.org/InStock';
                } else { //OutOfStock
                    $get_stock = seopress_check_ssl() . 'schema.org/OutOfStock';
                }
            }

            $sp_schemas_dyn_variables = [
                'site_title',
                'tagline',
                'site_url',
                'post_id',
                'post_title',
                'post_excerpt',
                'post_content',
                'post_permalink',
                'post_author_name',
                'post_date',
                'post_updated',
                'knowledge_graph_logo',
                'post_thumbnail',
                'post_author_picture',
                'product_regular_price',
                'product_sale_price',
                'product_price_with_tax',
                'product_date_from',
                'product_date_to',
                'product_sku',
                'product_barcode_type',
                'product_barcode',
                'product_category',
                'product_stock',
            ];

            $sp_schemas_dyn_variables = apply_filters('seopress_schemas_dyn_variables', $sp_schemas_dyn_variables);

            $sp_schemas_dyn_variables_replace = [
                get_bloginfo('name'),
                get_bloginfo('description'),
                get_home_url(),
                get_the_ID(),
                the_title_attribute('echo=0'),
                $seopress_get_the_excerpt,
                $seopress_get_the_content,
                get_permalink(),
                $the_author_meta,
                get_the_date('c'),
                get_the_modified_date('c'),
                seopress_social_knowledge_img_option(),
                get_the_post_thumbnail_url($post, 'full'),
                get_avatar_url(get_the_author_meta('ID')),
                $get_regular_price,
                $get_sale_price,
                $get_sale_price_with_tax,
                $get_date_on_sale_from,
                $get_date_on_sale_to,
                $get_sku,
                $get_barcode_type,
                $get_barcode,
                $product_cat_term_list,
                $get_stock,
            ];

            $sp_schemas_dyn_variables_replace = apply_filters('seopress_schemas_dyn_variables_replace', $sp_schemas_dyn_variables_replace);

            //Request schemas based on post type / rules
            $args = [
                'post_type' => 'seopress_schemas',
                'posts_per_page' => -1,
                //'fields' => 'ids',
            ];

            $sp_schemas_query = new WP_Query($args);
            $current_post = $post;
            $sp_schemas_ids = [];

            if ($sp_schemas_query->have_posts()) {
                while ($sp_schemas_query->have_posts()) {
                    $sp_schemas_query->the_post();
                    if (get_post_meta(get_the_ID(), '_seopress_pro_rich_snippets_rules', true) &&
                        seopress_is_content_valid_for_schemas($current_post->ID)) {
                        $sp_schemas_ids[] = get_the_ID();
                    }
                }
            }
            wp_reset_postdata();

            if ( ! empty($sp_schemas_ids)) {
                foreach ($sp_schemas_ids as $id) {
                    //Datas
                    $schema_datas = [];

                    //Type
                    $seopress_pro_rich_snippets_type = get_post_meta($id, '_seopress_pro_rich_snippets_type', true);

                    //Datas
                    $seopress_pro_schemas = get_post_meta($post->ID, '_seopress_pro_schemas');

                    $disable = get_post_meta($post->ID, '_seopress_pro_rich_snippets_disable', true);
                    if (is_array($disable) && array_key_exists($id, $disable)) {
                        continue;
                    }

                    //Article
                    if ('articles' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'article';

                        $post_meta_key = [
                            'type' => '_seopress_pro_rich_snippets_article_type',
                            'title' => '_seopress_pro_rich_snippets_article_title',
                            'desc' => '_seopress_pro_rich_snippets_article_desc',
                            'author' => '_seopress_pro_rich_snippets_article_author',
                            'img' => '_seopress_pro_rich_snippets_article_img',
                            'coverage_start_date' => '_seopress_pro_rich_snippets_article_coverage_start_date',
                            'coverage_start_time' => '_seopress_pro_rich_snippets_article_coverage_start_time',
                            'coverage_end_date' => '_seopress_pro_rich_snippets_article_coverage_end_date',
                            'coverage_end_time' => '_seopress_pro_rich_snippets_article_coverage_end_time',
                            'speakable' => '_seopress_pro_rich_snippets_article_speakable',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_articles_option($schema_datas);
                    }

                    //Local Business
                    if ('localbusiness' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'lb';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_lb_name',
                            'type' => '_seopress_pro_rich_snippets_lb_type',
                            'img' => '_seopress_pro_rich_snippets_lb_img',
                            'street_addr' => '_seopress_pro_rich_snippets_lb_street_addr',
                            'city' => '_seopress_pro_rich_snippets_lb_city',
                            'state' => '_seopress_pro_rich_snippets_lb_state',
                            'pc' => '_seopress_pro_rich_snippets_lb_pc',
                            'country' => '_seopress_pro_rich_snippets_lb_country',
                            'lat' => '_seopress_pro_rich_snippets_lb_lat',
                            'lon' => '_seopress_pro_rich_snippets_lb_lon',
                            'website' => '_seopress_pro_rich_snippets_lb_website',
                            'tel' => '_seopress_pro_rich_snippets_lb_tel',
                            'price' => '_seopress_pro_rich_snippets_lb_price',
                            'serves_cuisine' => '_seopress_pro_rich_snippets_lb_serves_cuisine',
                            'menu' => '_seopress_pro_rich_snippets_lb_menu',
                            'accepts_reservations' => '_seopress_pro_rich_snippets_lb_accepts_reservations',
                            'opening_hours' => '_seopress_pro_rich_snippets_lb_opening_hours',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_lb_option($schema_datas);
                    }

                    //FAQ
                    if ('faq' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'faq';

                        $post_meta_key = [
                            'q' => '_seopress_pro_rich_snippets_faq_q',
                            'a' => '_seopress_pro_rich_snippets_faq_a',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_faq_option($schema_datas);
                    }

                    //Courses
                    if ('courses' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'courses';

                        $post_meta_key = [
                            'title' => '_seopress_pro_rich_snippets_courses_title',
                            'desc' => '_seopress_pro_rich_snippets_courses_desc',
                            'school' => '_seopress_pro_rich_snippets_courses_school',
                            'website' => '_seopress_pro_rich_snippets_courses_website',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_courses_option($schema_datas);
                    }

                    //Recipes
                    if ('recipes' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'recipes';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_recipes_name',
                            'desc' => '_seopress_pro_rich_snippets_recipes_desc',
                            'cat' => '_seopress_pro_rich_snippets_recipes_cat',
                            'img' => '_seopress_pro_rich_snippets_recipes_img',
                            'video' => '_seopress_pro_rich_snippets_recipes_video',
                            'prep_time' => '_seopress_pro_rich_snippets_recipes_prep_time',
                            'cook_time' => '_seopress_pro_rich_snippets_recipes_cook_time',
                            'calories' => '_seopress_pro_rich_snippets_recipes_calories',
                            'yield' => '_seopress_pro_rich_snippets_recipes_yield',
                            'keywords' => '_seopress_pro_rich_snippets_recipes_keywords',
                            'cuisine' => '_seopress_pro_rich_snippets_recipes_cuisine',
                            'ingredient' => '_seopress_pro_rich_snippets_recipes_ingredient',
                            'instructions' => '_seopress_pro_rich_snippets_recipes_instructions',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_recipes_option($schema_datas);
                    }

                    //Jobs
                    if ('jobs' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'jobs';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_jobs_name',
                            'desc' => '_seopress_pro_rich_snippets_jobs_desc',
                            'date_posted' => '_seopress_pro_rich_snippets_jobs_date_posted',
                            'valid_through' => '_seopress_pro_rich_snippets_jobs_valid_through',
                            'employment_type' => '_seopress_pro_rich_snippets_jobs_employment_type',
                            'identifier_name' => '_seopress_pro_rich_snippets_jobs_identifier_name',
                            'identifier_value' => '_seopress_pro_rich_snippets_jobs_identifier_value',
                            'hiring_organization' => '_seopress_pro_rich_snippets_jobs_hiring_organization',
                            'hiring_same_as' => '_seopress_pro_rich_snippets_jobs_hiring_same_as',
                            'hiring_logo' => '_seopress_pro_rich_snippets_jobs_hiring_logo',
                            'hiring_logo_width' => '_seopress_pro_rich_snippets_jobs_hiring_logo_width',
                            'hiring_logo_height' => '_seopress_pro_rich_snippets_jobs_hiring_logo_height',
                            'address_street' => '_seopress_pro_rich_snippets_jobs_address_street',
                            'address_locality' => '_seopress_pro_rich_snippets_jobs_address_locality',
                            'address_region' => '_seopress_pro_rich_snippets_jobs_address_region',
                            'postal_code' => '_seopress_pro_rich_snippets_jobs_postal_code',
                            'country' => '_seopress_pro_rich_snippets_jobs_country',
                            'remote' => '_seopress_pro_rich_snippets_jobs_remote',
                            'direct_apply' => '_seopress_pro_rich_snippets_jobs_direct_apply',
                            'salary' => '_seopress_pro_rich_snippets_jobs_salary',
                            'salary_currency' => '_seopress_pro_rich_snippets_jobs_salary_currency',
                            'salary_unit' => '_seopress_pro_rich_snippets_jobs_salary_unit',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_jobs_option($schema_datas);
                    }

                    //Videos
                    if ('videos' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'videos';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_videos_name',
                            'description' => '_seopress_pro_rich_snippets_videos_description',
                            'img' => '_seopress_pro_rich_snippets_videos_img',
                            'duration' => '_seopress_pro_rich_snippets_videos_duration',
                            'url' => '_seopress_pro_rich_snippets_videos_url',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_videos_option($schema_datas);
                    }

                    //Events
                    if ('events' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'events';

                        $post_meta_key = [
                            'type' => '_seopress_pro_rich_snippets_events_type',
                            'name' => '_seopress_pro_rich_snippets_events_name',
                            'desc' => '_seopress_pro_rich_snippets_events_desc',
                            'img' => '_seopress_pro_rich_snippets_events_img',
                            'start_date' => '_seopress_pro_rich_snippets_events_start_date',
                            'start_date_timezone' => '_seopress_pro_rich_snippets_events_start_date_timezone',
                            'start_time' => '_seopress_pro_rich_snippets_events_start_time',
                            'end_date' => '_seopress_pro_rich_snippets_events_end_date',
                            'end_time' => '_seopress_pro_rich_snippets_events_end_time',
                            'previous_start_date' => '_seopress_pro_rich_snippets_events_previous_start_date',
                            'previous_start_time' => '_seopress_pro_rich_snippets_events_previous_start_time',
                            'location_name' => '_seopress_pro_rich_snippets_events_location_name',
                            'location_url' => '_seopress_pro_rich_snippets_events_location_url',
                            'location_address' => '_seopress_pro_rich_snippets_events_location_address',
                            'offers_name' => '_seopress_pro_rich_snippets_events_offers_name',
                            'offers_cat' => '_seopress_pro_rich_snippets_events_offers_cat',
                            'offers_price' => '_seopress_pro_rich_snippets_events_offers_price',
                            'offers_price_currency' => '_seopress_pro_rich_snippets_events_offers_price_currency',
                            'offers_availability' => '_seopress_pro_rich_snippets_events_offers_availability',
                            'offers_valid_from_date' => '_seopress_pro_rich_snippets_events_offers_valid_from_date',
                            'offers_valid_from_time' => '_seopress_pro_rich_snippets_events_offers_valid_from_time',
                            'offers_url' => '_seopress_pro_rich_snippets_events_offers_url',
                            'performer' => '_seopress_pro_rich_snippets_events_performer',
                            'organizer_name' => '_seopress_pro_rich_snippets_events_organizer_name',
                            'organizer_url' => '_seopress_pro_rich_snippets_events_organizer_url',
                            'status' => '_seopress_pro_rich_snippets_events_status',
                            'attendance_mode' => '_seopress_pro_rich_snippets_events_attendance_mode',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_events_option($schema_datas);
                    }

                    //Products
                    if ('products' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'product';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_product_name',
                            'description' => '_seopress_pro_rich_snippets_product_description',
                            'img' => '_seopress_pro_rich_snippets_product_img',
                            'price' => '_seopress_pro_rich_snippets_product_price',
                            'price_valid_date' => '_seopress_pro_rich_snippets_product_price_valid_date',
                            'sku' => '_seopress_pro_rich_snippets_product_sku',
                            'brand' => '_seopress_pro_rich_snippets_product_brand',
                            'global_ids' => '_seopress_pro_rich_snippets_product_global_ids',
                            'global_ids_value' => '_seopress_pro_rich_snippets_product_global_ids_value',
                            'currency' => '_seopress_pro_rich_snippets_product_price_currency',
                            'condition' => '_seopress_pro_rich_snippets_product_condition',
                            'availability' => '_seopress_pro_rich_snippets_product_availability',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_products_option($schema_datas);
                    }

                    //Software Application
                    if ('softwareapp' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'softwareapp';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_softwareapp_name',
                            'os' => '_seopress_pro_rich_snippets_softwareapp_os',
                            'cat' => '_seopress_pro_rich_snippets_softwareapp_cat',
                            'price' => '_seopress_pro_rich_snippets_softwareapp_price',
                            'currency' => '_seopress_pro_rich_snippets_softwareapp_currency',
                            'rating' => '_seopress_pro_rich_snippets_softwareapp_rating',
                            'max_rating' => '_seopress_pro_rich_snippets_softwareapp_max_rating',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_softwareapp_option($schema_datas);
                    }

                    //Service
                    if ('services' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'service';

                        $post_meta_key = [
                            'name' => '_seopress_pro_rich_snippets_service_name',
                            'type' => '_seopress_pro_rich_snippets_service_type',
                            'description' => '_seopress_pro_rich_snippets_service_description',
                            'img' => '_seopress_pro_rich_snippets_service_img',
                            'area' => '_seopress_pro_rich_snippets_service_area',
                            'provider_name' => '_seopress_pro_rich_snippets_service_provider_name',
                            'lb_img' => '_seopress_pro_rich_snippets_service_lb_img',
                            'provider_mobility' => '_seopress_pro_rich_snippets_service_provider_mobility',
                            'slogan' => '_seopress_pro_rich_snippets_service_slogan',
                            'street_addr' => '_seopress_pro_rich_snippets_service_street_addr',
                            'city' => '_seopress_pro_rich_snippets_service_city',
                            'state' => '_seopress_pro_rich_snippets_service_state',
                            'pc' => '_seopress_pro_rich_snippets_service_pc',
                            'country' => '_seopress_pro_rich_snippets_service_country',
                            'lat' => '_seopress_pro_rich_snippets_service_lat',
                            'lon' => '_seopress_pro_rich_snippets_service_lon',
                            'tel' => '_seopress_pro_rich_snippets_service_tel',
                            'price' => '_seopress_pro_rich_snippets_service_price',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_services_option($schema_datas);
                    }

                    //Review
                    if ('review' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'review';

                        $post_meta_key = [
                            'item' => '_seopress_pro_rich_snippets_review_item',
                            'item_type' => '_seopress_pro_rich_snippets_review_item_type',
                            'img' => '_seopress_pro_rich_snippets_review_img',
                            'rating' => '_seopress_pro_rich_snippets_review_rating',
                            'max_rating' => '_seopress_pro_rich_snippets_review_max_rating',
                            'body' => '_seopress_pro_rich_snippets_review_body',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_review_option($schema_datas);
                    }

                    //Custom
                    if ('custom' == $seopress_pro_rich_snippets_type) {
                        //Schema type
                        $schema_name = 'custom';

                        $post_meta_key = [
                            'custom' => '_seopress_pro_rich_snippets_custom',
                        ];

                        //Get datas
                        $schema_datas = seopress_automatic_rich_snippets_manual_option($id, $schema_name, $post_meta_key, $seopress_pro_schemas, $sp_schemas_dyn_variables, $sp_schemas_dyn_variables_replace);

                        //Output schema in JSON-LD
                        seopress_automatic_rich_snippets_custom_option($schema_datas);
                    }
                }
            }
        }
    }
}

/**
 * Check of the post is valid for any schema.
 *
 * @since 3.8.1
 *
 * @author Julio Potier
 *
 * @param (int) $post_id
 *
 * @return (bool)
 **/
function seopress_is_content_valid_for_schemas($post_id) {
    $_post = get_post($post_id);
    $_cpt = get_post_type($_post);
    $_taxos = get_post_taxonomies($_post);
    $_terms = array_flip(wp_list_pluck(wp_get_post_terms($post_id, array_keys(seopress_get_taxonomies())), 'term_id'));
    $rules = get_post_meta(get_the_ID(), '_seopress_pro_rich_snippets_rules', true);

    if ( ! is_array($rules)) {
        $rules = seopress_get_default_schemas_rules($rules);
    }
    $conditions = seopress_get_schemas_conditions();
    $filters = seopress_get_schemas_filters();
    $html = '';
    foreach ($rules as $or => $values) {
        $flag = 0;
        foreach ($values as $and => $value) {
            $filter = $filters[$value['filter']];
            $cond = $conditions[$value['cond']];
            if ('post_type' === $value['filter'] && post_type_exists($value['cpt']) &&
                (($value['cpt'] === $_cpt && 'equal' === $value['cond']) || ($value['cpt'] !== $_cpt && 'not_equal' === $value['cond']))
            ) {
                ++$flag;
            }
            if ('taxonomy' === $value['filter'] && term_exists((int) $value['taxo']) &&
                ((isset($_terms[$value['taxo']]) && 'equal' === $value['cond']) || ( ! isset($_terms[$value['taxo']]) && 'not_equal' === $value['cond']))
            ) {
                ++$flag;
            }
            if (
                'postId' === $value['filter'] &&
                (((int) $value['postId'] === (int) $post_id && 'equal' === $value['cond']) || ((int) $value['postId'] !== (int) $post_id && 'not_equal' === $value['cond']))
            ) {
                ++$flag;
            }

            if ($flag === count($values)) {
                return true;
            }
        }
    }

    return false;
}
