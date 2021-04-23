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

namespace Tygh\Shippings\YandexDelivery;

use Tygh\Registry;

class YandexDelivery
{
    public static $version = "1.0";

    public static function init($shipping_id = null, $config = null)
    {
        static $api = null;

        if ($api == null) {
            if ($config == null) {

                $config = new Config();
                $addon_info = Registry::get('addons.yandex_delivery');

                self::getApiKeys($addon_info, $config);
                self::getIds($addon_info, $config);

                $config->api_url = "https://delivery.yandex.ru/api/" . self::$version . "/";
                $config->format = 'json';
                if (isset($config->ids['client'])) {
                    $config->client_id = !empty($config->ids['client']['id']) ? $config->ids['client']['id'] : 0;
                }

                if (!empty($shipping_id)) {
                    $shipping = fn_get_shipping_params($shipping_id);
                    $config->sender_id = isset($shipping['sender_id']) ? $shipping['sender_id'] : 0;
                    $config->warehouse_id = isset($shipping['warehouse_id']) ? $shipping['warehouse_id'] : 0;
                    $config->requisite_id = isset($shipping['requisite_id']) ? $shipping['requisite_id'] : 0;
                    $config->is_logging_enabled = isset($shipping['logging']) && $shipping['logging'] == 'Y';

                } else {
                    if (!empty($config->ids['senders'])) {
                        $sender = reset($config->ids['senders']);
                        $config->sender_id = !empty($sender['id']) ? $sender['id'] : 0;
                    }

                    if (!empty($config->ids['warehouses'])) {
                        $warehouse = reset($config->ids['warehouses']);
                        $config->warehouse_id = !empty($warehouse['id']) ? $warehouse['id'] : 0;
                    }

                    if (!empty($config->ids['requisites'])) {
                        $requisite = reset($config->ids['requisites']);
                        $config->requisite_id = !empty($requisite['id']) ? $requisite['id'] : 0;
                    }
                }

                if (empty($config->sender_id) && is_array($config->ids['senders'])) {
                    $sender = reset($config->ids['senders']);
                    $config->sender_id = !empty($sender['id']) ? $sender['id'] : 0;
                }

                $config->available_city_from = fn_get_schema('yandex_delivery', 'city_from');
            }

            $api = new Api($config);
        }

        return $api;
    }

    public static function getSizePackage($package_info, $shipping_settings)
    {
        $package_size = array(
            'length' => 0,
            'width' => 0,
            'height' => 0,
        );

        if (!empty($package_info['packages'])) {

            $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 0;
            $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 0;
            $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 0;

            $box_data = array();
            foreach ($package_info['packages'] as $package) {
                $box_data[] = array(
                    empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'],
                    empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'],
                    empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height']
                );
            }

            $sort_box_data = array();
            foreach ($box_data as $box) {
                arsort($box);
                $sort_box_data[] = array_values($box);
            }

            $lenght_data = array();
            $width_data = array();
            $height_data = array();
            foreach ($sort_box_data as $box) {
                $lenght_data[] = $box[0];
                $width_data[] = $box[1];
                $height_data[] = $box[2];
            }

            $package_size = array(
                'length' => max($lenght_data),
                'width' => max($width_data),
                'height' => array_sum($height_data),
            );
        }

        return $package_size;
    }


    protected static function getApiKeys($addon_info, &$config)
    {
        if ($client = json_decode($addon_info['api_keys'], true)) {
            $config->keys = $client;
        } else {
            $api_keys = explode(PHP_EOL, $addon_info['api_keys']);

            foreach ($api_keys as $value) {
                if (strpos($value, ':') !== false) {
                    $data = explode(':' , $value);
                    $config->keys[trim($data[0])] = trim($data[1]);
                }
            }
        }
    }

    protected static function getIds($addon_info, &$config)
    {
        if ($client = json_decode($addon_info['client_ids'], true)) {
            $config->ids = $client;

        } else {
            $pattern_elements = "/(\".+?\"):([0-9]+|\[.*?\])/";
            preg_match_all($pattern_elements, $addon_info['client_ids'], $elements);

            $config_ids = array(
                'client_id' => 0,
                'warehouse_ids' => array(),
                'sender_ids' => array(),
                'requisite_ids' => array()
            );

            if (!empty($elements)) {
                foreach ($elements[1] as $pos => $index) {
                    $index = str_replace('"', '', $index);
                    $value = $elements[2][$pos];

                    if (strpos($value, '[') !== false) {
                        preg_match_all("/([\d]+)/", $value, $id);
                        $value = $id[1];
                    } else {
                        $value = intval($value);
                    }

                    $config_ids[$index] = $value;
                }
            }

            $config->ids['client'] = array(
                'id' => $config_ids['client_id']
            );

            foreach ($config_ids['warehouse_ids'] as $warehouse_id) {
                $config->ids['warehouses'][]['id'] = $warehouse_id;
            }

            foreach ($config_ids['sender_ids'] as $sender_id) {
                $config->ids['senders'][]['id'] = $sender_id;
            }

            foreach ($config_ids['requisite_ids'] as $requisite_id) {
                $config->ids['requisites'][]['id'] = $requisite_id;
            }
        }
    }

