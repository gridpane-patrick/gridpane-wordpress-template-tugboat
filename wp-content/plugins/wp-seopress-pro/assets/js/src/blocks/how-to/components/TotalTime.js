import { formatTotalTime } from '../../utils';
import { sprintf } from '@wordpress/i18n';

const TotalTime = ({ durationDays, durationHours, durationMinutes }) => {
    const formatted = formatTotalTime(durationDays, durationHours, durationMinutes, 'display');
    return formatted ? <p className="wpseopress-how-to-total-time">{sprintf('Total time: %1$s', formatted)}</p> : '';
}

export default TotalTime;
