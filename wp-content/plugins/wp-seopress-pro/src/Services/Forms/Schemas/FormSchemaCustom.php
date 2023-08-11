<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;

class FormSchemaCustom extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_custom':
                return 'textarea';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_custom':
                return __('Custom schema', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_custom':
                return __('eg: <script type="application/ld+json">{
                    "@context": "https://schema.org/",
                    "@type": "Review",
                    "itemReviewed": {
                    "@type": "Restaurant",
                    "image": "http://www.example.com/seafood-restaurant.jpg",
                    "name": "Legal Seafood",
                    "servesCuisine": "Seafood",
                    "telephone": "1234567",
                    "address" :{
                        "@type": "PostalAddress",
                        "streetAddress": "123 William St",
                        "addressLocality": "New York",
                        "addressRegion": "NY",
                        "postalCode": "10038",
                        "addressCountry": "US"
                    }
                    },
                    "reviewRating": {
                    "@type": "Rating",
                    "ratingValue": "4"
                    },
                    "name": "A good seafood place.",
                    "author": {
                    "@type": "Person",
                    "name": "Bob Smith"
                    },
                    "reviewBody": "The seafood is great.",
                    "publisher": {
                    "@type": "Organization",
                    "name": "Washington Times"
                    }
                }</script>', 'wp-seopress-pro');
        }
    }

    protected function getDescriptionByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_custom':
                $docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : [];
                if ( ! isset($docs['schemas']['dynamic'])) {
                    return '';
                }

                 /* translators: %s: documentation link */
                return sprintf(__('<a href="%s" target="_blank">You can use dynamic variables in your schema.</a>', 'wp-seopress-pro'), $docs['schemas']['dynamic']);
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_custom',
                'class' => 'seopress-textarea-high-size'
            ],
        ];
    }
}
