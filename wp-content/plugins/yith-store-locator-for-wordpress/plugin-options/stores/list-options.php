<?php
// Exit if accessed directly
!defined( 'YITH_SL' ) && exit();

$tab = array(
    'stores-list' => array(
        'custom-post-type_list_table' => array(
            'type'         => 'post_type',
            'post_type'    => YITH_Store_Locator_Post_Type::$post_type_name,
        ),
    )
);

return $tab;