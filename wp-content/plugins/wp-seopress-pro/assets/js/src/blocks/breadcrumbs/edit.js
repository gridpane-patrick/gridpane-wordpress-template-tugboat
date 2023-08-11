import { __ } from '@wordpress/i18n';
import { RawHTML } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { select } from '@wordpress/data';

export default function edit({ attributes }) {
    const { inlineStyles, homeOption } = attributes;
    const title = select("core/editor").getEditedPostAttribute('title') || __('Example title', 'wp-seopress-pro');
    const crumbs = [homeOption, title];

    return (
        <>
            <Notice status="warning" isDismissible={false}>{__('This is an example of breadcrumbs. View your site as a simple visitor to get the final rendering.', 'wp-seopress-pro')}</Notice>
            <div {...useBlockProps()}>
                <RawHTML>{inlineStyles}</RawHTML>
                {crumbs &&
                    <ol className="wpseopress-breadcrumb breadcrumb">
                        {crumbs.map((crumb, index) => (
                            <li key={index} className="wpseopress-breadcrumb-item breadcrumb-item" dangerouslySetInnerHTML={{ __html: crumb }} />
                        ))}
                    </ol>
                }
            </div>
        </>
    )
}
