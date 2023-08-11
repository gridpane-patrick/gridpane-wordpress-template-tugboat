<?php extract( $args );
$show_active_filters_text   =   yith_sl_get_option( "enable-active-filters-text" , 'yes' );
$active_filters_text        =   yith_sl_get_option( "active-filters-text", esc_html__( "Active filters:", "yith-store-locator"  ) );


if( $show_active_filters_text ): ?>
    <span class="section-label">
        <?php echo $active_filters_text; ?>
    </span>
<?php endif; ?>
<ul id="wrapper-active-filters">
    <?php if( is_array( $filters ) && !empty( $filters ) ): ?>
        <?php foreach ( $filters as $key => $values ): ?>
            <li class="wrapper-filter">
                <span class="filter-name" data-taxonomy="<?php echo $key; ?>">
                    <?php

                    $filter_slug = str_replace ( 'yisl_','', $key );

                    $label = yith_sl_get_filter_taxonomy_label_by_slug( $filter_slug );

                    echo $label; ?>
                </span>
                <ul class="wrapper-terms">
                    <?php foreach ( $values as $term  ): ?>
                        <li>
                            <span class="term-name">
                                <?php if( $key === 'yisl_radius' ):
                                    echo $term . ' ' . yith_sl_get_option( 'filter-radius-distance-unit', 'km' ) ;
                                else:
                                    $name = yith_sl_get_term_name_by_id( $term );
                                    echo $name;
                                endif; ?>
                            </span>
                            <span class="remove-term" data-taxonomy="<?php echo esc_attr( $key ); ?>" data-value="<?php echo esc_attr( $term ); ?>">
                                x
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
<span id="yith-sl-reset-all-filters">
    <?php echo apply_filters( 'yith-sl-clear-all-filters', esc_html__( 'Clear all', 'yith-store-locator' ) ); ?>
</span>