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
use Tygh\Languages\Languages;
use Tygh\Shippings\RusPickpoint;
use Tygh\Http;
use Tygh\Template\Document\Variables\PickpupPointVariable;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_pickpoint_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'pickpoint',
        'code' => 'pickpoint',
        'sp_file' => '',
        'description' => 'Pickpoint'
    );

    $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }

    $http_url = fn_get_storefront_protocol();
    $url = $http_url . '://e-solution.pickpoint.ru/api/';
    RusPickpoint::postamatPickpoint($url . 'postamatlist');
}

function fn_rus_pickpoint_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'pickpoint');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
}

function fn_rus_pickpoint_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['pickpoint_office'])) {
            $pickpoint_office = $cart['pickpoint_office'];
        } elseif (!empty($_REQUEST['pickpoint_office'])) {
            $pickpoint_office = $cart['pickpoint_office'] = $_REQUEST['pickpoint_office'];
        }

        if (!empty($pickpoint_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        if ($shipping['module'] != 'pickpoint') {
                            continue;
                        }

                        $shipping_id = $shipping['shipping_id'];

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];

                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                        }
                    }
                }
            }
        }

        foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
            foreach ($shippings as $shipping_id => $shippings_extra) {
                if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                    $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                    if ($module == 'pickpoint' && !empty($shippings_extra)) {
                        $pickpoint_cost = $shippings_extra['pickpoint_postamat']['Cost'];
                        if (!empty($cart['pickpoint_office'][$group_key][$shipping_id])) {
                            $shippings_extra['pickpoint_postamat'] = $cart['pickpoint_office'][$group_key][$shipping_id];
                        }

                        if (!empty($pickpoint_cost)) {
                            $shippings_extra['pickpoint_postamat']['pickpoint_cost'] = $pickpoint_cost;
                        }

                        $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];
                    if ($module == 'pickpoint' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        if (!empty($cart['pickpoint_office'][$group_key][$shipping_id])) {
                            $shipping_extra['pickpoint_postamat'] = $cart['pickpoint_office'][$group_key][$shipping_id];
                        }

                        if (!empty($pickpoint_cost)) {
                            $shipping_extra['pickpoint_postamat']['pickpoint_cost'] = $pickpoint_cost;
                        }

                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

function fn_rus_pickpoint_init_user_session_data(&$sess_data, $user_id)
{
    $sess_data['cart']['pickpoint_office'] = array();
}

/**
 * Hook handler: injects pickup point into order data.
 */
function fn_rus_pickpoint_pickup_point_variable_init(
    PickpupPointVariable $instance,
    $order,
    $lang_code,
    &$is_selected,
    &$name,
    &$phone,
    &$full_address,
    &$open_hours_raw,
    &$open_hours,
    &$description_raw,
    &$description
) {
    if (!empty($order['shipping'])) {
        if (is_array($order['shipping'])) {
            $shipping = reset($order['shipping']);
        } else {
            $shipping = $order['shipping'];
        }

        if (!isset($shipping['module']) || $shipping['module'] !== 'pickpoint') {
            return;
        }

        if (isset($shipping['data']['pickpoint_postamat']['pickup_data'])) {
            $pickup_data = $shipping['data']['pickpoint_postamat']['pickup_data'];

            $is_selected = true;
            $name = $pickup_data['name'];
            $full_address = fn_rus_pickpoint_format_pickpoint_format_pickup_point_address($pickup_data);
            $open_hours_raw = fn_rus_pickpoint_format_pickup_point_open_hours($pickup_data['work_time'], $lang_code);
            $open_hours = implode('<br/>', $open_hours_raw);
        }
    }

    return;
}

/**
 * Formats Pickpoint pickup point address.
 *
 * @param string[] $pickup_point Pickup point data from API.
 *
 * @return string Address
 */
function fn_rus_pickpoint_format_pickpoint_format_pickup_point_address($pickup_point)
{
    $address_parts = array_filter([
        $pickup_point['post_code'],
        $pickup_point['region_name'],
        $pickup_point['city_name'],
        $pickup_point['address']
    ], 'fn_string_not_empty');

    $address = implode(', ', $address_parts);

    return $address;
}

/**
 * Formats Pickpoint pickup point open hours.
 *
 * @param string $work_time Pickup point work time from API response.
 * @param string $lang_code Two-letter language code
 *
 * @return string[] Open hours
 */
function fn_rus_pickpoint_format_pickup_point_open_hours($work_time, $lang_code)
{
    $open_hours = [];
    $work_days = explode(',', $work_time);
    $intervals = [];
    $interval = ['[first_day]' => null, '[last_day]' => null, '[schedule]' => null];
    foreach ($work_days as $day => $time) {
        $day = ++$day=== 7 ? 0 : $day;
        if ($interval['[schedule]'] === null) {
            $interval['[first_day]'] = __("weekday_{$day}", [], $lang_code);
            $interval['[schedule]'] = $time;
        } elseif ($time === $interval['[schedule]']) {
            $interval['[last_day]'] = __("weekday_{$day}", [], $lang_code);
            continue;
        } else {
            $intervals[] = $interval;
            $interval = ['[first_day]' => __("weekday_{$day}", [], $lang_code), '[last_day]' => null, '[schedule]' => $time];
            continue;
        }
    }
    $intervals[] = $interval;

    foreach ($intervals as $interval) {
        $schedule_type = 'interval';
        if ($interval['[schedule]'] === 'NODAY') {
            $schedule_type = 'closed';
        }

        $day_type = 'interval';
        if (count($intervals) === 1) {
            $day_type = 'every';
        } elseif ($interval['[last_day]'] === null) {
            $day_type = 'single';
        }

        $open_hours[] = __("rus_pickpoint.day_{$day_type}.schedule_{$schedule_type}", $interval, $lang_code);
    }

    return $open_hours;
}

/**
 * The "update_shipping" hook handler.
 *
 * Actions performed:
 *  - Adds service parameters field to pickpoint shipping
 *
 * @param array<string, string> $shipping_data Information about examined shipping method
 * @param int                   $shipping_id   Shipping method identifier
 * @param string                $lang_code     Selected language code
 *
 * @see \fn_update_shipping()
 */
function fn_rus_pickpoint_update_shipping(array &$shipping_data, $shipping_id, $lang_code)
{
    if (!empty($shipping_data['service_params']) || empty($shipping_data['service_id'])) {
        return;
    }

    $service_id = (int) db_get_field('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s', 'pickpoint', 'pickpoint');
    if (empty($service_id) || (int) $shipping_data['service_id'] !== $service_id) {
        return;
    }

    $shipping_data['service_params'] = serialize([
        'pickpoint_width' => '',
        'pickpoint_height' => '',
        'pickpoint_length' => '',
        'delivery_mode' => 'Standard'
    ]);
}
