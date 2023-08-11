<?php
// Exit if accessed directly
!defined( 'YITH_SL' ) && exit();

$tab = array(
    'stores-filters' => array(
        'stores-filters-tab' => array(
            'type'      =>  'custom_tab',
            'action'    =>  'yith_sl_output_filters_page',
        ),
    )
);

return $tab;