import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import edit from './edit';
import '../local-business-field';
import './style.scss';

registerBlockType('wpseopress/local-business', {
    title: __('Local Business', 'wp-seopress-pro'),
    description: __('Displays Local Business information', 'wp-seopress-pro'),
    edit,
    save: () => (
        <div {...useBlockProps.save()}>
            <InnerBlocks.Content />
        </div>
    )
});