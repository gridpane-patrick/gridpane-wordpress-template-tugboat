import { __ } from '@wordpress/i18n';
import { cleanForSlug } from '@wordpress/url';
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { title, description } = attributes;
    return (
        <li {...useBlockProps.save()}>
            {title && <RichText.Content tagName="strong" id={cleanForSlug(title)} value={title} className="wpseopress-how-to-step-title" />}
            <RichText.Content tagName="div" value={description} className="wpseopress-how-to-step-description" />
        </li>
    );
}