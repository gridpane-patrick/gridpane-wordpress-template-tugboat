<?php

namespace SEOPressPro\Services;

defined('ABSPATH') || exit;

use SEOPressPro\Models\Table\TableInterface;
use SEOPressPro\Core\Table\TableFactory;
use SEOPressPro\Models\Table\TableStructure;
use SEOPressPro\Models\Table\TableColumn;
use SEOPressPro\Models\Table\Table;

class SignificantKeywords {
    public function getFullContentByPost($post) {
        setup_postdata($post);

        ob_start();
        $content = \do_shortcode($post->post_content);
        ob_end_clean();

        wp_reset_postdata();

        return $content;
    }

    public function cleanContent($content) {
        $content = strtolower($content);
        $content = sanitize_text_field($content);

        return $content;
    }

    public function getWordsByGroup($content) {
        return array_count_values(str_word_count($content, 1));
    }

    public function getTotalWords($content) {
        return array_sum($this->getWordsByGroup($content));
    }

    /**
     *
     * @param string $content
     * @return array
     */
    public function retrieveSignificantKeywords($content) {
        $content = $this->cleanContent($content);

        if (strlen($content) < apply_filters('seopress_pro_minimum_length_content_linking', 200)) {
            return [];
        }

        $languagesSupported = apply_filters('seopress_pro_stop_words_languages_supported_keywords', ['en','fr']);
        $stopWords = seopress_pro_get_service('StopWords')->setLanguages($languagesSupported);
        $content = $stopWords->clean($content);

        $words = $this->getWordsByGroup($content);

        $words = array_filter($words, function ($item) {
            return strlen($item) >= apply_filters('seopress_pro_significant_keyword_min_length', 3);
        }, ARRAY_FILTER_USE_KEY);
        arsort($words);

        $words = array_slice($words, 0, 20);

        return $words;
    }

    public function prepareWordsToInsert($words, $postId, $content) {
        $data = [];
        $total = $this->getTotalWords($content);
        foreach ($words as $word => $count) {
            $tf = $count / max(1, $total); // prevent div by 0
            $tf = str_replace(',', '.', $tf);

            $data[] = [
                'post_id' => $postId,
                'word' => $word,
                'count' => $count,
                'tf' => $tf
            ];
        }

        return $data;
    }

    /**
     *
     * @param array $words
     * @param string $content
     * @param int $postId
     * @return array
     */
    public function computeKeywords($words, $content, $postId) {
        $data = [];
        $content = $this->cleanContent($content);

        $total = $this->getTotalWords($content);
        $totalDocuments = seopress_pro_get_service('SignificantKeywordsRepository')->countAllDocuments();

        foreach ($words as $word => $count) {
            $allWordsCorrespondent = seopress_pro_get_service('SignificantKeywordsRepository')->getAllWordsCorrespondent($word, $postId);
            if (empty($allWordsCorrespondent)) {
                continue;
            }
            $totalWordsCorrespondent = count($allWordsCorrespondent);
            $idf = log10($totalDocuments / max(1, $totalWordsCorrespondent));
            $idf = is_infinite($idf) ? 0 : $idf;

            $bestCorrespondent = null;
            $bestScore = 0;
            $i = 0;
            do {
                $wordCorrespondent = (array) $allWordsCorrespondent[$i];
                $score = $wordCorrespondent['tf'] * $idf;

                if ($score > $bestScore) {
                    $bestCorrespondent = $wordCorrespondent;
                    $bestScore = $score;
                }
                $i++;
            } while (isset($allWordsCorrespondent[$i]));



            $data[] = [
                'word' => $word,
                'count' => (int) $count,
                'documents' => $totalWordsCorrespondent,
                'idf' => $idf,
                'suggestion' => $bestCorrespondent,
                'score' => isset($bestCorrespondent['tf']) ? $bestCorrespondent['tf'] * $idf : 0,
                'title' => isset($bestCorrespondent['post_id']) ? get_the_title($bestCorrespondent['post_id']) : '',
                'post_id' => isset($bestCorrespondent['post_id']) ? $bestCorrespondent['post_id'] : null
            ];
        }

        $temp = array_unique(array_column($data, 'post_id'));
        $data = array_intersect_key($data, $temp);

        usort($data, function ($a, $b) {
            return $a['score'] - $b['score'];
        });

        foreach ($data as $key => $item) {
            $data[$key]['permalink'] = get_permalink($item['post_id']);

            $editLink = '';
            try {
                $post = get_post($item['post_id']);
                $post_type_object = get_post_type_object($post->post_type);
                $action = '&action=edit';
                $editLink = admin_url(sprintf($post_type_object->_edit_link . $action, $post->ID));
            } catch (\Exception $e) {
            }


            $data[$key]['edit_link'] = $editLink;
        }

        return $data;
    }
}
