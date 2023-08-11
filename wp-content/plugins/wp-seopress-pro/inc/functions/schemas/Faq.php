<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//FAQ JSON-LD
function seopress_automatic_rich_snippets_faq_option($schema_datas) {
    //if no data
    if (0 != count(array_filter($schema_datas, 'strlen'))) {
        $faq_q 							= $schema_datas['q'];
        $faq_a 							= $schema_datas['a'];
        if (('' != $faq_q) && ('' != $faq_a)) {
            $json = [
                '@context'   => seopress_check_ssl() . 'schema.org',
                '@type'      => 'FAQPage',
                'name'       => 'FAQ',
                'mainEntity' => [
                    '@type'          => 'Question',
                    'name'           => $faq_q,
                    'answerCount'    => 1,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $faq_a,
                    ],
                ],
            ];

            $json = array_filter($json);

            $json = apply_filters('seopress_schemas_auto_faq_json', $json);

            $json = '<script type="application/ld+json">' . json_encode($json) . '</script>' . "\n";

            $json = apply_filters('seopress_schemas_auto_faq_html', $json);

            echo $json;
        }
    }
}
