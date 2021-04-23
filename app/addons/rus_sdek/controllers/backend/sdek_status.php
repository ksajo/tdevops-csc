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

use Tygh\Shippings\RusSdek;
use Tygh\Registry;
use Tygh\Shippings\Shippings;
use Tygh\Navigation\LastView;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $suffix = ".manage";
    $params = $_REQUEST;

    if ($mode == 'manage') {
        $post_fix = '';
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

        $suffix = ".manage" . $post_fix;
    }

    return array(CONTROLLER_STATUS_OK, "sdek_status$suffix");
}

if ($mode == 'manage') {
    $params = $_REQUEST;
    $t_date = date("Y-m-d", TIME);
    $shipping = db_get_array("SELECT b.service_params FROM ?:shipping_services as a LEFT JOIN ?:shippings as b ON a.service_id = b.service_id WHERE a.module = 'sdek'");
    $data_status = array();

    $data['period'] = !empty($params['period']) ? $params['period'] : 'A';
    list($data['time_from'], $data['time_to']) = fn_create_periods($_REQUEST);
    if ($data['period'] == 'A') {
        $data['time_from'] = date("Y-01-1 00:00:00");
        $data['time_to'] = date("Y-m-d 23:59:59", $data['time_to']);
    } else {
        $data['time_from'] = date("Y-m-d 00:00:00", $data['time_from']);
        $data['time_to'] = date("Y-m-d 23:59:59", $data['time_to']);
    }

    foreach ($shipping as $shipping_id => $d_shipping) {
        $service_params = unserialize($d_shipping['service_params']);
        if (!empty($service_params['authlogin']) && !empty($service_params['authpassword'])) {
            $shipping_params['Account'] = $service_params['authlogin'];
            $shipping_params['Secure'] = md5($t_date . '&' . $service_params['authpassword']);
            $shipping_params['Date'] = $t_date;
            $shipping_params['ChangePeriod']['DateFirst'] = $data['time_from'];
            $shipping_params['ChangePeriod']['DateLast'] = $data['time_to'];

            $d_status = RusSdek::orderStatusXml($shipping_params);
            RusSdek::addStatusOrders($d_status);
        } else {
            fn_set_notification('E', __('notice'), __('shippings.sdek.account_password_error'));
        }
    }

    $params['time_from'] = $data['time_from'];
    $params['time_to'] = $data['time_to'];
    $data['time_from'] = strtotime($data['time_from']);
    $data['time_to'] = strtotime($data['time_to']);
    list($data_status, $search) = fn_rus_sdek_get_status($params, Registry::get('settings.Appearance.admin_elements_per_page'));
    Tygh::$app['view']->assign('period', $data);
    Tygh::$app['view']->assign('data_status', $data_status);
    Tygh::$app['view']->assign('search', $search);
}
