import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, TextControl, CheckboxControl } from "@wordpress/components";

const Inspector = ({ attributes, updateField }) => {
    const { durationDays, durationHours, durationMinutes, estimatedCost, unorderedList } = attributes;
    return (
        <InspectorControls>
            <PanelBody title={__('Block settings', 'wp-seopress-pro')}>
                <PanelRow>
                    <CheckboxControl
                        label={__('Display as unordered list.', 'wp-seopress-pro')}
                        checked={unorderedList}
                        onChange={value => updateField('unorderedList', value)}
                    />
                </PanelRow>
                <PanelRow>
                    <TextControl
                        label={__('Estimated cost', 'wp-seopress-pro')}
                        value={estimatedCost}
                        onChange={value => updateField('estimatedCost', value)}
                    />
                </PanelRow>
                <PanelRow className="totalTime-controls">
                    <TextControl
                        type="number"
                        min="0"
                        label={__('Days', 'wp-seopress-pro')}
                        value={durationDays}
                        onChange={value => updateField('durationDays', value)}
                    />
                    <TextControl
                        type="number"
                        min="0"
                        label={__('Hours', 'wp-seopress-pro')}
                        value={durationHours}
                        onChange={value => updateField('durationHours', value)}
                    />
                    <TextControl
                        type="number"
                        min="0"
                        label={__('Minutes', 'wp-seopress-pro')}
                        value={durationMinutes}
                        onChange={value => updateField('durationMinutes', value)}
                    />
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
}

export default Inspector;