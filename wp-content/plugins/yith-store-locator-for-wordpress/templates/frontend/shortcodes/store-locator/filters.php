<?php $filters = yith_sl_get_terms( YITH_Store_Locator_Post_Type::$filter_taxonomy ) ?>

<div class="wrap-search-filters">
    <?php foreach ( $filters as $filter ):
        $filters_args = array(
            'name'      =>  'yith-store-locators-filters[]',
            'multiple'  =>  true,
            'options'   =>  $filter['children'],
            'default'   =>  ''
        );
        $layout = empty(get_term_meta($filter['parent']->term_id, 'yith-sl-filter-layout',true)) ? 'dropdown' : get_term_meta($filter['parent']->term_id, 'yith-sl-filter-layout',true);
    ?>
    <div class="wrap-search-filter">
        <h4 class="section-title parent-name"><?php echo $filter['parent']->name ?></h4>
        <?php yith_sl_get_template( $layout . '.php', 'frontend/fields/',$filters_args ) ?>
    </div>
    <?php endforeach; ?>

</div>