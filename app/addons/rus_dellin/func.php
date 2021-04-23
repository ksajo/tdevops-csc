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
use Tygh\Languages\Languages;
use Tygh\Template\Document\Variables\PickpupPointVariable;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_dellin_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'dellin',
        'code' => '301',
        'sp_file' => '',
        'description' => 'Деловые линии'
    );

    $service_id = db_query('INSERT INTO ?:shipping_services ?e', $service);
    $service['service_id'] = $service_id;

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }

    $path = Registry::get('config.dir.root') . '/app/addons/rus_dellin/database/dellin_cities.csv';
    fn_rus_cities_read_cities_by_chunk($path, RUS_CITIES_FILE_READ_CHUNK_SIZE, 'fn_rus_dellin_add_cities_in_table');

    $dellin_demo = Registry::get('config.dir.addons') . 'rus_dellin/database/dellin_demo.sql';
    if (file_exists($dellin_demo)) {
        db_import_sql_file($dellin_demo, 16348, false, false);
        fn_rm($dellin_demo);
    }
}

function fn_rus_dellin_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'dellin');

    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
}

function fn_rus_dellin_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['arrival_terminal'])) {
            $arrival_terminal = $cart['arrival_terminal'];

        } elseif (!empty($_REQUEST['arrival_terminal'])) {
            $arrival_terminal = $cart['arrival_terminal'] = $_REQUEST['arrival_terminal'];
        }

        if (!empty($arrival_terminal)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'dellin') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id]['arrival_terminals'];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_data'] = $shippings_extra;
                            if (!empty($arrival_terminal[$group_key][$shipping_id])) {
                                $terminal_id = $arrival_terminal[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_id'] = $terminal_id;

                                foreach ($shippings_extra as $_terminal) {
                                    if ($_terminal['code'] == $terminal_id) {
                                        $terminal_data = $_terminal;
                                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_data'] = $terminal_data;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($cart['shippings_extra']['data'])) {
            foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach ($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'dellin' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;
                        }
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];

                    if ($module == 'dellin' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

/**
 * Adds the cities data in the table.
 *
 * @param array $rows The array with cities data.
 *
 * @return void.
 */
function fn_rus_dellin_add_cities_in_table($rows)
{
    $cities_hash = fn_rus_cities_get_all_cities($rows);

    foreach ($rows as $city_data) {
        $city_data['City'] = fn_strtolower($city_data['City']);

        if (!empty($cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']])) {
            $city_id = $cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']];

            $city = array(
                'city_id' => $city_id,
                'number_city' => $city_data['id'],
                'code_kladr' => $city_data['codeKLADR'],
                'is_terminal' => $city_data['is_terminal'],
            );

            db_replace_into('rus_dellin_cities_link', $city);
        }
    }
}

/**
 * Gets the code kladr for dellin.
 *
 * @param array  $params    The parameters for search of city
 * @param string $lang_code The language code (e.g. 'en', 'ru', etc.).
 *
 * @return int The identificator for dellin.
 */
function fn_rus_dellin_get_code_city($params, $lang_code = CART_LANGUAGE)
{
    $condition = '';

    if (!empty($params['country'])) {
        $condition .= db_quote(' AND country_code = ?s', $params['country']);
    }

    if (!empty($params['state'])) {
        $condition .= db_quote(' AND state_code = ?s', $params['state']);
    }

    if (!empty($params['city'])) {
        $condition .= db_quote(' AND city = ?s', $params['city']);
    }

    $code_kladr = db_get_field(
        'SELECT code_kladr FROM ?:rus_city_descriptions'
        . ' LEFT JOIN ?:rus_cities'
        . ' ON ?:rus_city_descriptions.city_id = ?:rus_cities.city_id'
        . ' LEFT JOIN ?:rus_dellin_cities_link'
        . ' ON ?:rus_cities.city_id = ?:rus_dellin_cities_link.city_id'
        . ' WHERE ?:rus_cities.status = ?s AND lang_code = ?s ?p',
        'A',
        $lang_code,
        $condition
    );

    return $code_kladr;
}

/**
 * Hook handler: injects pickup point into order data.
 */
function fn_rus_dellin_pickup_point_variable_init(
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

        if (!isset($shipping['module']) || $shipping['module'] !== 'dellin') {
            return;
        }

        if (isset($shipping['terminal_data'])) {
            $pickup_data = $shipping['terminal_data'];

            $is_selected = true;
            $name = $pickup_data['name'];
            $full_address = $pickup_data['address'];
        }
    }

    return;
}
