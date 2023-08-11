<?php

namespace SEOPressPro\Services;

defined('ABSPATH') || exit;


class InspectUrlGoogle {
    public function handle($postId) {
        require_once WP_PLUGIN_DIR . '/wp-seopress-pro/vendor/autoload.php';

        //Get Google API Key
        $options = get_option('seopress_instant_indexing_option_name');
        $google_api_key = isset($options['seopress_instant_indexing_google_api_key']) ? $options['seopress_instant_indexing_google_api_key'] : '';

        $data = [];

        //Check we have setup at least one API key
        if (empty($google_api_key)) {
            $data['inspect_url']['status'] = __('No API key defined from the settings tab', 'wp-seopress-pro');
            update_post_meta($postId, '_seopress_gsc_inspect_url_data', $data);
            return $data;
        }

        //URL to inspect
        $url = apply_filters('seopress_inspect_url_permalink', get_permalink($postId));

        //Site URL
        $site_url = apply_filters('seopress_inspect_url_home_url', get_home_url());

        //Build the POST request
        try {
            $client = new \Google_Client();

            $client->setAuthConfig(json_decode($google_api_key, true));
            $client->setScopes('https://www.googleapis.com/auth/webmasters.readonly');

            $service = new \Google_Service_SearchConsole($client);

            $postBody = new \Google_Service_SearchConsole_InspectUrlIndexRequest();
            $postBody->setInspectionUrl($url);
            $postBody->setSiteUrl($site_url);
            $postBody->setLanguageCode(get_locale());
            $response = $service->urlInspection_index->inspect($postBody);

            $response = json_decode(json_encode($response));
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        update_post_meta($postId, '_seopress_gsc_inspect_url_data', $response);


        return $response;
    }
}
