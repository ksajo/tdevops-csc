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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart = &Tygh::$app['session']['cart'];
    if (isset($_REQUEST['boxberry_selected_point']) && is_array($_REQUEST['boxberry_selected_point'])) {
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
    }
}