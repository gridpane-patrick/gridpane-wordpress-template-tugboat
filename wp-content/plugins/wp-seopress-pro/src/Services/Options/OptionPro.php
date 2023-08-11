<?php

namespace SEOPressPro\Services\Options;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

use SEOPressPro\Services\Options\Schemas\LocalBusinessOptions;
use SEOPressPro\Services\Options\Schemas\PublisherOptions;

class OptionPro {
    use LocalBusinessOptions;
    use PublisherOptions;

    /**
     * @since 4.5.0
     *
     * @return array
     */
    public function getOption() {
        if (is_network_admin() && is_multisite()) {
            return get_option('seopress_pro_mu_option_name');
        } else {
            return get_option('seopress_pro_option_name');
        }
    }

    /**
     * @since 4.5.0
     *
     * @return string|null
     *
     * @param string $key
     */
    protected function searchOptionByKey($key) {
        $data = $this->getOption();

        if (empty($data)) {
            return null;
        }

        if ( ! isset($data[$key])) {
            return null;
        }

        return $data[$key];
    }

    /**
     * @since 4.6.0
     *
     * @return string
     */
    public function getRichSnippetEnable() {
        return $this->searchOptionByKey('seopress_rich_snippets_enable');
    }

    /**
     * @since 4.6.0
     *
     * @return string
     */
    public function getArticlesPublisher() {
        return $this->searchOptionByKey('seopress_social_knowledge_name');
    }

    /**
     * @since 4.6.0
     *
     * @return string
     */
    public function getRichSnippetsSiteNavigation() {
        return $this->searchOptionByKey('seopress_rich_snippets_site_nav');
    }
}
