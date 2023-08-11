<?php
/**
 * Local Business Block Field display callback
 *
 * @param   array     $attributes  Block attributes
 * @param   string    $content     Inner block content
 * @param   WP_Block  $block       Actual block
 * @return  string    $html
 */
function seopress_pro_local_business_field_block( $attributes, $content, $block ){
    $field    = ! empty( $attributes['field'] ) ? $attributes['field'] : '';
    $tag      = (bool) $attributes['inline'] ? 'span' : 'p';
    $external = (bool) $attributes['external'];
    $attr     = get_block_wrapper_attributes( array( 'class' => sanitize_title( $field ) ) );
    $value    = '';
    switch ( $field ) {
        case 'seopress_local_business_street_address':
            $value = seopress_local_business_street_address_option();
            break;
        case 'seopress_local_business_postal_code':
            $value = seopress_local_business_postal_code_option();
            break;
        case 'seopress_local_business_address_locality':
            $value = seopress_local_business_address_locality_option();
            break;
        case 'seopress_local_business_region':
            $value = seopress_local_business_address_region_option();
            break;
        case 'seopress_local_business_country':
            $value = seopress_local_business_address_country_option();
            break;
        case 'seopress_local_business_phone':
            $value = seopress_local_business_phone_option();
            break;
        case 'seopress_local_business_map_link':
            $value = seopress_pro_get_local_business_map_link( $external );
            break;
        case 'seopress_local_business_opening_hours':
            $hide_closed_days = ! empty( $attributes['hideClosedDays'] ) ? (bool) $attributes['hideClosedDays'] : false;
            $value = seopress_pro_get_local_business_opening_hours( $hide_closed_days );
            $tag   = 'div';
            break;
    }
    $html = ! empty( $value ) ? sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag, $attr, wp_kses_post( $value ) ) : sprintf('<p style="color:red">%s</p>', __( 'This value is missing from your Local Business settings', 'wp-seopress-pro' ));
    return apply_filters( 'seopress_local_business_field_block_html', $html, $attributes, $content, $block );
}

/**
 * Returns a Google Map link to the Local Business
 *
 * @return  string  $html  Link html
 */
function seopress_pro_get_local_business_map_link( $external = false){
    $lat      = seopress_local_business_lat_option();
    $lon      = seopress_local_business_lon_option();
    $place_id = seopress_local_business_place_id_option();
    $map_url  = 'https://www.google.com/maps/search/?api=1';
    $html     = '';
    $display  = false;
    if( ! empty( $place_id ) ){
        $map_url = add_query_arg( 'query_place_id', urlencode( $place_id ), $map_url );
        $display = true;
    }
    if( $lat && $lon ){
        $map_url = add_query_arg( 'query', urlencode( $lat . ',' . $lon ), $map_url );
        $display = true;
    }
    $title = $external ? __( 'View this local business on Google Maps (new window)', 'wp-seopress-pro' ) : __( 'View this local business on Google Maps', 'wp-seopress-pro' );
    if( $display ){
        $html = sprintf( '<a href="%1$s" title="%2$s"%3$s>%4$s</a>',
            esc_url( $map_url ),
            esc_attr( $title ),
            (bool) $external ? ' target="_blank"' : '',
            esc_html__( 'View on Google Maps', 'wp-seopress-pro')
        );
    }
    return apply_filters( 'seopress_pro_local_business_map_link', $html );
}

/**
 * Returns the Local Business opening hours table
 *
 * @return  string  $html  HTML Table
 */
function seopress_pro_get_local_business_opening_hours( $hide_closed_days = false, $attr = 'class="sp-opening-hours-table"' ){
    $opening_hours = seopress_local_business_opening_hours_option();
    $days          = seopress_pro_get_weekdays();
    if( ! empty( $opening_hours ) ){
        $html = sprintf( '<table %s><tbody>', $attr );
        foreach ( $opening_hours as $index => $day ) {
            if( ! empty( $day['open'] ) && 1 == $day['open'] && $hide_closed_days ) continue;

            $am_open  = ! empty( $day['am'] ) && ! empty( $day['am']['open'] );
            $pm_open  = ! empty( $day['pm'] ) && ! empty( $day['pm']['open'] );
            $am_start = sprintf( __( '%s:%s', 'wp-seopress-pro' ), $day['am']['start']['hours'], $day['am']['start']['mins'] );
            $pm_start = sprintf( __( '%s:%s', 'wp-seopress-pro' ), $day['pm']['start']['hours'], $day['pm']['start']['mins'] );
            $am_end   = sprintf( __( '%s:%s', 'wp-seopress-pro' ), $day['am']['end']['hours'], $day['am']['end']['mins'] );
            $pm_end   = sprintf( __( '%s:%s', 'wp-seopress-pro' ), $day['pm']['end']['hours'], $day['pm']['end']['mins'] );
            $am_cell  = $am_open ? sprintf( '<td>%s - %s</td>', esc_html( $am_start ), esc_html( $am_end ) ) : '<td class="sp-lb-closed"></td>';
            $pm_cell  = $pm_open ? sprintf( '<td>%s - %s</td>', esc_html( $pm_start ), esc_html( $pm_end ) ) : '<td class="sp-lb-closed"></td>';

            if( ! $am_open && ! $pm_open ){
                if( $hide_closed_days ) continue;
                $html .= sprintf( '<tr class="sp-lb-closed"><th scope="row">%s</th><td colspan="2" class="sp-lb-closed sp-lb-closed-all-day">%s</td></tr>', esc_html( $days[$index] ), esc_html( __('Closed', 'wp-seopress-pro') ) );
            } else {
                $html .= sprintf( '<tr><th scope="row">%s</th>%s%s</tr>', esc_html( $days[$index] ), $am_cell, $pm_cell );
            }

        }
        $html .= '</tbody></table>';
    }
    return apply_filters( 'seopress_pro_local_business_opening_hours', $html );
}

/**
 * Returns array of weekdays
 *
 * @return  array
 */
function seopress_pro_get_weekdays(){
    return array(
        __( 'Monday', 'wp-seopress-pro' ),
        __( 'Tuesday', 'wp-seopress-pro' ),
        __( 'Wednesday', 'wp-seopress-pro' ),
        __( 'Thursday', 'wp-seopress-pro' ),
        __( 'Friday', 'wp-seopress-pro' ),
        __( 'Saturday', 'wp-seopress-pro' ),
        __( 'Sunday', 'wp-seopress-pro' ),
    );
}
