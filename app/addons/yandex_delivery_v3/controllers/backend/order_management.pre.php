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

use Tygh\Addons\YandexDelivery\Enum\DeliveryType;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;
use Tygh\Enum\OrderDataTypes;

defined('BOOTSTRAP') or die('Access denied');

if (isset($_REQUEST['select_yad_office'])) {
    foreach ($_REQUEST['select_yad_office'] as $group_id => $shipping) {
        $company_id = Tygh::$app['session']['cart']['product_groups'][$group_id]['company_id'];
        foreach ($shipping as $shipping_id => $pickup_point_id) {
            Tygh::$app['session']['cart']['selected_yad_office'][$company_id][$shipping_id]['pickup_point_id'] = $pickup_point_id;
        }
    }
} elseif (isset($_REQUEST['select_yad_courier'])) {
    foreach ($_REQUEST['select_yad_courier'] as $group_id => $shipping) {
        $company_id = Tygh::$app['session']['cart']['product_groups'][$group_id]['company_id'];
        foreach ($shipping as $shipping_id => $courier_id) {
            Tygh::$app['session']['cart']['selected_yad_courier'][$company_id][$shipping_id]['courier_point_id'] = $courier_id;
        }
    }
/** @var string $mode */
} elseif ($mode === 'update_shipping' || $mode === 'update') {
    $cart = Tygh::$app['session']['cart'];

    if (empty($cart['order_id'])) {
        return;
    }
    $old_shipping_data = db_get_field(
        'SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s',
        $cart['order_id'],
        OrderDataTypes::SHIPPING
    );

    if (empty($old_shipping_data)) {
        return;
    }
    $old_shipping_data = unserialize($old_shipping_data);
    foreach ($old_shipping_data as $shipping) {
        if ($shipping['module'] !== YandexDeliveryService::MODULE) {
            continue;
        }
        $group_key = $shipping['group_key'];
        $shipping_id = $shipping['shipping_id'];

        switch ($shipping['service_params']['type_delivery']) {
            case DeliveryType::PICKUP:
                $cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'] =
                    empty($cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'])
                        ? $shipping['point_id']
                        : $cart['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'];
                break;
            case DeliveryType::COURIER:
                $cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'] =
                    empty($cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'])
                        ? $shipping['point_id']
                        : $cart['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'];
                break;
            case DeliveryType::POST:
            default:
                break;
        }
    }
}
