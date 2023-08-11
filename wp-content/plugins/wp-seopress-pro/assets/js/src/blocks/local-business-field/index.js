import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';

registerBlockType('wpseopress/local-business-field', {
    parent: ['wpseopress/local-business'],
    title: __('Local Business', 'wp-seopress-pro'),
    description: __('Displays a single Local Business field', 'wp-seopress-pro'),
    edit,
    save: () => null,
});
