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

use Tygh\Registry;
use Tygh\Shippings\YandexDelivery\YandexDelivery;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'create_yandex_order' && !empty($_REQUEST['yandex_order'])) {
        $result = fn_yandex_delivery_create_yandex_order($_REQUEST['yandex_order']);

        $redirect_url = 'shipments.yandex_delivery';
        if (!empty($_REQUEST['redirect_url'])) {
            $redirect_url = $_REQUEST['redirect_url'];
        }

        return array(CONTROLLER_STATUS_OK, $redirect_url);
    }
}

$navigation_sections = Registry::get('navigation.dynamic.sections');
$navigation_sections['shipments'] = array(
    'title' => __('shipments'),
    'href' => fn_url('shipments.manage'),
);
$navigation_sections['yandex_delivery'] = array(
    'title' => __('yandex_delivery.orders'),
    'href' => fn_url('shipments.yandex_delivery'),
);

if (fn_yandex_delivery_check_orders() && $mode != 'details') {
    Registry::set('navigation.dynamic.sections', $navigation_sections);
}

if ($mode == 'manage') {

    if (fn_yandex_delivery_check_orders()) {
        Registry::set('navigation.dynamic.active_section', 'shipments');
    }

} elseif ($mode == 'yandex_delivery') {

    list($yd_orders, $search) = fn_yandex_delivery_get_orders($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    if (fn_yandex_delivery_check_orders()) {
        Registry::set('navigation.dynamic.active_section', 'yandex_delivery');
    } else {
        return array(CONTROLLER_STATUS_OK, 'shipments.manage');
    }

    $yd_statuses = fn_yandex_delivery_get_statuses();

    $shipment_yd_statuses = array();
    foreach ($yd_statuses as $key => $status) {
        $shipment_yd_statuses[$key] = $status['yd_status_name'];
    }

    Tygh::$app['view']->assign('yd_orders', $yd_orders);
    Tygh::$app['view']->assign('shipment_yd_statuses', $shipment_yd_statuses);
    Tygh::$app['view']->assign('search', $search);

}