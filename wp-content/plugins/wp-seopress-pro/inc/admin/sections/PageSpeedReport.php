<?php
defined('ABSPATH') or die('Please don&rsquo;t call the plugin directly. Thanks :)');
$docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';
?>
<div id="seopress-page-speed-results" class="metabox-holder">
    <?php
        if (get_transient('seopress_results_page_speed') == true && get_transient('seopress_results_page_speed_desktop') == true) {
            //Init
            $json = [];

            $json = json_decode(get_transient('seopress_results_page_speed'), true);
            $json_desktop = json_decode(get_transient('seopress_results_page_speed_desktop'), true);

            if ( ! empty($json) && array_key_first($json) !== 'error' && ! empty($json_desktop) && array_key_first($json_desktop) !== 'error') {
                $perf_score = seopress_pro_get_ps_score($json, true);
                $perf_score_desktop = seopress_pro_get_ps_score($json_desktop);
                $core_web_vitals_score = seopress_pro_get_cwv_score($json);

                $cwv_svg = '<svg enable-background="new 0 0 24 24" focusable="false" height="15" viewBox="0 0 24 24" width="15" style="fill:#06f;vertical-align:middle"><g><g><path d="M0,0h24v24H0V0z" fill="none"></path></g></g><g><path d="M17,3H7C5.9,3,5,3.9,5,5v16l7-3l7,3V5C19,3.9,18.1,3,17,3z"></path></g></svg>';

                $loading_experience_scores = [
                    __('Performance', 'wp-seopress-pro') => [
                        'score' => $json['lighthouseResult']['categories']['seo']['score'] ? $perf_score : 0,
                        'score_desktop' => $json['lighthouseResult']['categories']['performance']['score'] ? $perf_score_desktop : 0,
                        'unit' => '<p class="wrap-scale">' . __('<span><span class="score red"></span>0-49</span><span><span class="score yellow"></span>50-89</span><span><span class="score green"></span>90-100</span>', 'wp-seopress-pro') . '</p>'
                    ],
                    __('Screenshot', 'wp-seopress-pro') => [
                        'score' => $json['lighthouseResult']['audits']['final-screenshot']['details']['data'] ? '<div class="your-screenshot"><img height="300" src="' . $json['lighthouseResult']['audits']['final-screenshot']['details']['data'] . '"/></div>' : ''
                    ],
                ];

                if (isset($json['loadingExperience'])) {
                    if (isset($json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS'])) {
                        $loading_experience_scores['First Contentful Paint (FCP)'] = [
                            'score' => $json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['percentile'] ? round((($json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['percentile']) / 1000), 2) : __('N/A', 'wp-seopress-pro'),
                            'unit' => 's',
                            'distribution' => $json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['distributions'] ? $json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['distributions'] : '',
                            'category' => $json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['category'] ? $json['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['category'] : '',
                            'web_vitals' => false
                        ];
                    }
                    if (isset($json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS'])) {
                        $loading_experience_scores['First Input Delay (FID)'] = [
                            'score' => $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['percentile'] ? $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['percentile'] : __('N/A', 'wp-seopress-pro'),
                            'unit' => 'ms',
                            'distribution' => $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['distributions'] ? $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['distributions'] : '',
                            'category' => $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category'] ? $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category'] : '',
                            'web_vitals' => true
                        ];
                    }
                    if (isset($json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS'])) {
                        $loading_experience_scores['Largest Contentful Paint (LCP)'] = [
                            'score' => $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['percentile'] ? round((($json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['percentile']) / 1000), 2) : __('N/A', 'wp-seopress-pro'),
                            'unit' => 's',
                            'distribution' => $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['distributions'] ? $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['distributions'] : '',
                            'category' => $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'] ? $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'] : '',
                            'web_vitals' => true
                        ];
                    }
                    if (isset($json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE'])) {
                        $loading_experience_scores['Cumulative Layout Shift (CLS)'] = [
                            'score' => $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] ? $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] : __('N/A', 'wp-seopress-pro'),
                            'distribution' => $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['distributions'] ? $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['distributions'] : '',
                            'category' => $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'] ? $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'] : '',
                            'web_vitals' => true
                        ];
                    }
                } ?>

    <div class="wrap-seopress-score">
        <div class="seopress-cwv">
            <?php if ($core_web_vitals_score === true) { ?>
            <img src="<?php echo SEOPRESS_PRO_ASSETS_DIR; ?>/img/cwv-pass.svg"
                alt='' width='96' height='96' />
            <?php } else { ?>
            <img src="<?php echo SEOPRESS_PRO_ASSETS_DIR; ?>/img/cwv-fail.svg"
                alt='' width='96' height='96' />
            <?php } ?>
            <div>
                <h3>
                    <?php _e('Core Web Vitals Assessment: ', 'wp-seopress-pro'); ?>

                    <?php if ($core_web_vitals_score === true) { ?>
                    <span class="green"><?php _e('Passed', 'wp-seopress-pro'); ?></span>
                    <?php } elseif ($core_web_vitals_score === null) { ?>
                    <span class="red"><?php _e('No data found', 'wp-seopress-pro'); ?></span>
                    <?php } else { ?>
                    <span class="red"><?php _e('Failed', 'wp-seopress-pro'); ?></span>
                    <?php } ?>
                </h3>
                <p><?php printf(__('Computed from the %s Core Web Vitals metrics over the latest 28-day collection period.', 'wp-seopress-pro'), $cwv_svg); ?>
                </p>
                <p><?php _e('The Core Web Vitals metrics are FID, LCP, and CLS. For aggregations with sufficient data in all three metrics, the aggregation passes the Core Web Vitals assessment if the 75th percentiles of all three metrics are Good. Otherwise, the aggregation does not pass the assessment. If the aggregation has insufficient data for FID, then it will pass the assessment if both the 75th percentiles of LCP and CLS are Good.', 'wp-seopress-pro'); ?>
                </p>
                <p>
                    <a href="<?php echo $docs['page_speed']['cwv']; ?>"
                        target="_blank" class="seopress-help">
                        <?php _e('Learn more about Core Web Vitals', 'wp-seopress-pro'); ?>
                    </a>
                    <span class="seopress-help dashicons dashicons-external"></span>
                     - 
                    <a href="https://pagespeed.web.dev/report?url=<?php echo get_home_url(); ?>"
                        target="_blank">
                        <?php _e('Full report on Google Page Speed website', 'wp-seopress-pro'); ?>
                    </a>
                    <span class="dashicons dashicons-external"></span>
                </p>
            </div>
        </div>

        <?php if ( ! empty($loading_experience_scores)) { ?>
        <div class="seopress-summary-items">
            <?php foreach ($loading_experience_scores as $key => $value) { ?>
            <div class="seopress-summary-item">
                <div class="seopress-summary-item-label">
                    <?php if ( ! empty($value['category'])) {
                    switch ($value['category']) {
                                                case 'SLOW':
                                                    echo '<span class="score red"></span>';
                                                    break;
                                                case 'AVERAGE':
                                                    echo '<span class="score yellow"></span>';
                                                    break;
                                                case 'FAST':
                                                    echo '<span class="score green"></span>';
                                                    break;
                                            }
                }?>
                    <?php echo $key; ?>

                    <?php if ( ! empty($value['web_vitals']) && $value['web_vitals'] === true) {
                    echo $cwv_svg;
                } ?>
                </div>
                <div class="seopress-summary-item-data">
                    <?php if ( ! empty($value['score'])) {
                    echo $value['score'];
                }?>
                    <?php if ( ! empty($value['score_desktop'])) {
                    echo $value['score_desktop'];
                }?>

                    <?php if ( ! empty($value['unit'])) {
                    echo $value['unit'];
                }?>
                    <?php if (array_key_exists('web_vitals', $value)) { ?>
                    <small>(75th Percentile)</small>
                    <?php } ?>
                    <?php if ( ! empty($value['distribution'])) { ?>
                    <div class="wrap-dist">
                        <?php foreach ($value['distribution'] as $value) {
                    $proportion = round($value['proportion'] * 100, 2);
                    echo '<div class="ps-fast" style="flex-grow:' . $proportion . '">' . $proportion . '%</div>';
                } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="lab-data">
            <?php
                $screenshot_thumbnails = $json['lighthouseResult']['audits']['screenshot-thumbnails']['details']['items'];

                if ( ! empty($screenshot_thumbnails)) {
                    echo '<ul class="screens">';
                    foreach ($screenshot_thumbnails as $value) {
                        echo '<li>';
                        echo '<img src="' . $value['data'] . '"/>';
                        echo '<span>' . round($value['timing'] / 1000, 2) . __(' s', 'wp-seopress-pro') . '</span>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } ?>
        </div>

        <?php if ( ! empty($json['lighthouseResult']['audits'])) {
                    // Init
                    $audits = [
                        'opportunities' => [],
                        'diagnostics' => [],
                        'passed' => [],
                    ];
                    foreach ($json['lighthouseResult']['audits'] as $key => $audit) {
                        if ($audit['scoreDisplayMode'] === 'informative' || $audit['scoreDisplayMode'] === 'manual') {
                            continue;
                        }

                        if ($audit['score'] === 0) {
                            continue;
                        }

                        $item = [
                            'title' => isset($audit['title']) ? esc_html($audit['title']) : '',
                            'description' => isset($audit['description']) ? esc_html($audit['description']) : '',
                            'score' => isset($audit['score']) ? $audit['score'] : '',
                            'displayValue' => isset($audit['displayValue']) ? $audit['displayValue'] : '',
                        ];

                        if ($audit['score'] <= 0.89 && $audit['scoreDisplayMode'] !== 'notApplicable') {
                            // Opportunities
                            if (isset($audit['details']['type']) && $audit['details']['type'] == 'opportunity') {
                                $audits['opportunities'][] = $item;
                            }

                            // Diagnostics
                            $audits['diagnostics'][] = $item;
                        }

                        // Passed audits
                        if (($audit['score'] >= 0.89 || $audit['score'] === null) && (isset($audit['details']['type']) && $audit['details']['type'] !== 'opportunity')) {
                            $audits['passed'][] = $item;
                        }
                    }
                } ?>
        <?php if ( ! empty($audits)) { ?>
        <div class="ps-audits">
            <?php foreach ($audits as $key => $cats) {
                    $count = ! empty(count($cats)) ? ' (' . count($cats) . ')' : '';
                    switch ($key) {
                        case 'opportunities':
                            $title = __('Opportunities', 'wp-seopress-pro');
                            break;
                        case 'diagnostics':
                            $title = __('Diagnostics', 'wp-seopress-pro');
                            break;
                        case 'passed':
                            $title = __('Passed audits', 'wp-seopress-pro');
                            break;
                    }

                    echo '<h3>' . $title . $count . '</h3>';
                    foreach ($cats as $_key => $_value) {
                        if (isset($_value['score'])) {
                            if ($_value['score'] >= 0.9) {
                                $score = 'green';
                            } elseif ($_value['score'] >= 0.5 && $_value['score'] <= 0.89) {
                                $score = 'yellow';
                            } elseif ($_value['score'] === 0 && $_value['score'] <= 0.49) {
                                $score = 'red';
                            }
                        } else {
                            $score = 'null';
                        } ?>
            <div class="ps-audit">
                <?php if ( ! empty($_value['title'])) { ?>
                <h4 class="ps-audit-title">
                    <span class="score <?php echo $score; ?>"></span>
                    <?php echo $_value['title']; ?>
                    <?php if ($_value['displayValue']) { ?>
                    <small class="<?php echo $score; ?>"><?php echo $_value['displayValue']; ?></small>
                    <?php } ?>
                </h4>

                <?php if ( ! empty($_value['description'])) { ?>
                <div class="ps-audit-desc">
                    <p><?php echo $_value['description']; ?>
                    </p>
                    <?php preg_match('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', trim($_value['description'], ').'), $matches);
            if ( ! empty($matches[0])) {
                echo '<p class="learn-more"><a class="seopress-help" target="_blank" rel="noopener noreferrer nofollow" href="' . $matches[0] . '">' . __('Learn more', 'wp-seopress-pro') . '</a><span class="seopress-help dashicons dashicons-external"></span></p>';
            } ?>
                </div>
                <?php }
                    } ?>
            </div>
            <?php
                    }
                } ?>
        </div>
        <?php } ?>

        <div class="wrap-info">
            <?php if ($json['lighthouseResult']['fetchTime']) {
                    $fetchTime = $json['lighthouseResult']['fetchTime']; ?>
            <p>
                <strong><?php _e('Captured at ', 'wp-seopress-pro'); ?></strong>
                <?php echo date_i18n(get_option('date_format'), strtotime($fetchTime)); ?>,
                <?php echo date('H:i', strtotime($fetchTime)); ?>
            </p>
            <?php
                } ?>
        </div>
    </div>
    <?php
            } ?>
    <?php
        } ?>
</div>
