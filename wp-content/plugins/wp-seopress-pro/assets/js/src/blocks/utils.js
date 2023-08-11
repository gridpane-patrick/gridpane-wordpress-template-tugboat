import { _n, sprintf } from '@wordpress/i18n';

export const stripHtml = html => {
    let tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

export const formatTotalTime = (days = 0, hours = 0, minutes = 0, context = 'display') => {

    let formatted = '';
    let formattedDays = '';
    let formattedHours = '';
    let formattedMinutes = '';

    days = parseInt(days) ? parseInt(days) : 0;
    hours = parseInt(hours) ? parseInt(hours) : 0;
    minutes = parseInt(minutes) ? parseInt(minutes) : 0;

    switch (context) {
        case 'schema':
            const durationIndicator = days > 0 || hours > 0 || minutes > 0 ? 'P' : '';
            const timeIndicator = hours > 0 || minutes > 0 ? 'T' : '';
            formattedDays = days ? `${days}D` : '';
            formattedHours = hours ? `${hours}H` : '';
            formattedMinutes = minutes ? `${minutes}M` : '';
            formatted = `${durationIndicator}${formattedDays}${timeIndicator}${formattedHours}${formattedMinutes}`;
            break;
        case 'display':
        default:
            formattedDays = days > 0 ? sprintf(_n('%1$d day', '%1$s days', days, 'wp-seopress-pro'), days) : '';
            formattedHours = hours > 0 ? sprintf(_n('%1$d hour', '%1$s hours', hours, 'wp-seopress-pro'), hours) : '';
            formattedMinutes = minutes > 0 ? sprintf(_n('%1$d minute', '%1$s minutes', minutes, 'wp-seopress-pro'), minutes) : '';
            const arr = [formattedDays, formattedHours, formattedMinutes].filter(el => '' !== el);
            formatted = arr.length ? arr.join(', ') : '';
            break;
    }
    return formatted;
}