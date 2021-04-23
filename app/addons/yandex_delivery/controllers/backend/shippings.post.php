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

use Tygh\Shippings\YandexDelivery\YandexDelivery;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == "configure") {

    if (!empty($_REQUEST['module']) && $_REQUEST['module'] == YD_MODULE_NAME && !empty($_REQUEST['shipping_id'])) {
        $yd = YandexDelivery::init($_REQUEST['shipping_id']);

        $shipping = fn_get_shipping_params($_REQUEST['shipping_id']);
        $senders = $yd->getSendersList();
        
        $warehouses = $yd->getWarehousesList();
        $requisites = $yd->getRequisiteList();
        $deliveries = $yd->getDeliveries();

        $shipping = fn_get_shipping_params($_REQUEST['shipping_id']);

        $deliveries_select = array();
        if (!empty($shipping['deliveries'])) {
            foreach($shipping['deliveries'] as $delivery_id) {
                $deliveries_select[$delivery_id] = $deliveries[$delivery_id];
            }
        }

        Tygh::$app['view']->assign('deliveries', $deliveries);
        Tygh::$app['view']->assign('deliveries_select', $deliveries_select);
        Tygh::$app['view']->assign('requisites', $requisites);
        Tygh::$app['view']->assign('warehouses', $warehouses);
        Tygh::$app['view']->assign('senders', $senders);
        Tygh::$app['view']->assign('available_city_from', $yd->available_city_from);
    }
}
