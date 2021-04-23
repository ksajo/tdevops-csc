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
use Tygh\Shippings\BoxberryClient;
use Tygh\Languages\Values as LanguageValues;

use Boxberry\Models\Parsel;
use Boxberry\Models\Customer;
use Boxberry\Models\Item;
use Boxberry\Models\CourierDelivery;
use Boxberry\Collections\Items;
use Tygh\Template\Document\Variables\PickpupPointVariable;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Adds the Boxberry shipping services to the list
 */
function fn_rus_boxberry_install()
{
    $services = array(
        array(
            'status' => 'A',
            'module' => 'rus_boxberry',
            'code' => 'boxberry_self',
            'sp_file' => '',
            'description_code' => 'rus_boxberry.boxberry_self',
        ),
        array(
            'status' => 'A',
            'module' => 'rus_boxberry',
            'code' => 'boxberry_self_prepaid',
            'sp_file' => '',
            'description_code' => 'rus_boxberry.boxberry_self_prepaid',
        ),
        array(
            'status' => 'A',
            'module' => 'rus_boxberry',
            'code' => 'boxberry_courier',
            'sp_file' => '',
            'description_code' => 'rus_boxberry.boxberry_courier',
        ),
        array(
            'status' => 'A',
            'module' => 'rus_boxberry',
            'code' => 'boxberry_courier_prepaid',
            'sp_file' => '',
            'description_code' => 'rus_boxberry.boxberry_courier_prepaid',
        ),
    );

    foreach ($services as $service) {
        $service_id = db_get_field('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s', $service['module'], $service['code']);
        if (empty($service_id)) {
            $service_id = db_replace_into('shipping_services', $service);

            foreach (Languages::getAll() as $lang_code => $lang_data) {
                $description = LanguageValues::getLangVar($service['description_code'], $lang_code);
                $data = array(
                    'service_id' => $service_id,
                    'description' => $description,
                    'lang_code' => $lang_code
                );

                db_replace_into('shipping_service_descriptions', $data);
            }
        }
    }
}

/**
 * Removes the Boxberry shipping services from the list
 */
function fn_rus_boxberry_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'rus_boxberry');

    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
}

/**
 * A hook for adding the information about Boxberry pickup point
 *
 * @param array $cart Array of cart content and user information necessary for placing an order
 * @param array $allow
 * @param array $product_groups Products grouped by packages, suppliers, vendors
 */
function fn_rus_boxberry_pre_place_order(&$cart, $allow, $product_groups)
{
    foreach($cart['product_groups'] as $group_key => &$group) {
        if (!empty($group['chosen_shippings']) && !empty($cart['shippings_extra']['boxberry'])) {
            $boxberry_extra = $cart['shippings_extra']['boxberry'][$group_key];

            foreach($group['chosen_shippings'] as &$shipping) {
                if ($shipping['module'] == 'rus_boxberry') {
                    $shiping_id = $shipping['shipping_id'];

                    if (!empty($boxberry_extra[$shiping_id]['point_id'])) {
                        $shipping['point_id'] = $boxberry_extra[$shiping_id]['point_id'];
                        $shipping['pickup_data'] = $boxberry_extra[$shiping_id]['pickup_data'];
                    }
                }
            }
        }
    }
}

/**
 * A hook for creating Boxberry parcel after the creation of a shipment in CS-Cart
 *
 * @param array $shipment_data
 * @param array $order_info
 * @param array $group_key
 */
function fn_rus_boxberry_create_shipment(&$shipment_data, $order_info, $group_key)
{
    if ($shipment_data['carrier'] == 'rus_boxberry') {
        $tracking_number = fn_rus_boxberry_create_parsel($shipment_data, $order_info, $group_key);

        if (!empty($tracking_number)) {
            $shipment_data['tracking_number'] = $tracking_number;
        } else {
            $shipment_data['products'] = array();
        }
    }
}

/**
 * A hook for selecting additional fields in the SQL query
 *
 * @param array $params      Search parameters for shipments
 * @param array $fields_list Array of fields to be selected
 */
function fn_rus_boxberry_get_shipments($params, &$fields_list)
{
    $fields_list[] = '?:shipments.carrier';
    $fields_list[] = '?:shipments.shipping_id';
    $fields_list[] = '?:shipments.tracking_number';
}

/**
 * A hook for adding links to Boxberry labels to shipments
 *
 * @param array $shipments Array of shipments
 */
function fn_rus_boxberry_get_shipments_info_post(&$shipments)
{
    if (Registry::get('runtime.controller') == 'shipments') {
        foreach ($shipments as &$shipment_data) {
            if ($shipment_data['carrier'] == 'rus_boxberry') {

                $service_params = fn_get_shipping_params($shipment_data['shipping_id']);
                $client = new BoxberryClient($service_params);

                $shipment_data['boxberry_label'] = $client->getLabel($shipment_data['tracking_number']);
            }
        }
    }
}

/**
 * Creates a Boxberry parcel
 *
 * @param array $shipment_data
 * @param array $order_info
 * @param array $group_key
 *
 * @return string Tracking number of the Boxberry parcel
 */
