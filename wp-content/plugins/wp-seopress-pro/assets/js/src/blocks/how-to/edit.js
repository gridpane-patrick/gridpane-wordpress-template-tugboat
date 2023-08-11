import './editor.scss';
import '../how-to-step';
import { __ } from '@wordpress/i18n';
import { select, subscribe } from '@wordpress/data';
import { stripHtml, formatTotalTime } from '../utils';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/block-editor';
import Inspector from './inspector.js';
import AppenderButton from './components/AppenderButton';
import EstimatedCost from './components/EstimatedCost';
import TotalTime from './components/TotalTime';

export default function edit({ attributes, setAttributes, clientId }) {

    const { description, schema, durationDays, durationHours, durationMinutes, unorderedList, estimatedCost } = attributes;

    const updateField = (field, value = '') => {
        const newSchema = {
            ...schema,
            '@context': 'https://schema.org',
            '@type': 'HowTo',
            'name': select('core/editor').getEditedPostAttribute('title'),
            //'step': getStepSchema()
        };

        const imageId = select('core/editor').getEditedPostAttribute('featured_media');
        if (imageId) {
            const imageObj = select('core').getMedia(imageId);
            const imageUrl = imageObj && imageObj.source_url ? imageObj.source_url : '';
            delete newSchema.image;
            if (imageUrl) newSchema.image = imageUrl;
        }

        switch (field) {
            case 'description':
                newSchema.description = stripHtml(value);
                if ('' === newSchema.description) delete newSchema.description
                break;
            case 'durationDays':
                newSchema.totalTime = formatTotalTime(value, durationHours, durationMinutes, 'schema');
                if (!newSchema.totalTime) delete newSchema.totalTime;
                break;
            case 'durationHours':
                newSchema.totalTime = formatTotalTime(durationDays, value, durationMinutes, 'schema');
                if (!newSchema.totalTime) delete newSchema.totalTime;
                break;
            case 'durationMinutes':
                newSchema.totalTime = formatTotalTime(durationDays, durationHours, value, 'schema');
                if (!newSchema.totalTime) delete newSchema.totalTime;
                break;
            case 'estimatedCost':
                newSchema.estimatedCost = value;
                if (!newSchema.estimatedCost) delete newSchema.estimatedCost;
                break;
        }
        setAttributes({ schema: newSchema, [field]: value });
    }

    const getStepSchema = () => {
        const children = select('core/block-editor').getBlock(clientId).innerBlocks || [];
        let step = [];
        if (children.length) {
            step = children.reduce((acc, child) => {
                if (undefined !== child.attributes.schema) acc.push(child.attributes.schema);
                return acc;
            }, []);
        }
        return step;
    }

    const updateStepSchema = () => {
        unsubscribe();
        const newSchema = {
            ...schema,
            '@context': 'https://schema.org',
            '@type': 'HowTo',
            'name': select('core/editor').getEditedPostAttribute('title'),
            'step': getStepSchema(),
        };
        if (JSON.stringify(schema.step) !== JSON.stringify(newSchema.step)) {
            unsubscribe();
            setAttributes({ schema: newSchema })
        }
    }

    const unsubscribe = subscribe(e => {
        const isSavingPost = select('core/editor').isSavingPost();
        const isAutosavingPost = select('core/editor').isAutosavingPost();
        if (isAutosavingPost || isSavingPost) return;

        const parentSelected = select('core/block-editor').isBlockSelected(clientId);
        const childSelected = select('core/block-editor').hasSelectedInnerBlock(clientId);
        if (parentSelected || childSelected) {
            unsubscribe();
            updateStepSchema();
        }
    });

    const Tag = unorderedList ? `ul` : `ol`;
    const Appender = () => <AppenderButton clientId={clientId} />;
    return (
        <div {...useBlockProps()}>
            <Inspector attributes={attributes} updateField={updateField} />
            <RichText
                tagName="div"
                value={description}
                multiline={true}
                className="wpseopress-how-to-description"
                onChange={description => updateField('description', description)}
                placeholder={__('Type in a tutorial description or introduction', 'wp-seopress-pro')}
            />
            <EstimatedCost estimatedCost={estimatedCost} />
            <TotalTime durationDays={durationDays} durationHours={durationHours} durationMinutes={durationMinutes} />
            <Tag className="wpseopress-how-to-steps">
                <InnerBlocks allowedBlocks={['wpseopress/how-to-step']} renderAppender={Appender} />
            </Tag>
        </div>
    );
}