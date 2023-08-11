import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import './editor.scss';

export default function edit() {
    const ALLOWED_BLOCKS = ['core/paragraph', 'core/heading'];
    const TEMPLATE = [
        ['core/heading', { placeholder: __('Title', 'wp-seopress-pro') }],
        ['core/paragraph', { placeholder: __('Description', 'wp-seopress-pro') }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_street_address', inline: false }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_postal_code', inline: true }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_address_locality', inline: true }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_region', inline: false }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_country', inline: false }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_phone', inline: false }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_map_link', inline: false }],
        ['wpseopress/local-business-field', { field: 'seopress_local_business_opening_hours', inline: false }],
    ];

    return (
        <div {...useBlockProps()}>
            <InnerBlocks allowedBlocks={ALLOWED_BLOCKS} template={TEMPLATE} />
        </div>
    );
}
