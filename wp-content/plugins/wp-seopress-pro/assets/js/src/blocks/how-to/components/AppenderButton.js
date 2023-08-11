import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Inserter } from '@wordpress/block-editor';

const AppenderButton = props => (
    <Inserter
        rootClientId={props.clientId}
        renderToggle={({ onToggle, disabled }) => (
            <Button
                className="add-step-button"
                onClick={onToggle}
                disabled={disabled}
                text={__('Add step', 'wp-seopress-pro')}
                icon="insert"
            />
        )}
        isAppender
    />
);

export default AppenderButton;