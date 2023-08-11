<?php
!defined( 'YITH_SL' ) && exit();

$tab = array(
    'stores' => array(
        'stores-options' => array(
            'type'     => 'multi_tab',
            'sub-tabs' => array(
                'stores-list'        => array(
                    'title' => esc_html__('All stores','yith-store-locator'),
                ),
                'stores-filters'        => array(
                    'title' => esc_html__('Stores Filters','yith-store-locator')
                ),
                'stores-page-details'        => array(
                    'title' => esc_html__('Stores Pages','yith-store-locator')
                ),
            )
        )
    )
);

return $tab;