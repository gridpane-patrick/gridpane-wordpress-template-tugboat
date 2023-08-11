import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { stripHtml } from '../utils';
import { cleanForSlug } from '@wordpress/url';
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function edit({ attributes, setAttributes }) {
    let { title, description, schema } = attributes;

    const updateStep = (field, value) => {
        const newSchema = { ...schema, '@type': 'HowToStep' };
        switch (field) {
            case 'title':
                newSchema.name = value;
                newSchema.url = select('core/editor').getPermalink() + '#' + cleanForSlug(value);
                break;
            case 'description':
                newSchema.text = stripHtml(value);
                const found = value.match(/src="(.*?)"/);
                if (found && found[1]) newSchema.image = found[1];
                break;
        }
        setAttributes({ schema: newSchema, [field]: value });
    }


    return (
        <li {...useBlockProps()}>
            <RichText
                tagName="strong"
                value={title}
                className='wpseopress-how-to-step-title'
                onChange={title => updateStep('title', title)}
                placeholder={__('Type a title', 'wp-seopress-pro')}
            />
            <RichText
                tagName="div"
                value={description}
                multiline={true}
                className='wpseopress-how-to-step-description'
                onChange={description => updateStep('description', description)}
                placeholder={__('Type a description', 'wp-seopress-pro')}
            />
        </li>
    );
}
