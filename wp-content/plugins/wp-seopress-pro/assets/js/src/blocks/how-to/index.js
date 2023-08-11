import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';

registerBlockType('wpseopress/how-to', {
    title: __('How to', 'wp-seopress-pro'),
    description: __('Display a tutorial block.', 'wp-seopress-pro'),
    keywords: [__('How to', 'wp-seopress-pro'), __('Tutorial', 'wp-seopress-pro')],
    edit,
    save,
});
