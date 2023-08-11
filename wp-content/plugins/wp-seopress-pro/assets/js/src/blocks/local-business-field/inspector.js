import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import FieldSelect from "./FieldSelect";
import { PanelBody, PanelRow, CheckboxControl } from "@wordpress/components";

const Inspector = ({ attributes, setAttributes, options }) => {
    const { field, inline, hideClosedDays, external } = attributes;
    return (
        <InspectorControls>
            <PanelBody title={__('Field settings', 'wp-seopress-pro')}>
                <PanelRow>
                    <FieldSelect attributes={attributes} setAttributes={setAttributes} options={options} />
                </PanelRow>
                <PanelRow>
                    {'seopress_local_business_opening_hours' === field
                        ? <CheckboxControl
                            label={__('Hide closed days', 'wp-seopress-pro')}
                            checked={hideClosedDays}
                            onChange={hideClosedDays => setAttributes({ hideClosedDays })}
                        />
                        : <CheckboxControl
                            label={__('Display the field value inline', 'wp-seopress-pro')}
                            checked={inline}
                            onChange={inline => setAttributes({ inline })}
                        />
                    }
                </PanelRow>
                <PanelRow>
                    {'seopress_local_business_map_link' === field &&
                        <CheckboxControl
                            label={__('Open link in a new tab', 'wp-seopress-pro')}
                            checked={external}
                            onChange={external => setAttributes({ external })}
                        />
                    }
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
}

export default Inspector;