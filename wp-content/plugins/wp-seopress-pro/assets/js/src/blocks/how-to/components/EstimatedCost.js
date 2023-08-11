import { __, sprintf } from '@wordpress/i18n';
const EstimatedCost = ({ estimatedCost }) => {
    return estimatedCost ? <p className="wpseopress-how-to-estimated-cost">{sprintf(__('Estimated cost: %1$s', 'wp-seopress-pro'), estimatedCost)}</p> : '';
}
export default EstimatedCost;