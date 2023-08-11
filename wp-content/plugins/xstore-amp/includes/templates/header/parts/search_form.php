<?php
/**
 * Header search form template
 *
 * @package    search_form.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings, $xstore_amp_vars;

$xstore_amp = XStore_AMP::get_instance();

$action_url =  admin_url('admin-ajax.php?action=xstore_amp_search');
$action_url = preg_replace('#^https?:#', '', $action_url);

$search_page_action = $xstore_amp_vars['is_woocommerce'] ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );

?>
<div class="searchWrapper pos-fixed invisible" [class]="!search.show ? 'searchWrapper invisible pos-fixed' : 'searchWrapper pos-fixed'">
    <div class="amp-container">
        <form id="searchForm" action-xhr="<?php echo esc_url($action_url); ?>" method="POST"
              class="searchForm flex align-items-center pos-relative"
              on="submit: AMP.setState({ 'search': {'clicked_once' : 'yes', 'processing': (search.query != '' ? 'yes' : false)} });
              submit-success: AMP.setState({ 'search': {'processing': false, 'results': event.response.suggestions, 'resultsTabs': event.response.tabs }}),
              searchTabs.changeToLayoutContainer(),
              searchTabs.refresh,
              searchResults.changeToLayoutContainer(),
              searchResults.refresh">
            <span role="button" tabindex="-1" on="tap: AMP.setState({'search': {'show' : false}});">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="1.3em" height="1.3em" fill="currentColor" style="vertical-align: middle" viewBox="0 0 24 24">
                    <path d="M17.976 22.8l-10.44-10.8 10.464-10.848c0.24-0.288 0.24-0.72-0.024-0.96-0.24-0.24-0.72-0.264-0.984 0l-10.92 11.328c-0.264 0.264-0.264 0.672 0 0.984l10.92 11.28c0.144 0.144 0.312 0.216 0.504 0.216 0.168 0 0.336-0.072 0.456-0.192 0.144-0.12 0.216-0.288 0.24-0.48 0-0.216-0.072-0.384-0.216-0.528z"></path>
                </svg>
            </span>
            <input type="text" name="s" [value]="search.query"
                   placeholder="<?php echo esc_attr__('Search your products', 'xstore-amp'); ?>"
                   class="input-bg"
                   on="input-debounced:
                   AMP.setState({'search' : {
                        'query' : event.value,
                        'results': event.value != '' ? search.results : '',
                        'resultsTabs': event.value != '' ? search.resultsTabs : ''
                   }
                   }),
                   searchForm.submit">
            <span class="buttons-wrapper flex align-items-center">
                <span role="button" tabindex="-1" class="et-icon et-delete" [class]="search.processing ? 'et-icon is-loading' : 'et-icon et-delete'" hidden [hidden]="(!search.processing && !search.results) || !search.clicked_once" on="tap: searchForm.clear, AMP.setState({'search': {'query': '', 'results' : '', 'resultsTabs': ''}})"></span>
                <a href="<?php echo esc_url($search_page_action); ?>" role="button" [href]="search.action + '&s='+search.query + '&et_search=true'" class="inline-flex align-items-center text-center">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.784 22.8l-6.168-6.144c1.584-1.848 2.448-4.176 2.448-6.576 0-5.52-4.488-10.032-10.032-10.032-5.52 0-10.008 4.488-10.008 10.008s4.488 10.032 10.032 10.032c2.424 0 4.728-0.864 6.576-2.472l6.168 6.144c0.144 0.144 0.312 0.216 0.48 0.216s0.336-0.072 0.456-0.192c0.144-0.12 0.216-0.288 0.24-0.48 0-0.192-0.072-0.384-0.192-0.504zM18.696 10.080c0 4.752-3.888 8.64-8.664 8.64-4.752 0-8.64-3.888-8.64-8.664 0-4.752 3.888-8.64 8.664-8.64s8.64 3.888 8.64 8.664z"></path>
                    </svg>
                </a>
            </span>
        </form>
        <br/>
        <hr>
        <?php if ( !isset($xstore_amp_settings['general']['search_tags']) || !empty($xstore_amp_settings['general']['search_tags'])) : ?>
        <div class="search-tags-wrapper" [class]="(search.processing == 'yes' || (search.results.length > 0 && search.clicked_once)) ? 'hidden' : 'search-tags-wrapper'">
            <br/>
            <?php if ( !isset($xstore_amp_settings['general']['search_tags_title'])) {
	            $xstore_amp_settings['general']['search_tags_title'] = esc_html__('Trending searches', 'xstore-amp');
            }
            if ( !empty($xstore_amp_settings['general']['search_tags_title'])) {
                echo '<h4 class="widget-title">'.$xstore_amp_settings['general']['search_tags_title'].'</h4>';
            }
            $tags = 'Shirt, Shoes, Cap, Coat, Skirt';
            if ( isset($xstore_amp_settings['general']['search_tags']) && !empty($xstore_amp_settings['general']['search_tags'] ) ) {
                $tags = $xstore_amp_settings['general']['search_tags'];
            }
            ?>
            <div class="search-tags tagcloud">
                <?php
                    foreach ( explode(',',$tags) as $tag ) {
                        ?>
                        <a role="button" tabindex="-1" on="tap: AMP.setState({'search': {'query': '<?php echo esc_attr($tag); ?>'}}), searchForm.submit"><?php echo esc_html($tag); ?></a>
                        <?php
                    }
                ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="search-categories" [class]="(search.processing == 'yes' || (search.results.length > 0 && search.clicked_once)) ? 'hidden' : 'search-categories'">
            <br/>
            <br/>
            <?php
                $xstore_amp->get_products_categories(array(),
                    array(
                        'title_tag' => 'h4',
                        'data-slides' => 4.5
                    )
                );
            ?>
        </div>
        <amp-list id="searchTabs"
                layout="fixed-height"
                  height="30"
                  src="amp-state:search.resultsTabs"
                  binding="no"
                  class="search-tabs"
                  [class]="'search-tabs ' + search.activeTab"
                  hidden
                  [hidden]="!search.resultsTabs || !search.clicked_once"
                  items=".">
            <template type="amp-mustache">
	            <?php
	            $search_options = array('products', 'posts');
	            if ( isset($xstore_amp_settings['general']['search_results']) && !empty($xstore_amp_settings['general']['search_results']) ) {
		            $search_options = explode(',', $xstore_amp_settings['general']['search_results']);
		            foreach ( $search_options as $element_key => $element_name ) {
			            if ( !isset($xstore_amp_settings['general'][$element_name.'_visibility']) || !$xstore_amp_settings['general'][$element_name.'_visibility'] ) {
				            unset($search_options[$element_key]);
			            }
		            }
	            }
	            foreach ($search_options as $option) { ?>
                    {{#tab_<?php echo $option; ?>}}
                    <span class="text-center flex align-items-center" role="button" tabindex="-1" on="tap:AMP.setState({'search': {'activeTab': '<?php echo $option; ?>'}})"
                          data-tab="<?php echo $option; ?>">
                                <?php
                                switch ($option){
	                                case 'posts':
		                                echo esc_html__('Posts', 'xstore-amp');
		                                break;
	                                case 'products':
		                                echo esc_html__('Products', 'xstore-amp');
		                                break;
	                                case 'pages':
		                                echo esc_html__('Pages', 'xstore-amp');
		                                break;
	                                case 'portfolio':
		                                echo esc_html__('Portfolio', 'xstore-amp');
		                                break;
	                                default:
		                                break;
                                }
                                ?>
                            </span>
                    {{/tab_<?php echo $option; ?>}}
		            <?php
	            }
	            ?>
            </template>
        </amp-list>
        <amp-list id="searchResults"
                layout="fixed-height"
                height="70"
                src="amp-state:search.results"
                binding="no"
                [class]="search.resultsTabs.length ? 'show-'+search.activeTab : 'show-all'"
                hidden
                [hidden]="!search.results || !search.clicked_once"
                items=".">
        <template type="amp-mustache">
            {{#no_results}}
                <div class="search-no-results text-center">
                    <h3>{{no_results_text}}</h3>
                    <p>{{{no_results_description}}}</p>
                </div>
            {{/no_results}}
            {{^no_results}}
            <div class="result-item flex" data-type="{{type}}">
                <a href="{{url}}" class="flex">
                    {{#img}}
                    <span class="item-img">
                        <amp-img width="270" height="160" src="{{img}}" class="wp-image" layout="responsive" alt="">
                        </amp-img>
                    </span>
                    {{/img}}
                    <div class="item-info">
                        <span class="item-title">
                            {{value}}
                        </span>
                        {{#price}}
                            <span class="price">{{{price}}}</span>
                        {{/price}}
                        {{#type_product}}
                            {{#in_stock}}
                                <span class="stock in-stock"><?php echo esc_html__('In stock', 'xstore-amp'); ?></span>
                            {{/in_stock}}
                            {{^in_stock}}
                                <span class="stock out-of-stock"><?php echo esc_html__('Out of stock', 'xstore-amp'); ?></span>
                            {{/in_stock}}
                        {{/type_product}}
                        {{#date}}
                            <span class="post-date">{{{date}}}</span>
                        {{/date}}
                    </div>
                </a>
            </div>
            {{/no_results}}
        </template>
    </amp-list>
    </div>
</div>