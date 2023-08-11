import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import FieldSelect from "./FieldSelect";
import Inspector from './inspector.js';
import ServerSideRender from '@wordpress/server-side-render';

export default function edit({ attributes, setAttributes }) {
    const { field, inline } = attributes;
    const options = [
        { label: __('Street Address', 'wp-seopress-pro'), className: 'sp-street', value: 'seopress_local_business_street_address' },
        { label: __('Zipcode', 'wp-seopress-pro'), className: 'sp-code', value: 'seopress_local_business_postal_code' },
        { label: __('City', 'wp-seopress-pro'), className: 'sp-city', value: 'seopress_local_business_address_locality' },
        { label: __('State', 'wp-seopress-pro'), className: 'sp-state', value: 'seopress_local_business_region' },
        { label: __('Country', 'wp-seopress-pro'), className: 'sp-country', value: 'seopress_local_business_country' },
        { label: __('Phone', 'wp-seopress-pro'), className: 'sp-phone', value: 'seopress_local_business_phone' },
        { label: __('Map link', 'wp-seopress-pro'), className: 'sp-map-link', value: 'seopress_local_business_map_link' },
        { label: __('Opening hours', 'wp-seopress-pro'), className: 'sp-opening-hours', value: 'seopress_local_business_opening_hours' },
    ];

    const Tag = inline ? `span` : `div`;
    return (
        <Tag {...useBlockProps({ className: field })}>
            <Inspector attributes={attributes} setAttributes={setAttributes} options={options} />
            {!field
                ? (<FieldSelect attributes={attributes} setAttributes={setAttributes} options={options} />)
                : <ServerSideRender
                    block="wpseopress/local-business-field"
                    attributes={attributes}
                />
            }
        </Tag>
    )
}
