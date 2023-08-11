import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';

registerBlockType('wpseopress/how-to-step', {
    title: __('How To Step', 'wp-seopress-pro'),
    description: __('Display a tutorial step.', 'wp-seopress-pro'),
    edit,
    save,
});