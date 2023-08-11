import { __ } from '@wordpress/i18n';
import TotalTime from './components/TotalTime';
import EstimatedCost from './components/EstimatedCost';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { description, schema, unorderedList, durationDays, durationHours, durationMinutes, estimatedCost } = attributes;
    const Tag = unorderedList ? `ul` : `ol`;
    return (
        <div {...useBlockProps.save()}>
            {description && <RichText.Content tagName="div" value={description} className="wpseopress-how-to-description" />}
            <EstimatedCost estimatedCost={estimatedCost} />
            <TotalTime durationDays={durationDays} durationHours={durationHours} durationMinutes={durationMinutes} />
            <Tag className="wpseopress-how-to-steps">
                <InnerBlocks.Content />
            </Tag>
            <script type="application/ld+json">{JSON.stringify(schema)}</script>
        </div>
    );
}