    /**
     * Returns the data for the compact look of the pickup point's schedule
     *
     * @param array $schedules The full work schedule of the pickup point.
     *
     * @return array The data required for the compact look of the work schedule.
     */
    public static function getScheduleDays($schedules)
    {
        if (empty($schedules)) {
            return array();
        }

        $ndash = html_entity_decode('&ndash;');

        $work_days = array();
        foreach ($schedules as $key_day => $day) {
            $day_index = $day['day'];

            $day['from'] = substr($day['from'], 0, strrpos($day['from'], ':', -1));
            $day['to'] = substr($day['to'], 0, strrpos($day['to'], ':', -1));

            $work_days[$day_index]['intervals'][] = array(
                'from' => $day['from'],
                'to' => $day['to']
            );
        }

        $last_day = reset($work_days);
        $last_day_num = 1;
        $index_compact = 1;
        $compact_work_days = array(
            $index_compact => array(
                'first_day' => $last_day_num,
                'last_day' => $last_day_num,
                'intervals' => $last_day['intervals']
            )
        );

        foreach ($work_days as $day_num => $day) {
            $last_day_num = $day_num;
            if (self::checkSameDays($last_day, $day)) {
                $compact_work_days[$index_compact]['last_day'] = $day_num;
            } else {
                $index_compact++;
                $compact_work_days[$index_compact] = array(
                    'first_day' => $last_day_num,
                    'last_day' => $last_day_num,
                    'intervals' => $day['intervals']
                );
            }
        }

        if ($last_day_num < 7) {
            $index_compact++;
            $compact_work_days[$index_compact]['first_day'] = $last_day_num + 1;
            $compact_work_days[$index_compact]['last_day'] = 7;
            $compact_work_days[$index_compact]['from'] = false;
        }

        foreach ($compact_work_days as &$day) {
            if (!empty($day['intervals'])) {
                foreach ($day['intervals'] as &$interval) {
                    $interval = $interval['from'] . $ndash . $interval['to']; // &ndash; is used as a time interval separator
                }
                $day['schedule'] = implode(', ', $day['intervals']);
            }

            if ($day['last_day'] == 7) {
                $day['last_day'] = 0;
            }

            if ($day['first_day'] == 7) {
                $day['first_day'] = 0;
            }
        }

        return $compact_work_days;
    }

    /**
     * Checks that the working hours on the two days are the same
     *
     * @param array $day_one The array with the data of the first day
     * @param array $day_two The array with the data of the second day
     *
     * @return bool If the working hours are the same, returns "true"; otherwise returns "false"
     */
    public static function checkSameDays($day_one, $day_two)
    {
        $is_same = true;
        foreach ($day_one['intervals'] as $interval_index => $interval) {
            if (!isset($day_two['intervals'][$interval_index])) {
                $is_same = false;

            } elseif ($interval['from'] != $day_two['intervals'][$interval_index]['from']) {
                $is_same = false;

            } elseif ($interval['to'] != $day_two['intervals'][$interval_index]['to']) {
                $is_same = false;
            }
        }

        return $is_same;
    }

