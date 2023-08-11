import { __ } from '@wordpress/i18n';
import { SelectControl } from "@wordpress/components";

const FieldSelect = ({ attributes, setAttributes, options }) => {
    const { field } = attributes;
    return <SelectControl
        label={__('Select the field to display', 'wp-seopress-pro')}
        value={field}
        options={options}
        onChange={field => setAttributes({ field, inline: false })}
    />
}

export default FieldSelect;