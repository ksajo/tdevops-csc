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

use Tygh\Enum\NotificationSeverity;
use Tygh\Registry;
use Tygh\Shippings\RusPickpoint;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = &\Tygh::$app['session']['cart'];
$params = $_REQUEST;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update_steps' && !empty($_REQUEST['update_step']) && $_REQUEST['update_step'] == 'step_two') {
        $old_user_data = $cart['user_data'];
        $user_data = $_REQUEST['user_data'];

        foreach (array('country', 'state', 'city') as $address_field) {
            $field_billing = BILLING_ADDRESS_PREFIX . '_' . $address_field;
            $field_shipping = SHIPPING_ADDRESS_PREFIX . '_' . $address_field;
            if (!empty($user_data[$field_billing]) && $user_data[$field_billing] != $old_user_data[$field_billing]
                || !empty($user_data[$field_shipping]) && $user_data[$field_shipping] != $old_user_data[$field_shipping]
            ) {
                $cart['pickpoint_office'] = array();
                break;
            }
        }
    }
}

if ($mode == 'update_steps' || $mode == 'shipping_estimation') {
    if (isset(Tygh::$app['session']['cart']['pickpoint_office'])) {
        unset(Tygh::$app['session']['cart']['pickpoint_office']);
        unset($cart['pickpoint_office']);
    }
    $city = $fromcity = '';
    $shipping_ids = (!empty($params['shipping_ids'])) ? $params['shipping_ids'] : array();
    $cities = fn_get_schema('pickpoint', 'cities', 'php', true);
    $p_group_key = (!empty($params['group_key'])) ? $params['group_key'] : 0;

    if (!empty($params['to_state'])) {
        $fromcity = fn_get_state_name($params['to_state'], $params['country'], 'RU');
        $pickpoint_cities = $cities[$params['to_state']];
        $city = key($pickpoint_cities);

    } elseif (!empty($params['customer_location'])) {
        $fromcity = fn_get_state_name($params['customer_location']['state'], $params['customer_location']['country'], 'RU');

        if (!empty($params['customer_location']['city'])) {
            $city = $params['customer_location']['city'];
        } elseif (!empty($params['customer_location']['state'])) {
            $pickpoint_cities = $cities[$params['customer_location']['state']];
            $city = key($pickpoint_cities);
        }
    }

    if (!empty($params['pickpoint_office'])) {
        foreach ($params['pickpoint_office'] as $g_id => $select) {
            foreach ($select as $s_id => $o_id) {
                $cart['pickpoint_office'][$g_id][$s_id] = $o_id;
            }
        }

    } elseif (!empty($params['pickpoint_id']) && !empty($params['address_pickpoint']) && !empty($shipping_ids)) {
        $pickpoint_office = array(
            'pickpoint_id' => $params['pickpoint_id'],
            'address_pickpoint' => $params['address_pickpoint'],
            'pickup_data' => RusPickpoint::getPickpointPostamatById($params['pickpoint_id']),
        );

        foreach ($shipping_ids as $group_key => $shipping_id) {
            if ($group_key == $p_group_key) {
                $cart['pickpoint_office'][$group_key][$shipping_id] = $pickpoint_office;
            }
        }

    } elseif (!empty($fromcity) && !empty($city)) {
        $shipping_info = (!empty($cart['product_groups'])) ? reset($cart['product_groups']) : '';
        $service_data = (!empty($shipping_info['chosen_shippings'])) ? reset($shipping_info['chosen_shippings']) : '';

        if (!empty($shipping_ids)) {
            foreach ($shipping_ids as $group_key => $shipping_id) {
                $shipping_info['keys']['group_key'] = $group_key;
                $shipping_info['keys']['shipping_id'] = $shipping_id;
            }
        }

        if (empty($service_data)) {
            foreach ($shipping_info['shippings'] as $shipping_id => $shipping) {
                if ($shipping['module'] == 'pickpoint') {
                    $service_data = $shipping;
                }
            }
        }

        $pickpoint_office = array();
        if (!empty($cart['pickpoint_office'])) {
            $pickpoint_offices = reset($cart['pickpoint_office']);
            $pickpoint_office = reset($pickpoint_offices);
        }

        if (!empty($cart['product_groups'])) {
            foreach ($cart['product_groups'] as $group_key => $product_group) {
                foreach ($product_group['shippings'] as $shipping_id => $shipping) {
                    if (($shipping['module'] == 'pickpoint') && empty($cart['pickpoint_office'][$group_key][$shipping_id])) {
                        $cart['pickpoint_office'][$group_key][$shipping_id] = $pickpoint_office;
                    }
                }
            }
        }
    }

    if (!empty($cart['pickpoint_office'])) {
        Tygh::$app['view']->assign('pickpoint_office', $cart['pickpoint_office']);
        Tygh::$app['view']->assign('p_office', reset($cart['pickpoint_office']));
    }

    Tygh::$app['view']->assign('fromcity', $fromcity);
    Tygh::$app['view']->assign('pickpoint_city', $city);
}

if ($mode == 'checkout' || $mode == 'cart') {
    $shipping_ids = (!empty($params['shipping_ids'])) ? $params['shipping_ids'] : array();
    $p_group_key = (!empty($params['group_key'])) ? $params['group_key'] : 0;

    if (!empty($params['pickpoint_id']) && !empty($params['address_pickpoint'])) {
        foreach ($shipping_ids as $group_key => $shipping_id) {
            if ($group_key == $p_group_key) {
                $pickup_data = RusPickpoint::getPickpointPostamatById($params['pickpoint_id']);
                if (empty($pickup_data) && isset($params['city'])) {
                    $pickpoint_id = 0;
                    $address_pickpoint = RusPickpoint::findPostamatPickpoint($pickpoint_id, $params['city']);
                    $pickup_data = RusPickpoint::getPickpointPostamatById($pickpoint_id);
                    fn_set_notification(NotificationSeverity::WARNING, __('warning'), __('rus_pickpoint.selected_pickpoint_not_working'));
                } else {
                    $pickpoint_id = $params['pickpoint_id'];
                    $address_pickpoint = $params['address_pickpoint'];
                }
                $pickpoint_office = array(
                    'pickpoint_id' => $pickpoint_id,
                    'address_pickpoint' => $address_pickpoint,
                    'pickup_data' => $pickup_data,
                );
                $cart['pickpoint_office'][$group_key][$shipping_id] = $pickpoint_office;
            }
        }

    } elseif (!empty($params['pickpoint_office'])) {
        foreach ($params['pickpoint_office'] as $g_id => $select) {
            foreach ($select as $s_id => $o_id) {
                $cart['pickpoint_office'][$g_id][$s_id] = $o_id;
            }
        }
    }
}
