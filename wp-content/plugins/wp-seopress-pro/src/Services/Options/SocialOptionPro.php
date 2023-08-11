<?php

namespace SEOPressPro\Services\Options;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

use SEOPress\Services\Options\SocialOption;

class SocialOptionPro extends SocialOption {
    /**
     * @since 4.5.0
     *
     * @return string
     */
    public function getSocialKnowledgeImage() {
        return $this->searchOptionByKey('seopress_social_knowledge_img');
    }
}
