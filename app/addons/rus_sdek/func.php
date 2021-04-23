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

use Tygh\Addons\RusTaxes\Receipt\Item as ReceiptItem;
use Tygh\Addons\RusTaxes\TaxType;
use Tygh\Languages\Languages;
use Tygh\Navigation\LastView;
use Tygh\Registry;
use Tygh\Shippings\Shippings;
use Tygh\Template\Document\Variables\PickpupPointVariable;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_sdek_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'sdek',
        'code' => '1',
        'sp_file' => '',
        'description' => 'СДЭК',
    );
    
    $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }

    $path = Registry::get('config.dir.root') . '/app/addons/rus_sdek/database/cities_sdek.csv';
    fn_rus_cities_read_cities_by_chunk($path, RUS_CITIES_FILE_READ_CHUNK_SIZE, 'fn_rus_sdek_add_cities_in_table');
}

function fn_rus_sdek_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'sdek');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
}

function fn_rus_sdek_update_cart_by_data_post(&$cart, $new_cart_data, $auth)
{
    if (!empty($new_cart_data['select_office'])) {
        $cart['select_office'] = $new_cart_data['select_office'];
    }
}

function fn_rus_sdek_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{

    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['select_office'])) {
            $select_office = $cart['select_office'];

        } elseif (!empty($_REQUEST['select_office'])) {
            $select_office = $cart['select_office'] = $_REQUEST['select_office'];
        }

        if (!empty($select_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'sdek') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                            if (!empty($select_office[$group_key][$shipping_id])) {
                                $office_id = $select_office[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_id'] = $office_id;

                                if (!empty($shippings_extra['offices'][$office_id])) {
                                    $office_data = $shippings_extra['offices'][$office_id];
                                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_data'] = $office_data;
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

                        if ($module == 'sdek' && !empty($shippings_extra)) {
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

                    if ($module == 'sdek' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

/**
 * Calculates shipping costs for SDEK.
 *
 * @param array order_info The array with the order data.
 * @param array shipping_info The array with the shipping method data.
 * @param array shipment_info The array with the shipment data.
 *
 * @return The calculated shipping cost.
 */
function fn_sdek_calculate_cost_by_shipment($order_info, $shipping_info, $shipment_info)
{
    $total = $weight = 0;
    $length = $width = $height = SDEK_DEFAULT_DIMENSIONS;
    $sum_rate = 0;

    $shipping_info['module'] = $shipment_info['carrier'];

    $symbol_grams = Registry::get('settings.General.weight_symbol_grams');

    foreach ($shipment_info['products'] as $item_id => $amount) {
        $product = $order_info['products'][$item_id];

        $total += $product['subtotal'];

        $product_extra = db_get_row("SELECT shipping_params, weight FROM ?:products WHERE product_id = ?i", $product['product_id']);

        $product_weight = fn_sdek_check_weight($product_extra['weight'], $symbol_grams);

        $p_ship_params = unserialize($product_extra['shipping_params']);

        $package_length = empty($p_ship_params['box_length']) ? $length : $p_ship_params['box_length'];
        $package_width = empty($p_ship_params['box_width']) ? $width : $p_ship_params['box_width'];
        $package_height = empty($p_ship_params['box_height']) ? $height : $p_ship_params['box_height'];
        $weight_ar = fn_convert_weight_to_imperial_units($product_weight);
        $weight = $weight_ar['plain'];

        $params_product['weight'] = $weight;
        $params_product['length'] = $package_length;
        $params_product['width'] = $package_width;
        $params_product['height'] = $package_height;

        foreach ($order_info['product_groups'] as $product_groups) {
            if (!empty($product_groups['products'][$item_id])) {
                $products[$item_id] = $product_groups['products'][$item_id];
                $products[$item_id] = array_merge($products[$item_id], $params_product);
                $products[$item_id]['amount'] = $amount;
            }

            $shipping_info['package_info'] = $product_groups['package_info'];
        }
    }

    $data_package = Shippings::groupProductsList($products, $shipping_info['package_info']['location']);
    $data_package = reset($data_package);
    $shipping_info['package_info_full'] = $data_package['package_info_full'];
    $shipping_info['package_info'] = $data_package['package_info_full'];

    $sum_rate = Shippings::calculateRates(array($shipping_info));
    $sum_rate = reset($sum_rate);
    $result = $sum_rate['price'];

    return $result;
}

function fn_sdek_get_name_customer($order_info)
{
    $firstname = $lastname = "";

    if (!empty($order_info['lastname'])) {
        $lastname = $order_info['lastname'];

    } elseif (!empty($order_info['s_lastname'])) {
        $lastname = $order_info['s_lastname'];

    } elseif (!empty($order_info['b_lastname'])) {
        $lastname = $order_info['b_lastname'];
    }

    if (!empty($order_info['firstname'])) {
        $firstname = $order_info['firstname'];

    } elseif (!empty($order_info['s_firstname'])) {
        $firstname = $order_info['s_firstname'];

    } elseif (!empty($order_info['b_firstname'])) {
        $firstname = $order_info['b_firstname'];
    }

    return $lastname . ' ' . $firstname;
}

function fn_sdek_get_phone_customer($order_info)
{
    $phone = '-';

    if (!empty($order_info['phone'])) {
        $phone = $order_info['phone'];

    } elseif (!empty($order_info['s_phone'])) {
        $phone = $order_info['s_phone'];

    } elseif (!empty($order_info['b_phone'])) {
        $phone = $order_info['b_phone'];
    }

    if (empty($phone)) {
        $phone = '-';
    }

    return $phone;
}

function fn_sdek_get_data_auth($data_auth, $b_country, $s_country, $currency_sdek)
{
    if ($b_country != 'RU' && $s_country != 'RU') {
        $data_auth['ForeignDelivery'] = 1;

        if (!empty($currency_sdek[$s_country])) {
            $data_auth['Currency'] = $currency_sdek[$s_country];

        } elseif (!empty($currency_sdek[$b_country])) {
            $data_auth['Currency'] = $currency_sdek[$b_country];

        } else {
            $data_auth['Currency'] = CART_PRIMARY_CURRENCY;
        }
    }

    return $data_auth;
}

function fn_sdek_get_product_data($sdek_products, $data_product, $order_info, $shipment_id, $amount, $symbol_grams, ReceiptItem $receipt_item)
{
    $ware_key = (!empty($data_product['product_code'])) ? $data_product['product_code'] : $data_product['product_id'];
    $sdek_product = array(
        'ware_key' => $ware_key,
        'order_id' => $order_info['order_id'],
        'product' => $data_product['product'],
        'amount' => $amount,
        'shipment_id' => $shipment_id
    );

    $product_weight = db_get_field("SELECT weight FROM ?:products WHERE product_id = ?i", $data_product['product_id']);

    if (!empty($data_product['product_options'])) {
        $product_options = array();
        foreach($data_product['product_options'] as $_options) {
            $product_options[$_options['option_id']] = $_options['value'];
        }

        $product_weight = fn_apply_options_modifiers($product_options, $product_weight, 'W');
    }

    $product_weight = fn_sdek_check_weight($product_weight, $symbol_grams);

    $sdek_product['weight'] = $product_weight;

    if ($receipt_item) {
        $sdek_product['price'] = $receipt_item->getPrice();
        $sdek_product['total'] = $receipt_item->getTotal();
    } elseif (!empty($data_product['price']) && $data_product['price'] != 0) {
        $sdek_product['price'] = $data_product['price'] - ($data_product['price'] / $order_info['subtotal'] * $order_info['subtotal_discount']);
        $sdek_product['total'] = $sdek_product['price'] * $amount;
    }

    if (!empty($sdek_products[$ware_key]) && ($sdek_product['price'] != $sdek_products[$ware_key]['price'])) {
        $ware_key = (!empty($data_product['item_id'])) ? $data_product['item_id'] : $data_product['product_id'];
        $sdek_product['ware_key'] = $ware_key;
    }

    if (!empty($sdek_products[$ware_key])) {
        $sdek_products[$ware_key]['amount'] += $sdek_product['amount'];
        $sdek_products[$ware_key]['price'] = $sdek_product['price'];
        $sdek_products[$ware_key]['total'] += $sdek_product['total'];
        $sdek_products[$ware_key]['weight'] += $sdek_product['weight'];
    } else {
        $sdek_products[$ware_key] = $sdek_product;
    }

    if (empty($sdek_products[$ware_key]['price'])){
        $sdek_products[$ware_key]['price'] = "0.00";
    }

    if (empty($sdek_products[$ware_key]['total'])) {
        $sdek_products[$ware_key]['total'] = "0.00";
    }

    if ($receipt_item) {
        $tax_code = $receipt_item->getTaxType();
        $tax_sum = $receipt_item->getTaxSum();
    } else {
        $tax_code = TaxType::NONE;
        $tax_sum = 0;
    }

    $sdek_products[$ware_key]['tax'] = fn_sdek_normalize_tax_type($tax_code);
    $sdek_products[$ware_key]['tax_sum'] = $tax_sum;

    return array($sdek_products, $product_weight);
}


function fn_sdek_get_price_by_currency($price, $data_auth, $currencies, $default_currency)
{
    if (!empty($data_auth['Currency'])) {
        if (!empty($currencies[$data_auth['Currency']])) {
            $price = fn_format_price_by_currency($price, $data_auth['Currency'], $default_currency);
        }
    }

    if ($price == 0) {
        $price = '0.00';
    }

    return $price;
}

function fn_sdek_get_data_product_xml($product, $sdek_info)
{
    $payment = '0.00';
    if (!empty($sdek_info['use_imposed']) && $sdek_info['use_imposed'] == 'Y') {
        $payment = (!empty($sdek_info['CashDelivery'])) ? $sdek_info['CashDelivery'] : '0.00';

        if (!empty($sdek_info['use_product']) && $sdek_info['use_product'] == 'Y') {
            $payment += $product['price'];
        }
    }

    $product_for_xml = array (
        'WareKey' => $product['ware_key'],
        'Cost' => number_format($product['price'], 2, '.', ''),
        'Payment' => number_format($payment, 2, '.', ''),
        'Amount' => $product['amount'],
        'Comment' => $product['product']
    );

    $product_for_xml['PaymentVATSum'] = number_format(fn_sdek_calculate_tax_sum($product['tax'], $payment), 2, '.', '');
    $product_for_xml['PaymentVATRate'] = $product['tax'];

    return $product_for_xml;
}

function fn_rus_sdek_calculate_cart_items(&$cart, &$cart_products, &$auth)
{
    foreach ($cart_products as &$product) {
        if ($product['weight'] == 0) {
            $product['weight'] = round(100 / Registry::get('settings.General.weight_symbol_grams'), 3);
        }
    }
}

/**
 * Checks if the data about shipment statuses exists.
 *
 * @return boolean  Returns true if the data about shipment statuses exists; returns
 *                  false otherwise
 */
function fn_sdek_delivery_check_orders()
{
    $status_id = db_get_field("SELECT status_id FROM ?:rus_sdek_history_status LIMIT 1");

    return !empty($status_id);
}

/**
 * Gets the data of specific shipment statuses; the passed parameters determine,
 * the data of which statuses will be returned.
 *
 * @param array  $params          The parameters by which the shipment statuses
 *                                will be selected
 * @param int    $items_per_page  The maximum number of statuses to appear on one page
 * @param string $lang_code       The language code
 *
 * array  Returns an array of shipment statuses, and the parameters of the selected
 *        statuses
 */
function fn_rus_sdek_get_status($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $condition = 'WHERE 1 ';
    $_view = 'sdek_status';
    $params = LastView::instance()->update($_view, $params);

    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    if (!empty($params['time_from'])) {
        $condition .= db_quote(' AND timestamp >= ?i', strtotime($params['time_from']));

        if (!empty($params['time_to'])) {
            $condition .= db_quote(' AND timestamp < ?i', strtotime($params['time_to']));
        }
    }

    if (!empty($params['sdek_order_id'])) {
        $condition .= db_quote(" AND order_id = ?i ", $params['sdek_order_id']);
    }

    $join = db_quote(' LEFT JOIN ?:rus_sdek_cities_link as b ON a.city_code = b.sdek_city_code');
    $join .= db_quote(' LEFT JOIN ?:rus_city_descriptions as c ON b.city_id = c.city_id AND c.lang_code = ?s', $lang_code);

    $sort_by = !empty($params['sort_by']) ? $params['sort_by'] : 'order_id';
    $sort = 'asc';
    if (!empty($params['sort_order'])) {
        $sort = $params['sort_order'];
        $params['sort_order'] = ($params['sort_order'] == 'asc') ? 'desc' : 'asc';
        $params['sort_order_rev'] = $params['sort_order'];
    } else {
        $params['sort_order'] = 'asc';
        $params['sort_order_rev'] = 'asc';
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field('SELECT COUNT(*) FROM ?:rus_sdek_history_status ?p', $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data_status = db_get_array(
        'SELECT a.*, c.city FROM ?:rus_sdek_history_status as a ?p ?p ORDER BY ?p ?p ?p',
        $join, $condition, $sort_by, $sort, $limit
    );

    return array($data_status, $params);
}

/**
 * Sets the weight of a product/order to 100 grams in the specified weight measurement unit
 * when the product/order doesn't have weight.
 *
 * @param float|int  $weight        The weight of a product or order.
 * @param float|int  $symbol_grams  Grams in the unit of weight defined by the weight symbol.
 *
 * @return float|int The weight of a product or order.
 */
function fn_sdek_check_weight($weight, $symbol_grams)
{
    if (empty($weight) || $weight == 0) {
        $weight = (100 / $symbol_grams);
    }

    return $weight;
}

/**
 * Normalizes tax type for sdek service using.
 *
 * @param string $tax_type
 *
 * @return string
 */
function fn_sdek_normalize_tax_type($tax_type)
{
    $map = [
        TaxType::NONE    => 'vatx',
        TaxType::VAT_110 => 'vat10',
        TaxType::VAT_118 => 'vat18',
        TaxType::VAT_120 => 'vat20',
    ];

    $tax_type = isset($map[$tax_type]) ? $map[$tax_type] : $tax_type;

    return strtoupper($tax_type);
}

/**
 * Calculates tax sum from price.
 *
 * @param string    $tax_type   Tax type (vat0, vat10, vat18)
 * @param float     $price      Price
 *
 * @return float
 */
function fn_sdek_calculate_tax_sum($tax_type, $price)
{
    $tax_type = strtolower($tax_type);

    switch ($tax_type) {
        case TaxType::VAT_10:
            $result = $price * 10 / 110;
            break;
        case TaxType::VAT_18:
            $result = $price * 18 / 118;
            break;
        case TaxType::VAT_20:
            $result = $price * 20 / 120;
            break;
        default:
            $result = 0;
            break;
    }

    return round($result, 2);
}

/**
 * Hook handler: on after getting cities list. Changes the cities data.
 */
function fn_rus_sdek_get_cities_post($params, $items_per_page, $lang_code, &$cities)
{
    $cities_ids = array_column($cities, 'city_id');

    if ($cities_ids) {
        $sdek_ids = db_get_hash_multi_array(
            'SELECT city_id, sdek_city_code FROM ?:rus_sdek_cities_link WHERE city_id IN (?a)',
            array('city_id', 'sdek_city_code', 'sdek_city_code'),
            $cities_ids
        );
    }

    if (isset($sdek_ids)) {
        foreach ($cities as &$city_data) {
            $city_data['sdek_city_code'] = empty($sdek_ids[$city_data['city_id']]) ? '' : implode(',',
                $sdek_ids[$city_data['city_id']]);
        }
    }
}

/**
 * Hook handler: on after update cities. The updates of city for the sdek table.
 */
function fn_rus_sdek_update_city_post($city_data, $city_id, $lang_code)
{
    if (empty($city_data['sdek_city_code'])) {
        return false;
    }

    if (!empty($city_data['sdek_city_code_old']) && $city_data['sdek_city_code_old'] == $city_data['sdek_city_code']) {
        return false;
    }

    $sdek_city_code = explode(',', $city_data['sdek_city_code']);

    if (count($sdek_city_code) > 1) {
        foreach ($sdek_city_code as $sdek_code) {
            $sdek_link = array(
                'city_id' => $city_id,
                'sdek_city_code' => $sdek_code,
                'zipcode' => $city_data['sdek_city_zipcode']
            );

            db_replace_into('rus_sdek_cities_link', $sdek_link);
        }

    } else {
        $sdek_link = array(
            'city_id' => $city_id,
            'sdek_city_code' => $city_data['sdek_city_code'],
            'zipcode' => $city_data['sdek_city_zipcode']
        );

        db_replace_into('rus_sdek_cities_link', $sdek_link);
    }
}

/**
 * Checks the availability of the state code.
 *
 * @param string $state      The state code.
 * @param string $country    The country code.
 * @param bool   $avail_only If set to true - gets only enabled states.
 *
 * @return bool If the availability of the state code - true, else - false.
 */
function fn_rus_sdek_check_state_code($state, $country = '', $avail_only = true)
{
    $condition = '';

    if (!empty($country)) {
        $condition .= db_quote(' AND country_code = ?s', $country);
    }

    if ($avail_only == true) {
        $condition .= db_quote(' AND status = ?s', 'A');
    }

    $state = db_get_field(
        'SELECT code FROM ?:states WHERE code = ?s ?p',
        $state,
        $condition
    );

    if (empty($state)) {
        return false;
    }

    return true;
}

/**
 * Gets the sdek data.
 *
 * @param int[] $city_ids The cities identificator.
 *
 * @return array The array sdek data.
 */
function fn_rus_sdek_get_sdek_data($city_ids)
{
    $sdek_data = db_get_array(
        'SELECT city_id, sdek_city_code, zipcode FROM ?:rus_sdek_cities_link WHERE city_id IN (?a)',
        $city_ids
    );

    return $sdek_data;
}

/**
 * Adds the cities data in the table.
 *
 * @param array $rows The array with cities data.
 *
 * @return void.
 */
function fn_rus_sdek_add_cities_in_table($rows)
{
    $cities_hash = fn_rus_cities_get_all_cities($rows);

    foreach ($rows as $city_data) {
        $city_data['City'] = (string) trim($city_data['City']);
        $city_data['City'] = fn_strtolower($city_data['City']);

        if (!empty($cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']])) {
            $city_id = $cities_hash[$city_data['Country']][$city_data['OblName']][$city_data['City']];

            $zipcode = $city_data['PostCodeList'];
            if (!empty($city_data['PostCodeList']) && fn_strlen($city_data['PostCodeList']) <= 1) {
                $zipcode = str_pad($city_data['PostCodeList'], 6, '0', STR_PAD_LEFT);
            }

            $city = array(
                'city_id' => $city_id,
                'sdek_city_code' => $city_data['ID'],
                'zipcode' => $zipcode
            );

            db_replace_into('rus_sdek_cities_link', $city);
        }
    }
}

/**
 * Hook handler: on before delete cities. The deletes of city in the sdek table.
 */
function fn_rus_sdek_delete_city_post($city_id)
{
    db_query('DELETE FROM ?:rus_sdek_cities_link WHERE city_id = ?i', $city_id);
}

/**
 * Hook handler: sets pickup point data.
 */
function fn_rus_sdek_pickup_point_variable_init(
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

        if (!isset($shipping['module']) || $shipping['module'] !== 'sdek') {
            return;
        }

        if (isset($shipping['office_data'])) {
            $pickup_data = $shipping['office_data'];
            
            $is_selected = true;
            $name = $pickup_data['Name'];
            $full_address = $pickup_data['FullAddress'];
            $phone = $pickup_data['Phone'];
            $open_hours = $pickup_data['WorkTime'];
            $open_hours_raw = [$open_hours];
            $description_raw = $description = $pickup_data['AddressComment'];
        }
    }

    return;
}

