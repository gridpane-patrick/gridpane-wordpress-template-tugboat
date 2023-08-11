<?php

namespace Hurrytimer\Placeholders;

use Hurrytimer\C;
use Hurrytimer\Dependencies\Carbon\Carbon;

class Date_Placeholder extends Placeholder
{

    /**
     * @var \Hurrytimer\Campaign
     */
    protected $campaign;

    public function __construct( $campaign )
    {
        $this->campaign = $campaign;
    }

    public function get_format()
    {
        return apply_filters( 'hurryt_date_format', get_option( 'date_format' ) );
    }

    public function get_value( $options = [] )
    {
        $placeholder = '{{date' . implode( '|', $options ) . '}}';
        $date = $placeholder;
        switch ( $this->campaign->mode ) {
            case C::MODE_REGULAR:
                $date = Carbon::parse( $this->campaign->getEndDatetime(), hurryt_tz() );
                break;
            case C::MODE_RECURRING:
                $date = $this->campaign->getRecurrenceEndDate();
                break;
        }

        return apply_filters( 'hurryt_placeholder_value', $this->format( $date, $options ), $placeholder,
            $this->campaign->get_id() );
    }

    /**
     * @param \Carbon\Carbon $date
     * @param array $params
     * @return string|void
     */
    public function format( $date, $params )
    {
        if ( !( $date instanceof Carbon ) ) {
            return $date;
        }
        // relative date.
        if ( !empty( $params ) && $params[ 0 ] === 'r' ) {
            if ( $date->isToday() ) {
                return __( 'Today', 'hurrytimer' );
            }
            if ( $date->isTomorrow() ) {
                return __( 'Tomorrow', 'hurrytimer' );
            }
        }

        return $date->format( $this->get_format() );
    }


}