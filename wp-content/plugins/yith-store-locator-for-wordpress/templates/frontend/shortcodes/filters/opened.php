<?php
extract($args);

$filters = yith_sl_get_filters_with_terms();

if ( $show_filter_radius === 'yes' ): ?>

    <div class="wrapper-filter type-dropdown" data-taxonomy="yisl_radius">
        <label for="filter-radius" class="filter-label">
            <?php echo esc_html( $filter_radius_title ); ?>
        </label>
        <select id="filter-radius" data-taxonomy="yisl_radius">
            <option value="selectall"><?php esc_html_e( 'Any', 'yith-store-locator' ); ?></option>
            <?php foreach ( $filters['radius']['terms'] as $term ): ?>
                <?php $value = $term->name; ?>
                <option value="<?php echo $value ?>" data-taxonomy="yisl_radius" data-value="<?php echo $value ?>" <?php selected( $filter_radius_step_default, $value ) ?> >
                    <?php echo $value . ' ' . $distance_unit; ?>
                </option>
            <?php endforeach; ?>

        </select>

    </div>
<?php endif;

if ($show_filters === 'yes' && !empty( $filters['stores'] ) ):

    foreach ( $filters['stores'] as $filter ):

        $args = array(
            'options'       => $filter['terms'],
            'style'         => $filter['type'],
            'label'         => $filter['label'],
            'taxonomy'      => $prefix . $filter['slug'],
            'show_label'    => $filter['show_label'],
            'icon'          => $filter['icon']
        );

        ?>

        <div class="wrapper-filter <?php echo 'type-' . $filter['type'] ?>" data-taxonomy="<?php echo $prefix . $filter['slug'] ?>">

            <?php if( $filter['type'] === 'dropdown' ):  ?>

                <?php yith_sl_get_template('select.php', '/frontend/fields/', $args); ?>


            <?php else: ?> <!-- checkbox -->

                <?php if ( apply_filters( 'yith_sl_show_filter_label', $filter['show_label'] ) === '1' ) : ?>
                    <?php if( $filter['icon'] != '' ): ?>
                        <img class="filter-icon" src="<?php echo esc_attr( $filter['icon'] ); ?>"/>
                    <?php endif; ?>
                    <h3 class='filter-label'><?php echo $filter['label']; ?> </h3>
                <?php endif; ?>

                <div class="wrapper-options">
                    <ul>
                        <?php
                        $filter_type = $filter['type'] === 'dropdown' ? 'select' : $filter['type'];
                        yith_sl_get_template('checkbox.php', '/frontend/fields/', $args);
                        ?>
                    </ul>
                </div>

            <?php endif; ?>
        </div>

    <?php endforeach;
endif; ?>


