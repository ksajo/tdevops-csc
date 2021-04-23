<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

defined('BOOTSTRAP') or die('Access denied');

$schema = [
    'engaged_visitor'                => [
        'name'       => __('yandex_metrika_engaged_visitor_text'),
        'type'       => 'number',
        'class'      => 1,
        'depth'      => 5,
        'conditions' => [],
        'flag'       => '',
    ],
    'basket'                         => [
        'name'       => __('yandex_metrika_basket_text'),
        'type'       => 'action',
        'class'      => 1,
        'flag'       => 'basket',
        'depth'      => 0,
        'conditions' => [
            [
                'url'  => 'basket',
                'type' => 'exact',
            ],
        ],
    ],
    'order'                          => [
        'name'       => __('yandex_metrika_order_text'),
        'type'       => 'action',
        'class'      => 1,
        'flag'       => 'order',
        'depth'      => 0,
        'conditions' => [
            [
                'url'  => 'order',
                'type' => 'exact',
            ],
        ],
        'controller' => 'checkout',
        'mode'       => 'complete',
    ],
    'wishlist'                       => [
        'name'       => __('yandex_metrika_wishlist_text'),
        'type'       => 'action',
        'class'      => 1,
        'flag'       => '',
        'depth'      => 0,
        'conditions' => [
            [
                'url'  => 'wishlist',
                'type' => 'exact',
            ],
        ],
    ],
    'buy_with_one_click_form_opened' => [
        'name'       => __('yandex_metrika_buy_with_one_click_form_opened_text'),
        'type'       => 'action',
        'class'      => 1,
        'flag'       => '',
        'depth'      => 0,
        'conditions' => [
            [
                'url'  => 'buy_with_one_click_form_opened',
                'type' => 'exact',
            ],
        ],
    ],
    'call_request'                   => [
        'name'       => __('yandex_metrika_call_request_text'),
        'type'       => 'action',
        'class'      => 1,
        'flag'       => '',
        'depth'      => 0,
        'conditions' => [
            [
                'url'  => 'call_request',
                'type' => 'exact',
            ],
        ],
    ],
];

return $schema;
