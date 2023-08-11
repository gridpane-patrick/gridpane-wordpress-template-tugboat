<?php
extract( $args );

$filters = yith_sl_get_filters_with_terms();

if( $show_filter_radius === 'yes' ):  ?>

    <div class="wrapper-filter type-dropdown <?php echo $filter_radius_step_default != '' ? 'selected' : ''; ?>" data-taxonomy="yisl_radius">
        <p class="open-dropdown filter-label radius" data-title="<?php echo $filter_radius_title; ?>">
            <span class="text"><?php echo !empty( $filter_radius_step_default ) ? $filter_radius_title . ': ' . $filter_radius_step_default . $distance_unit : $filter_radius_title; ?></span>
            <svg class="hovered-paths dropdown-arrow" xml:space="preserve" style="" viewBox="0 0 306 306" height="10" width="10" y="0px" x="0px" id=""><g><g>
                        <g id="chevron-right">
                            <polygon points="94.35,0 58.65,35.7 175.95,153 58.65,270.3 94.35,306 247.35,153   " data-original="#000000" class="hovered-path active-path" style="fill:#707070" data-old_color="#000000"></polygon>
                        </g>
                    </g></g>
            </svg>
        </p>
        <div class="wrapper-options">
            <ul>
                <li>
                    <label for="yith-sl-filter-radius-all" data-title="<?php $filter_radius_title ?>"><?php esc_html_e( 'Any', 'yith-store-locator' ); ?></label>
                    <input id="yith-sl-filter-radius-all" type="radio" name="yith-sl-filter-radius" value="selectall" data-taxonomy="yisl_radius" data-value="selectall">
                </li>
                <?php foreach ( $filters['radius']['terms'] as $term ): ?>
                    <?php $value = $term->name; ?>
                    <li class="<?php echo $filter_radius_step_default === $value ? 'active' : ''; ?>">
                        <label for="yith-sl-filter-radius-<?php echo $value ?>" data-title="<?php echo $value . ' ' . $distance_unit ?>"><?php echo $value . ' ' . $distance_unit; ?></label>
                        <input id="yith-sl-filter-radius-<?php echo $value ?>" type="radio" name="yith-sl-filter-radius" value="<?php echo $value ?>" <?php checked( $filter_radius_step_default, $value) ?> data-taxonomy="yisl_radius" data-value="<?php echo $value ?>">
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif;

if( $show_filters === 'yes' ):

    foreach ( $filters['stores'] as $filter ): ?>

        <div class="wrapper-filter <?php echo 'type-' . $filter['type'] ?>" data-taxonomy="<?php echo $prefix . $filter['slug'] ?>">

            <p class='filter-label open-dropdown' data-title="<?php echo $filter['label']; ?>">
                <?php if( $filter['icon'] != '' ): ?>
                    <img class="filter-icon" src="<?php esc_attr_e( $filter['icon'] ) ?>" width="15px" />
                <?php endif; ?>
                <span class="text"><?php echo $filter['label']; ?></span>
                <svg class="hovered-paths dropdown-arrow" xml:space="preserve" style="" viewBox="0 0 306 306" height="10" width="10" y="0px" x="0px" id=""><g><g>
                            <g id="chevron-right">
                                <polygon points="94.35,0 58.65,35.7 175.95,153 58.65,270.3 94.35,306 247.35,153   " data-original="#000000" class="hovered-path active-path" style="fill:#707070" data-old_color="#000000"></polygon>
                            </g>
                        </g></g>
                </svg>
            </p>

            <?php

            $args = array(
                'options'   =>  $filter['terms'],
                'style'     =>  $filter_display_mode,
                'label'     =>  $filter['label'],
                'taxonomy'  =>  $prefix . $filter['slug']
            );
            ?>
            <div class="wrapper-options">
                <ul>
                    <?php

                    yith_sl_get_template( $filter['type'] . '.php', '/frontend/fields/', $args );
                    ?>
                </ul>
            </div>

        </div>

    <?php endforeach;
endif; ?>