    /**
     * Retrieves only the necessary information from Yandex.Delivery response.
     *
     * @param array $data The list and data of all shipping services provided by Yandex.Delivery.
     *
     * @return array The necessary shipping information.
     */
    public static function compactDeliveries($data)
    {
        $deliveries = array();
        foreach ($data as $key => $delivery) {
            if (empty($delivery['delivery'])) {
                continue;
            }

            $delivery_id = $delivery['delivery']['id'];
            
            if ($delivery['type'] == YD_DELIVERY_PICKUP) {
		$deliveries['pickup'][$delivery_id] = array(
		    'cost' => $delivery['cost'],
		    'costWithRules' => $delivery['costWithRules'],
		    'delivery_name' => $delivery['delivery']['name'],
		    'unique_name' => $delivery['delivery']['unique_name'],
		    'minDays' => $delivery['minDays'],
		    'maxDays' => $delivery['maxDays'],
		    'tariffId' => $delivery['tariffId'],
		    'direction' => $delivery['direction'],
		    'is_pickup_point' => $delivery['is_pickup_point'],
		    'is_ff_import_available' => $delivery['delivery']['is_ff_import_available'],
		    'is_ds_import_available' => $delivery['delivery']['is_ds_import_available'],
		    'is_ff_withdraw_available' => $delivery['delivery']['is_ff_withdraw_available'],
		    'is_ds_withdraw_available' => $delivery['delivery']['is_ds_withdraw_available'],
		    'deliveryIntervals' => $delivery['deliveryIntervals'],
		);

		$deliveries['pickup'][$delivery_id]['pickupPoints'] = self::getCompactPickupPoints($delivery['pickupPoints']);
	    }
	    
            if ($delivery['type'] == YD_DELIVERY_COURIER) {
                $deliveries['courier'][$delivery_id] = array(
                    'delivery_id' => $delivery_id,
                    'name' => $delivery['delivery']['name'],
                    'cost' => $delivery['cost'],
                    'costWithRules' => $delivery['costWithRules'],
                    'delivery_name' => $delivery['delivery']['name'],
                    'unique_name' => $delivery['delivery']['unique_name'],
                    'minDays' => $delivery['minDays'],
                    'maxDays' => $delivery['maxDays'],
                    'tariffId' => $delivery['tariffId'],
                    'direction' => $delivery['direction'],
                    'is_pickup_point' => $delivery['is_pickup_point'],
                    'is_ff_import_available' => $delivery['delivery']['is_ff_import_available'],
                    'is_ds_import_available' => $delivery['delivery']['is_ds_import_available'],
                    'is_ff_withdraw_available' => $delivery['delivery']['is_ff_withdraw_available'],
                    'is_ds_withdraw_available' => $delivery['delivery']['is_ds_withdraw_available'],
                    'deliveryIntervals' => $delivery['deliveryIntervals'],
                );
            }
        }

        return $deliveries;
    }

    /**
     * Retrieves only the necessary information about pickup points from Yandex.Delivery response.
     *
     * @param array $pickup_points The list of pickup points.
     *
     * @return array The necessary information about the pickup points.
     */
    public static function getCompactPickupPoints($pickup_points)
    {
        $compact_pickups = array();
        foreach ($pickup_points as $pickup) {
            $short_address = explode(', ', $pickup['full_address']);
            $short_address = array_slice($short_address, 2);

            $compact_pickups[$pickup['id']] = array(
                'id' => $pickup['id'],
                'delivery_id' => $pickup['delivery_id'],
                'type' => $pickup['type'],
                'name' => $pickup['name'],
                'lat' => $pickup['lat'],
                'lng' => $pickup['lng'],
                'location_name' => $pickup['location_name'],
                'full_address' => $pickup['full_address'],
                'comment' => $pickup['address']['comment'],
                'phones' => $pickup['phones'],
                'phone' => !empty($pickup['phones']) ? reset($pickup['phones']) : array(),
                'short_address' => implode(', ', $short_address),
                'schedules' => $pickup['schedules'],
                'address' => array(
                    'street' => $pickup['address']['street']
                )
            );
        }

        return $compact_pickups;
    }

    /**
     * Selects only the delivery services that are enabled in the shipping method settings.
     *
     * @param array $data           The list and data of all shipping services provided by Yandex.Delivery.
     * @param array $service_params The settings of the shipping method in CS-Cart.
     *
     * @return array The list of selected delivery services.
     */
    public static function filterDeliveries($data, $service_params)
    {
        if (empty($service_params['deliveries'])) {
            return array();
        }

        $deliveries_ids = array_flip($service_params['deliveries']);
        $deliveries = array_intersect_key($data, $deliveries_ids);

        foreach ($deliveries as $key => &$delivery) {
            unset($delivery['pickupPoints']);
        }

        return $deliveries;
    }

    /**
     * Selects only the pickup points of the delivery services that are enabled in the shipping method settings.
     *
     * @param array $data           The list and data of all shipping services provided by Yandex.Delivery.
     * @param array $service_params The settings of the shipping method in CS-Cart.
     *
     * @return array The list of selected pickup points.
     */
    public static function filterPickupPoints($data, $service_params)
    {
        if (empty($service_params['deliveries'])) {
            return array();
        }

        $deliveries_ids = array_flip($service_params['deliveries']);
        $deliveries = array_intersect_key($data, $deliveries_ids);

        $pickup_points = array();
        foreach ($deliveries as $key => $delivery) {
            if (empty($delivery['is_pickup_point'])) {
                continue;
            }
            $pickup_points += $delivery['pickupPoints'];
        }

        return $pickup_points;
    }
}
