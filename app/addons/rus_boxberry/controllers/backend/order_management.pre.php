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

$cart = &Tygh::$app['session']['cart'];

if (!empty($_REQUEST['boxberry_selected_point'])) {

    foreach ($_REQUEST['boxberry_selected_point'] as $group_id => $shippings) {
        if (!is_array($shippings)) {
            continue;
        }
        foreach ($shippings as $shipping_id => $boxberry_point) {
            if (!empty($boxberry_point)) {
                $cart['shippings_extra']['boxberry'][$group_id][$shipping_id]['point_id'] = $boxberry_point;
            }
        }
    }

} elseif ($mode == 'update') {

    if (!empty($cart['order_id'])) {
        $old_ship_data = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $cart['order_id'], 'L');

        if (!empty($old_ship_data)) {
            $old_ship_data = unserialize($old_ship_data);

            foreach ($old_ship_data as $shipping) {
                if (empty($shipping['point_id']) || $shipping['module'] !== 'rus_boxberry') {
                    continue;
                }

                $group_key = $shipping['group_key'];
                $shipping_id = $shipping['shipping_id'];

                if (empty($cart['shippings_extra']['boxberry'][$group_key][$shipping_id]['point_id'])) {
                    $cart['shippings_extra']['boxberry'][$group_key][$shipping_id]['point_id'] = $shipping['point_id'];
                    $cart['shippings_extra']['boxberry'][$group_key][$shipping_id]['pickup_data'] = $shipping['pickup_data'];
                }
            }
        }
    }
}
