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

function fn_rus_russianpost_install()
{
    $objects = fn_rus_russianpost_schema();

    if (fn_allowed_for('ULTIMATE')) {
        $company_id = fn_get_default_company_id();
    } else {
        $company_id = 0;
    }

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

        $shipping = array(
            'company_id' => $company_id,
            'destination' => 'I',
            'min_weight' => 0.00,
            'usergroup_ids' => '0',
            'rate_calculation' => 'R',
            'service_id' => $service['service_id'],
            'localization' => '',
            'tax_ids' => '',
            'status' => 'A',
            'delivery_time' => '',
        );

        if ($object['code'] === 'russian_pochta') {
            $unique_params = array(
                'max_weight' => 20.00,
                'service_params' => array(
                    'object_type' => RUSSIANPOST_OBJECT_TYPE_NON_STANDARD_PARCEL,
                    'isavia' => '0',
                    'cash_on_delivery' => '',
                    'average_quantity_in_packet' => '',
                    'services' => array(
                        'delivery_notice' => 'N',
                        'registered_notice' => 'N',
                        'inventory' => 'N',
                        'careful' => 'N',
                        'ponderous_parcel' => 'N',
                        'delivery_courier' => 'N',
                        'delivery_product' => 'N',
                        'oversize' => 'N',
                        'insurance' => 'N',
                        'cash_sender' => 'N',
                        'sms_receipt' => 'N',
                        'sms_delivery' => 'N',
                        'check_investment' => 'N',
                        'compliance_investment' => 'N',
                        'delivery_by_hand' => 'N',
                    ),
                    'api_login' => '',
                    'api_password' => ''
                ),
                'position' => 50,
                'shipping' => $objects['pochta']['description']
            );

            $shipping = array_merge($shipping, $unique_params);
            fn_update_shipping($shipping, 0);
        }
    }
}

function fn_rus_russianpost_uninstall()
{
    $objects = fn_rus_russianpost_schema();

    foreach ($objects as $object) {
        $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', $object['module']);

        if (!empty($service_ids)) {
            db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
            db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);

            array_map(function ($service_id) {
                $shipping_id = db_get_field('SELECT shipping_id FROM ?:shippings WHERE service_id = ?i', $service_id);
                fn_delete_shipping($shipping_id);
            }, $service_ids);
        }
    }

    db_query('DROP TABLE IF EXISTS ?:rus_russianpost_status');
}

function fn_rus_russianpost_schema()
{
    return array(
        'postcalc' => array(
            'status' => 'A',
            'module' => 'russian_post',
            'code' => 'russian_post_calc',
            'sp_file' => '',
            'description' => 'Тарифный калькулятор (russianpostcalc.ru)'
        ),
        'pochta' => array(
            'status' => 'A',
            'module' => 'russian_post',
            'code' => 'russian_pochta',
            'sp_file' => '',
            'description' => 'Почта России (pochta.ru)'
        ),
    );
}

function fn_rus_russianpost_get_shipping_service($module)
{
    $service = db_get_row('SELECT * FROM ?:shipping_services WHERE `module` = ?s', $module);

    return $service;
}

function fn_rus_postblank_rub_kop_price($total)
{
    $rub = '0';
    $kop = '00';
    if (is_numeric($total)) {
        $total_array = explode('.', $total);
        $rub = reset($total_array);

        if (!empty($total_array[1])) {
            $kop = $total_array[1];
        } else {
            $total = (float) $rub . '.' . $kop;
        }
    } else {
        $total = 0;
    }

    return array($total, $rub, $kop);
}

/**
 * Filters out services that are not available for specified sending object (type)
 *
 * @param integer $object_id Sending object id
 *
 * @return array
 */
function fn_rus_russianpost_get_shipping_services_by_sending_object($object_id)
{
    $services = fn_get_schema('russianpost', 'sending_services', 'php', true);
    $services_mapping = fn_get_schema('russianpost', 'objects_to_services_mapping', 'php', true);

    if (isset($services_mapping[$object_id])) {

        foreach ($services as $service_id => $item) {

            if (!in_array($service_id, $services_mapping[$object_id])) {
                unset($services[$service_id]);
            }
        }
    }

    return $services;
}

/**
 * Fetches selected sending object id from shipping method's params, or picks the first id
 * from all available objects array
 *
 * @param array $shipping Shipping parameters
 * @param array $sending_objects Sending objects list
 *
 * @return int
 */
function fn_rus_russianpost_get_selected_object(array $shipping, array $sending_objects)
{
    $selected_object_id = 0;

    if (!empty($shipping['service_params']['object_type'])) {
        $selected_object_id = $shipping['service_params']['object_type'];

    } elseif (!empty($sending_objects['sending_object'])
        && is_array($sending_objects['sending_object'])
    ) {
        $first_section = reset($sending_objects['sending_object']);

        if (!empty($first_section['variants'])) {
            reset($first_section['variants']);
            $selected_object_id = key($first_section['variants']);
        }

    } elseif (!empty($sending_objects)) {
        $first_section = reset($sending_objects);

        if (!empty($first_section['variants'])) {
            reset($first_section['variants']);
            $selected_object_id = key($first_section['variants']);
        }
    }

    return $selected_object_id;
}
