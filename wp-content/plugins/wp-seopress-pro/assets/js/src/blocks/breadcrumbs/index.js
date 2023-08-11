import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';

registerBlockType('wpseopress/breadcrumbs', {
    title: __('Breadcrumbs', 'wp-seopress-pro'),
    description: __('Display an HTML breadcrumbs.', 'wp-seopress-pro'),
    keywords: [__('ariane', 'wp-seopress-pro'), __('breadcrumbs', 'wp-seopress-pro'), __('crumbs', 'wp-seopress-pro'), __('navigation', 'wp-seopress-pro')],
    edit,
    save: () => null
});
