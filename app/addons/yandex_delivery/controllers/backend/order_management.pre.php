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

use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (!empty($_REQUEST['select_yd_store'])) {

    foreach ($_REQUEST['select_yd_store'] as $g_id => $select) {
        foreach ($select as $s_id => $o_id) {
            Tygh::$app['session']['cart']['shippings_extra']['data'][$g_id][$s_id]['pickup_point_id'] = $o_id;
        }
    }

} elseif (!empty($_REQUEST['select_yd_courier'])) {

    foreach ($_REQUEST['select_yd_courier'] as $g_id => $select) {
        foreach ($select as $s_id => $o_id) {
            Tygh::$app['session']['cart']['shippings_extra']['data'][$g_id][$s_id]['courier_point_id'] = $o_id;
        }
    }

} elseif ($mode == 'update_shipping' || $mode == 'update') {

    $cart = &Tygh::$app['session']['cart'];
    if (!empty($cart['order_id'])) {
        $old_ship_data = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $cart['order_id'], 'L');

        if (!empty($old_ship_data)) {
            $old_ship_data = unserialize($old_ship_data);

            foreach ($old_ship_data as $shipping) {

                if ($shipping['module'] != YD_MODULE_NAME) {
                    continue;
                }

                $group_key = $shipping['group_key'];
                $shipping_id = $shipping['shipping_id'];

                if (fn_yandex_delivery_check_type_delivery($shipping['service_params'])) {
                    $cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'] = empty($cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id']) ? $shipping['point_id'] : $cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'];
                } else {
                    $cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'] = empty($cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id']) ? $shipping['point_id'] : $cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'];
                }
            }
        }
    }
}
