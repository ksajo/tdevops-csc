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

use Tygh\Registry;
use Tygh\Languages\Languages;

function fn_rus_pecom_install()
{
    $objects = fn_rus_pecom_schema();

    foreach ($objects as $object) {
        $service = array(
            'status' => $object['status'],
            'module' => $object['module'],
            'code' => $object['code'],
            'sp_file' => $object['sp_file'],
            'description' => $object['description'],
        );

        $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

        foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
            db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
        }
    }

    $path = Registry::get('config.dir.root') . '/app/addons/rus_pecom/database/cities_pecom.csv';
    fn_rus_cities_read_cities_by_chunk($path, RUS_CITIES_FILE_READ_CHUNK_SIZE, 'fn_rus_pecom_add_cities_in_table');

    $pecom_demo = Registry::get('config.dir.addons') . 'rus_pecom/database/pecom_demo.sql';
    if (file_exists($pecom_demo)) {
        db_import_sql_file($pecom_demo, 16348, false, false);
        fn_rm($pecom_demo);
    }
}

function fn_rus_pecom_uninstall()
{
    $objects = fn_rus_pecom_schema();

    foreach ($objects as $object) {
        $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', $object['module']);

        if (!empty($service_ids)) {
            db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
            db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
        }
    }
}

function fn_rus_pecom_schema()
{
    return array(
        'pecom' => array(
            'ru' => 'Включить ПЭК',
            'status' => 'A',
            'module' => 'pecom',
            'code' => 'PECOM',
            'sp_file' => '',
            'description' => 'ПЭК'
        ),
    );
}

function fn_rus_pecom_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['shippings_extra']['data'])) {
            foreach($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'pecom' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;

                            if (!empty($shippings_extra['delivery_time'])) {
                                $product_groups[$group_key]['shippings'][$shipping_id]['delivery_time'] = $shippings_extra['delivery_time'];
                            }
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
                    if ($module == 'pecom' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
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
function fn_rus_pecom_add_cities_in_table($rows)
{
    $cities_hash = fn_rus_cities_get_all_cities($rows);

    foreach ($rows as $city_data) {
        $city_data['City'] = fn_strtolower($city_data['City']);

        if (!empty($cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']])) {
            $city_id = $cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']];

            $city = array(
                'city_id' => $city_id,
                'pecom_id' => $city_data['pecom_id']
            );

            db_replace_into('rus_pecom_cities_link', $city);
        }
    }
}

/**
 * Gets the cities identificators.
 *
 * @param array  $params    The parameters for search of city
 * @param string $lang_code The language code (e.g. 'en', 'ru', etc.).
 *
 * @return int The identificator for pecom city.
 */
function fn_rus_pecom_get_city($params, $lang_code = CART_LANGUAGE)
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

    $pecom_id = db_get_field(
        'SELECT pecom_id FROM ?:rus_city_descriptions'
        . ' LEFT JOIN ?:rus_cities'
            . ' ON ?:rus_city_descriptions.city_id = ?:rus_cities.city_id'
        . ' LEFT JOIN ?:rus_pecom_cities_link'
            . ' ON ?:rus_cities.city_id = ?:rus_pecom_cities_link.city_id'
        . ' WHERE ?:rus_cities.status = ?s AND lang_code = ?s ?p',
        'A',
        $lang_code,
        $condition
    );

    return $pecom_id;
}