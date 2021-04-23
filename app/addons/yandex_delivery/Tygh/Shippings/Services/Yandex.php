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

namespace Tygh\Shippings\Services;

use Tygh\Tygh;
use Tygh\Registry;
use Tygh\Shippings\IService;
use Tygh\Shippings\IPickupService;
use Tygh\Shippings\YandexDelivery\YandexDelivery;
use Tygh\Shippings\YandexDelivery\Objects\RequestDeliveryList;

class Yandex implements IService, IPickupService
{
    /**
     * Abailability multithreading in this module
     *
     * @var bool $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * The currency in which the carrier calculates shipping costs.
     *
     * @var string $calculation_currency
     */
    public $calculation_currency = 'RUB';

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $error_stack
     */
    private $error_stack = array();

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    public $sid;

    public $tariff_id = 0;

    public $pickuppoint_id = 0;

    public $courierpoint_id = 0;

    public $_shipping_info;

    /**
     * Returns shipping service information
     *
     * @return array
     */
    public static function getInfo()
    {
        return [
            'name'         => __('carrier_yandex'),
            'tracking_url' => '',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPickupMinCost()
    {
        if (!$this->isPickupService()) {
            return false;
        }

        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['cost']) ? $shipping_data['cost'] : false;
    }

    /**
     * @inheritdoc
     */
    public function getPickupPoints()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPickupPointsQuantity()
    {
        if (!$this->isPickupService()) {
            return false;
        }

        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['number_of_pickup_points']) ? $shipping_data['number_of_pickup_points'] : false;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param array $response
     *
     * @internal param string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        $error = implode('; ', $this->error_stack);

        return $error;
    }

