<?php
// Exit if accessed directly
!defined( 'YITH_SL' ) && exit();

$tab = array(
    'categories' => array(
        'custom-post-type_list_table' => array(
            'type'         => 'taxonomy',
            'taxonomy'    => YITH_Store_Locator_Post_Type::$category_taxonomy,
        ),
    )
);

return $tab;