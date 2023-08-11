<?php

namespace Hurrytimer;

class CampaignShortcode
{

    /**
     * @var array The default shortcode attributes.
     */
    protected $default_attrs = [
        'id' => null,
        'run_in_background' => false,
        'sticky' => false
    ];

    /**
     * @var array The attributes array filter.
     */
    protected $attrs_filter_def = [
        'id' => FILTER_VALIDATE_INT,
        'run_in_background' => FILTER_VALIDATE_BOOLEAN,
        'sticky' => FILTER_VALIDATE_BOOLEAN
    ];

    /**
     * Init shortcode
     */
    function init()
    {
        add_shortcode( 'hurrytimer', [ $this, 'content' ] );
        add_filter( 'pre_do_shortcode_tag', [ $this, 'maybe_display' ], 10, 3 );
    }

    /**
     *
     * @param $return
     * @param $tag
     * @param $attr
     *
     * @return mixed|string|void
     */
    function maybe_display( $return, $tag, $attrs )
    {
        if ( $tag !== 'hurrytimer' ) {
            return $return;
        }


        $attrs = wp_parse_args( $attrs, $this->default_attrs );

        $attrs = filter_var_array( $attrs, $this->attrs_filter_def );

        if ( empty( $attrs[ 'id' ] ) ) {
            return __( 'HurryTimer: Invalid campaign ID.', 'hurrytimer' );
        }

        if ( !get_post( $attrs[ 'id' ] ) ) {
            return __( 'HurryTimer: Invalid campaign ID.', 'hurrytimer' );
        }

        $campaign = hurryt_get_campaign( $attrs[ 'id' ] );

        if ( $attrs[ 'sticky' ] && !$campaign->show_sticky_on_page( hurryt_current_page_id() ) ) {
            return apply_filters( 'hurryt_no_campaign', '', $campaign->get_id() );
        }

        if ( !$campaign->is_running() ) {

            // Let developers show something else whenever the campaign isn't running.
            return apply_filters( 'hurryt_no_campaign', '', $campaign->get_id() );
        }

        // Let developers decide whether to show the campaign or not.
        if ( !apply_filters( 'hurryt_show_campaign', true, $campaign->get_id() ) ) {
            return '';
        }


        return $return;
    }

    /**
     * Return shortcode content.
     *
     * @param $attrs
     *
     * @return string
     */
    public function content( $attrs )
    {

        $attrs = wp_parse_args( $attrs, $this->default_attrs );

        $attrs = filter_var_array( $attrs, $this->attrs_filter_def );

        $campaign = hurryt_get_campaign( absint( $attrs[ 'id' ] ) );

        if ( $campaign->is_expired() ) {
            ( new ActionManager( $campaign ) )->run();
        }

        // TODO: Move `build_template` to `TemplateBuilder->build(int $campaign_id)`.
        $template = apply_filters( 'hurryt_campaign_template', $campaign->build_template(), $campaign->get_id() );

        return !empty( $template ) ? $campaign->wrap_template( $template, $attrs ) : '';
    }


}