    /**
     * Sets data to internal class variable
     *
     * @param  array $shipping_info
     *
     * @return array|void
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');

        $group_key = isset($shipping_info['keys']['group_key']) ? $shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($shipping_info['keys']['shipping_id']) ? $shipping_info['keys']['shipping_id'] : 0;

        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'])) {
            $this->courierpoint_id = Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['courier_point_id'];
        }

        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'])) {
            $this->pickuppoint_id = Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['pickup_point_id'];
        }
    }

    /**
     * Prepare request information
     *
     * @return array|RequestDeliveryList Prepared data
     */
    public function getRequestData()
    {
        $service_params = $this->_shipping_info['service_params'];
        if (empty($service_params['deliveries'])) {
            return [];
        }

        $request_data = new RequestDeliveryList();

        $package_info = $this->_shipping_info['package_info'];

        $request_data->city_from = !empty($service_params['city_from']) ? $service_params['city_from'] : $package_info['origination']['city'];
        $request_data->city_to = !empty($package_info['location']['city']) ? $package_info['location']['city'] : '';

        $yd = YandexDelivery::init($this->_shipping_info['shipping_id']);
        $request_data->geo_id_to = $yd->getGeoID($package_info);

        $weight_data = fn_convert_weight_to_imperial_units($package_info['W']);
        $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        $request_data->weight = sprintf('%.3f', round((double) $weight + 0.00000000001, 3));

        $package_size = YandexDelivery::getSizePackage($package_info, $service_params);

        $request_data->width = !empty($package_size['width']) ? $package_size['width'] : 10;
        $request_data->height = !empty($package_size['height']) ? $package_size['height'] : 10;
        $request_data->length = !empty($package_size['length']) ? $package_size['length'] : 10;

        $request_data->total_cost = $package_info['C'];

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return array Server response
     */
    public function getSimpleRates()
    {
        static $init_cache = false;

        $cache_name = 'yd_cache';
        if (!$init_cache) {
            Registry::registerCache($cache_name, YD_CACHE_DELIVERY, Registry::cacheLevel('time'));
            $init_cache = true;
        }

        $service_params = $this->_shipping_info['service_params'];
        if (empty($service_params['deliveries'])) {
            return ['error' => __('yandex_delivery.no_shipping_services_selected')];
        }

        $package_info = $this->_shipping_info['package_info'];

        if (empty($package_info['location']['city'])) {
            return [];
        }

        $weight = !empty($package_info['W']) ? $package_info['W'] : '';
        $state = empty($package_info['location']['state']) ? '' : $package_info['location']['state'];
        $key = md5($weight . $state . $package_info['location']['city']);
        $response = Registry::get($cache_name . '.' . $key);

        if (!empty($response)) {
            return $response;
        }

        $yd = YandexDelivery::init($this->_shipping_info['shipping_id']);

        $request_delivery_list = $this->getRequestData();
        $request_delivery_list->client_id = $yd->client_id;
        $request_delivery_list->sender_id = $yd->sender_id;

        $response = array();
        if (!empty($yd->client_id)) {
            $response = $yd->searchDeliveryList($request_delivery_list);
        }

        if ($response['status'] == 'ok') {
            $deliveries = YandexDelivery::compactDeliveries($response['data']);

            $response['data'] = isset($deliveries['pickup']) ? $deliveries['pickup'] : array();
            $response['courier'] = isset($deliveries['courier']) ? $deliveries['courier'] : array();
            Registry::set($cache_name . '.' . $key, $response);

        } else {
            $this->internalError($response['error']);

            if (!empty($response['data']['errors'])) {
                foreach ($response['data']['errors'] as $key => $error) {
                    $this->internalError($key . ' - ' . $error);
                }

            }
        }

        return $response;
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param array $response
     *
     * @internal param string $resonse Reponse from Shipping service server
     * @return array Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = [
            'cost'          => false,
            'error'         => false,
            'delivery_time' => false,
        ];

        $service_params = $this->_shipping_info['service_params'];
        if (is_array($response) && isset($response['error'])) {
            $return['error'] = $response['error'];

            return $return;
        }

        if (fn_yandex_delivery_check_type_delivery($service_params)) {
            $return = $this->getCourierPoints($response, $service_params, $return);
        } else {
            $this->processPickpoints($response, $service_params, $return);
        }

        $this->storeShippingData($return);

        return $return;
    }

    /**
     * Sorts and filters pickup points provided by Yandex.Delivery
     *
     * @param array $response       The list and data of all shipping services provided by Yandex.Delivery.
     * @param array $service_params The settings of the shipping method in CS-Cart.
     * @param array $return         The results of the function are saved to this variable.
     */
    public function processPickpoints($response, $service_params, &$return)
    {
        if (empty($response)) {
            return;
        }

        $deliveries = YandexDelivery::filterDeliveries($response['data'], $service_params);
        $pickup_points = YandexDelivery::filterPickupPoints($response['data'], $service_params);

        if (!empty($pickup_points)) {
            $package_info = $this->_shipping_info['package_info'];
            $yd = YandexDelivery::init($this->_shipping_info['shipping_id']);
            $yd->getGeoID($package_info);

            if ($this->_shipping_info['service_params']['sort_type'] == "near") {
                $pickup_points = $this->sortByNearPoints($pickup_points);
            }

            if (empty($this->pickuppoint_id) || !isset($pickup_points[$this->pickuppoint_id])) {
                $selected_point = $this->findNearPickpoint($pickup_points);
            } else {
                $selected_point = $this->pickuppoint_id;
            }

            if (!empty($pickup_points[$selected_point])) {
                $pickup_points[$selected_point]['work_time'] = YandexDelivery::getScheduleDays($pickup_points[$selected_point]['schedules']);
                $delivery_id = $pickup_points[$selected_point]['delivery_id'];
            }

            if (!empty($delivery_id)) {
                $shipping_data = $deliveries[$delivery_id];
            } else {
                $shipping_data = reset($deliveries);
            }

            $return['data'] = array(
                'selected_point' => $selected_point,
                'deliveries'     => $deliveries,
                'pickup_points'  => $pickup_points,
            );

            $return['delivery_time'] = $this->getDeliveryTime($shipping_data);
        }

        if (empty($this->error_stack) && isset($shipping_data)) {
            if (isset($shipping_data['costWithRules'])) {
                $return['cost'] = $shipping_data['costWithRules'];
            }
        } else {
            $return['error'] = $this->processErrors($response);
        }
    }

    /**
     * Prepares data about courier delivery.
     *
     * @param array $response       The list and data of all shipping services provided by Yandex.Delivery.
     * @param array $service_params The settings of the shipping method in CS-Cart.
     * @param array $return         The results of the function are saved to this variable.
     *
     * @return array The array of data about courier delivery.
     */
    public function getCourierPoints($response, $service_params, $return)
    {
        if (empty($response)) {
            return $return;
        }

        $deliveries = YandexDelivery::filterDeliveries($response['courier'], $service_params);

        $shipping_data = [];
        $selected_point = 0;
        if (!empty($deliveries)) {
            $selected_point = $this->getSelectedCourierPoint($deliveries);

            $delivery_id = $deliveries[$selected_point]['delivery_id'];
            if (!empty($delivery_id) && !empty($deliveries[$delivery_id])) {
                $shipping_data = $deliveries[$delivery_id];
            } else {
                $shipping_data = reset($deliveries);
            }
        }

        $return['data'] = [
            'selected_point' => $selected_point,
            'deliveries'     => $deliveries,
            'courier_points' => $deliveries,
            'delivery_time'  => $this->getDeliveryTime($shipping_data)
        ];

        if (empty($this->error_stack) && isset($shipping_data)) {
            if (isset($shipping_data['costWithRules'])) {
                $return['cost'] = $shipping_data['costWithRules'];
            }
        } else {
            $return['error'] = $this->processErrors($response);
        }

        return $return;
    }

    /**
     * Select the nearest pickup point id.
     *
     * @param array $pickup_points The list of pickup points.
     *
     * @return array The nearest pickup point id
     */
    protected function findNearPickpoint($pickup_points)
    {
        $pickup_points_near = $this->getNearPickpoints($pickup_points);
        $pickup_points_near = empty($pickup_points_near) ? array() : array_keys($pickup_points_near);

        return reset($pickup_points_near);
    }

    /**
     * Calculate the distance between shipping address and pickup points; sort pickup points from nearest to farthest
     *
     * @param array $pickup_points The list of pickup points
     *
     * @return array $near_pickoints The list of pickup point IDs with calculated distances
     */
    protected function getNearPickpoints($pickup_points)
    {
        $state = empty($this->_shipping_info['package_info']['location']['state']) ? '' : $this->_shipping_info['package_info']['location']['state'];
        $address = !empty($this->_shipping_info['package_info']['location']['address']) ? trim($this->_shipping_info['package_info']['location']['address']) : '';
        $city = empty($this->_shipping_info['package_info']['location']['city']) ? '' : $this->_shipping_info['package_info']['location']['city'];
        $full_address = $state . $city . $address;
        $key = md5($this->_shipping_info['shipping_id'] . implode('_', $this->_shipping_info['service_params']['deliveries']) . $full_address);

        $near_pickoints = fn_get_session_data($key);

        if (empty($near_pickoints)) {
            $address = preg_split('/[ ,]+/', $address);
            $address[] = trim($this->_shipping_info['package_info']['location']['city']);

            $yd = YandexDelivery::init($this->_shipping_info['shipping_id']);
            $ll_address = $yd->getGeoByAddress($address);

            if (!empty($ll_address)) {
                $lat_pickoints = array();
                $lng_pickoints = array();
                $near_pickoints = array();
                foreach ($pickup_points as $id => $point) {
                    $lat_pickoints[$id] = $point['lat'];
                    $lng_pickoints[$id] = $point['lng'];
                    $near_pickoints[$id] = sqrt(pow($lat_pickoints[$id] - $ll_address[1], 2) + pow($lng_pickoints[$id] - $ll_address[0], 2));
                }

                asort($near_pickoints);
            }

            fn_set_session_data($key, $near_pickoints, YD_CACHE_DELIVERY);
        }

        return empty($near_pickoints) ? $pickup_points : $near_pickoints;
    }

    /**
     * Sort pickup points by distance from shipping address
     *
     * @param array $pickup_points The list of pickup points
     *
     * @return array $near_pickoints The sorted list of pickup points
     */
    protected function sortByNearPoints($pickup_points)
    {
        $sort_pickup_points = array();
        $pickpoints_near = $this->getNearPickpoints($pickup_points);

        foreach ($pickpoints_near as $point_id => $distance) {
            if (!empty($pickup_points[$point_id])) {
                $sort_pickup_points[$point_id] = $pickup_points[$point_id];
            }
        }

        return $sort_pickup_points;
    }

    /**
     * Prepares a string with delivery time.
     *
     * @param array $shipping_data The array with the shipping data.
     *
     * @return string|null The string with the delivery time.
     */
    protected function getDeliveryTime($shipping_data)
    {
        if (empty($shipping_data['minDays'])) {
            return null;
        }

        if ($shipping_data['minDays'] == $shipping_data['maxDays']) {
            $delivery_time = sprintf('%s %s', $shipping_data['minDays'], __('days'));
        } else {
            $delivery_time = sprintf('%s - %s %s', $shipping_data['minDays'], $shipping_data['maxDays'], __('days'));
        }

        return $delivery_time;
    }

    /**
     * Gets the identifier of the selected courier service.
     *
     * @param array $courier_points The list of courier services.
     *
     * @return int The identifier of the selected courier service.
     */
    protected function getSelectedCourierPoint($courier_points)
    {
        if (empty($this->courierpoint_id) || !isset($courier_points[$this->courierpoint_id])) {
            $courier_points_ids = array_keys($courier_points);
            $selected_point = reset($courier_points_ids);
        } else {
            $selected_point = $this->courierpoint_id;
        }

        return $selected_point;
    }

    /**
     * Saves shipping data to session
     *
     * @param array $rate Rate data
     *
     * @return bool
     */
    protected function storeShippingData($rate)
    {
        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;
        Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id] = [
            'number_of_pickup_points' => isset($rate['data']['pickup_points']) ? count($rate['data']['pickup_points']) : false,
            'cost'                    => $rate['cost'],
        ];

        return true;
    }

    /**
     * Fetches stored data from session
     *
     * @return array
     */
    protected function getStoredShippingData()
    {
        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;
        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id])) {
            return Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id];
        }

        return [];
    }

    protected function isPickupService()
    {
        return !fn_yandex_delivery_check_type_delivery($this->_shipping_info['service_params']);
    }

    /**
     * Collects errors during preparing and processing request
     *
     * @param string $error
     */
    private function internalError($error)
    {
        $this->error_stack[] = $error;
    }
}