function fn_rus_boxberry_create_parsel($shipment_data, $order_info, $group_key)
{
    if ($shipment_data['carrier'] != 'rus_boxberry') {
        return false;
    }

    $tracking_number = 0;

    $product_group = $order_info['product_groups'][$group_key];
    $chosen_shipping = reset($product_group['chosen_shippings']);

    if ($chosen_shipping['module'] == 'rus_boxberry') {
        $client = new BoxberryClient();

        $service_params = db_get_field('SELECT service_params FROM ?:shippings WHERE shipping_id = ?i', $chosen_shipping['shipping_id']);
        $service_params = unserialize($service_params);
        $default_weight = $service_params['default_weight'];

        $client->setKey($service_params['password']);

        $parsel = new Parsel();
        $parsel->setOrderId($order_info['order_id']);

        $items = new Items();
        $total = $order_info['shipping_cost'];
        foreach ($shipment_data['products'] as $key => $amount) {

            if (empty($amount)) {
                continue;
            }

            $product = $order_info['products'][$key];
            $total += $product['price'] * $amount;

            $item = new Item();
            $item->setId($product['product_id']);
            $item->setName($product['product']);
            $item->setPrice($product['price']);
            $item->setQuantity($amount);

            $weight = db_get_field('SELECT weight FROM ?:products WHERE product_id = ?i', $product['product_id']);
            $weight = fn_apply_options_modifiers(
                $product['extra']['product_options'], $weight, 'W', array(), array('product_data' => $product)
            );

            $weight = $weight * Registry::get('settings.General.weight_symbol_grams') / 1000;
            $weight = sprintf('%.3f', round((double) $weight + 0.00000000001, 3));

            if (empty($weight) || $weight === "0.000" && !empty($default_weight)){
                $item->setWeight($default_weight * $amount);
            } else {
                $item->setWeight($weight * 1000 * $amount);
            }

            $items[] = $item;
        }

        $parsel->setWeights(array('weight' => 0));
        $parsel->setItems($items);

        if ($chosen_shipping['service_code'] == 'boxberry_self_prepaid' || $chosen_shipping['service_code'] == 'boxberry_courier_prepaid') {
            $parsel->setPaymentSum(0);
        } else {
            $parsel->setPaymentSum($total);
        }

        $parsel->setDeliverySum($order_info['shipping_cost']);

        $customer = new Customer();
        $customer->setPhone($order_info['phone']);
        $customer->setFio($order_info['firstname'] . ' ' . $order_info['lastname']);
        $customer->setEmail($order_info['email']);
        $customer->setAddress($order_info['b_address']);
        $parsel->setCustomer($customer);

        $shop = array(
            'name' => '',
            'name1' => isset($service_params['boxberry_target_start']) ? $service_params['boxberry_target_start'] : ''
        );
        if ($chosen_shipping['service_code'] == 'boxberry_courier' || $chosen_shipping['service_code'] == 'boxberry_courier_prepaid') {
            $parsel->setVid(2);
            $courier_dost = new CourierDelivery();
            $courier_dost->setIndex($product_group['package_info_full']['location']['zipcode']);
            $courier_dost->setCity($product_group['package_info_full']['location']['city']);
            $courier_dost->setAddressp($product_group['package_info_full']['location']['address']);
            $parsel->setCourierDelivery($courier_dost);
        } else {
            $parsel->setVid(1);
            $shop['name'] = $chosen_shipping['point_id'];
        }

        $parsel->setShop($shop);

        $tracking_number = $client->createParcel($parsel);
    }

    return $tracking_number;
}

/**
 * Hook handler: injects pickup point into order data.
 */
function fn_rus_boxberry_pickup_point_variable_init(
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

        if (!isset($shipping['module']) || $shipping['module'] !== 'rus_boxberry') {
            return;
        }

        if (isset($shipping['pickup_data'])) {
            $pickup_data = $shipping['pickup_data'];

            $is_selected = true;
            $name = $pickup_data['type'] . ' ' . $pickup_data['address'];
            $phone = $pickup_data['phone'];
            $full_address = $pickup_data['full_address'];
            $description_raw = $description = $pickup_data['trip_description'];
        }
    }

    return;
}

/**
 * The "rus_cities_location_manager_detect_zipcode_post_post" hook handler.
 *
 * Action performed:
 * - Modifies the detected zipcode for skipping Central Post Office codes, which ends on 2 or 3 zeros.
 *
 * @param string                $country_code ISO 3166-1 country code
 * @param string                $state_code   ISO 3166-2 state code
 * @param array<string, string> $city         City information
 * @param string                $zipcode      Detected zipcode
 *
 * @see fn_rus_cities_location_manager_detect_zipcode_post()
 */
function fn_rus_boxberry_rus_cities_location_manager_detect_zipcode_post_post($country_code, $state_code, array $city, &$zipcode)
{
    if (!$city) {
        return;
    }
    $city_postal_codes = explode(',', $city['zipcode']);
    foreach ($city_postal_codes as $postal_code) {
        if (preg_match('/00$/', $postal_code)) {
            continue;
        }
        $zipcode = $postal_code;
        break;
    }
}

/**
 * The "rus_cities_geo_maps_set_customer_location_pre_post" hook handler.
 *
 * Action performed:
 *  - Modifies the detected location to skip Central Post Office zip codes.
 *
 * @param array<string, string>        $location Customer location data
 * @param array<array<string, string>> $cities   List of available cities and their data
 *
 * @see fn_rus_cities_geo_maps_set_customer_location_pre()
 */
function fn_rus_boxberry_rus_cities_geo_maps_set_customer_location_pre_post(&$location, $cities)
{
    if (empty($cities)) {
        return;
    }
    $city = reset($cities);
    $city_postal_codes = explode(',', $city['zipcode']);
    foreach ($city_postal_codes as $postal_code) {
        if (preg_match('/00$/', $postal_code)) {
            continue;
        }
        $location['postal_code'] = $postal_code;
        break;
    }
}

/**
 * Hook handler: sets cart 'calculate_shipping' param according to selected point.
 */
function fn_rus_boxberry_checkout_update_steps_before_update_user_data(&$cart, $auth, $params, $user_id, $user_data)
{
    $cart['calculate_shipping'] = !empty($params['boxberry_selected_point']);
}
