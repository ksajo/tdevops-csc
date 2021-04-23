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
use Tygh\Shippings\RusSdek;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = $_REQUEST;

    if ($mode == 'sdek_delivery') {
        $post_fix = '';
        if (!empty($params['sdek_order_id'])) {
            $post_fix .= '&sdek_order_id=' . $params['sdek_order_id'];
        }
        if (!empty($params['period'])) {
            $post_fix .= '&period=' . $params['period'];
        }
        if (!empty($params['time_from'])) {
            $post_fix .= '&time_from=' . $params['time_from'];
        }
        if (!empty($params['time_to'])) {
            $post_fix .= '&time_to=' . $params['time_to'];
        }
        if (!empty($params['status'])) {
            $post_fix .= '&status=' . $params['status'];
        }

        $suffix = ".sdek_delivery" . $post_fix;

        return array(CONTROLLER_STATUS_OK, "shipments$suffix");
    }

    if ($mode == 'update_status') {
        if (!empty($params['shipment_ids'])) {
            foreach ($params['shipment_ids'] as $shipment_id) {
                $order_id = $params['sdek_ids'][$shipment_id];

                list($_shipments) = fn_get_shipments_info(array('order_id' => $order_id, 'advanced_info' => true, 'shipment_id' => $shipment_id));
                $shipment = reset($_shipments);
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
                );
                $data_auth = RusSdek::dataAuth($params_shipping);
                if (!empty($data_auth)) {
                    $date_status = RusSdek::orderStatusXml($data_auth, $order_id, $shipment_id);
                    RusSdek::addStatusOrders($date_status);
                }
            }

            fn_set_notification('N', __('notice'), __('addons.rus_sdek.text_update_status'));
        }
    }
}


$navigation_sections = Registry::get('navigation.dynamic.sections');
$navigation_sections['shipments'] = array(
    'title' => __('shipments'),
    'href' => fn_url('shipments.manage'),
);
$navigation_sections['sdek_delivery'] = array(
    'title' => __('sdek_delivery.orders'),
    'href' => fn_url('shipments.sdek_delivery')
);

if (fn_sdek_delivery_check_orders() && $mode != 'details') {
    Registry::set('navigation.dynamic.sections', $navigation_sections);
}

if ($mode == 'manage') {

    if (fn_sdek_delivery_check_orders()) {
        Registry::set('navigation.dynamic.active_section', 'shipments');
        Tygh::$app['view']->assign('shipments_sdek', 'Y');
    }

} elseif ($mode == 'sdek_delivery') {
    $params = $_REQUEST;
    $shipping = db_get_array(
        'SELECT b.service_params '
        . 'FROM ?:shipping_services as a '
        . 'LEFT JOIN ?:shippings as b '
            . 'ON a.service_id = b.service_id '
        . 'WHERE a.module = ?s AND a.status = ?s',
        'sdek', 'A'
    );
    $data_status = array();

    $data['period'] = !empty($params['period']) ? $params['period'] : 'A';
    list($data['time_from'], $data['time_to']) = fn_create_periods($_REQUEST);
    if ($data['period'] == 'A') {
        $data['time_from'] = date('Y-01-1 00:00:00');
        $data['time_to'] = date('Y-m-d 23:59:59', $data['time_to']);
    } else {
        $data['time_from'] = date('Y-m-d 00:00:00', $data['time_from']);
        $data['time_to'] = date('Y-m-d 23:59:59', $data['time_to']);
    }

    $params['time_from'] = $data['time_from'];
    $params['time_to'] = $data['time_to'];
    $data['time_from'] = strtotime($data['time_from']);
    $data['time_to'] = strtotime($data['time_to']);
    $data['sdek_order_id'] = (!empty($params['sdek_order_id'])) ? $params['sdek_order_id'] : '';
    list($data_status, $search) = fn_rus_sdek_get_status($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    $sdek_history = db_get_array('SELECT * FROM ?:rus_sdek_history_status');
    if (empty($sdek_history)) {
        return array(CONTROLLER_STATUS_OK, 'shipments.manage');
    }

    Registry::set('navigation.dynamic.active_section', 'sdek_delivery');

    Tygh::$app['view']->assign('period', $data);
    Tygh::$app['view']->assign('data_status', $data_status);
    Tygh::$app['view']->assign('search', $search);
}

if ($mode == 'update_status') {
    $params = $_REQUEST;

    if (!empty($params['data_update'])) {
        foreach ($params['data_update'] as $shipment_id => $order_id) {
            list($_shipments) = fn_get_shipments_info(array('order_id' => $order_id, 'advanced_info' => true, 'shipment_id' => $shipment_id));
            $shipment = reset($_shipments);
            $params_shipping = array(
                'shipping_id' => $shipment['shipping_id'],
                'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
            );
            $data_auth = RusSdek::dataAuth($params_shipping);
            if (empty($data_auth)) {
                continue;
            }
            $date_status = RusSdek::orderStatusXml($data_auth, $order_id, $shipment_id);
            RusSdek::addStatusOrders($date_status);
        }

        fn_set_notification('N', __('notice'), __('addons.rus_sdek.text_update_status'));
    }

    return array(CONTROLLER_STATUS_OK, 'shipments.sdek_delivery');
}

if ($mode == 'update_all_status') {
    $t_date = date("Y-m-d", TIME);

    $params = $_REQUEST;
    $data['period'] = !empty($params['period']) ? $params['period'] : 'A';
    list($data['time_from'], $data['time_to']) = fn_create_periods($_REQUEST);
    if ($data['period'] == 'A') {
        $data['time_from'] = date('Y-01-01 00:00:00');
        $data['time_to'] = date('Y-m-d 23:59:59', $data['time_to']);
    } else {
        $data['time_from'] = date('Y-m-d 00:00:00', $data['time_from']);
        $data['time_to'] = date('Y-m-d 23:59:59', $data['time_to']);
    }

    $shipping = db_get_array(
        'SELECT b.service_params '
        . 'FROM ?:shipping_services as a '
        . 'LEFT JOIN ?:shippings as b '
        . 'ON a.service_id = b.service_id '
        . 'WHERE a.module = ?s AND a.status = ?s',
        'sdek', 'A'
    );

    foreach ($shipping as $shipping_id => $d_shipping) {
        $service_params = unserialize($d_shipping['service_params']);
        if (!empty($service_params['authlogin']) && !empty($service_params['authpassword'])) {
            $shipping_params[$service_params['authlogin']]['Account'] = $service_params['authlogin'];
            $shipping_params[$service_params['authlogin']]['Secure'] = md5($t_date . '&' . $service_params['authpassword']);
            $shipping_params[$service_params['authlogin']]['Date'] = $t_date;
            $shipping_params[$service_params['authlogin']]['ChangePeriod']['DateFirst'] = $data['time_from'];
            $shipping_params[$service_params['authlogin']]['ChangePeriod']['DateLast'] = $data['time_to'];
        }
    }

    foreach ($shipping_params as $data_shipping) {
        $d_status = RusSdek::orderStatusXml($data_shipping);
        RusSdek::addStatusOrders($d_status);
    }

    fn_set_notification('N', __('notice'), __('addons.rus_sdek.text_update_status'));

    return array(CONTROLLER_STATUS_OK, 'shipments.sdek_delivery');
}