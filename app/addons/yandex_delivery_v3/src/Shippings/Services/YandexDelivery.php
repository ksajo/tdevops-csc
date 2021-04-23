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

use Tygh\Addons\YandexDelivery\Enum\DeliveryType;
use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;
use Tygh\Registry;
use Tygh\Shippings\IPickupService;
use Tygh\Shippings\IService;
use Tygh\Tygh;

class YandexDelivery implements IService, IPickupService
{
    /** @var bool $_allow_multithreading Availability to perform multi-threads requests. */
    private $_allow_multithreading = false;

    /** @var string $calculation_currency The currency in which the carrier calculates shipping costs. */
    public $calculation_currency = 'RUB';

    /** @var array<string, string> $error_stack Stack for error messages. */
    private $error_stack = [];

    /** @var array<string, string> $shipping_info Information about shipping method. */
    public $shipping_info = [];

    /** @var int $company_id Current Company id environment*/
    public $company_id = 0;

    /**
     * @inheritDoc
     */
    public function getPickupMinCost()
    {
        // TODO: Implement getPickupMinCost() method.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getPickupPoints()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPickupPointsQuantity()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['number_of_pickup_points']) ? $shipping_data['number_of_pickup_points'] : false;
    }

    /**
     * @inheritDoc
     */
    public function prepareData($shipping_info)
    {
        $this->shipping_info = $shipping_info;
        $this->company_id = $shipping_info['package_info']['origination']['company_id'];
        $shipping_id = isset($shipping_info['keys']['shipping_id']) ? $shipping_info['keys']['shipping_id'] : 0;

        if (isset(Tygh::$app['session']['cart']['selected_yad_courier'][$this->company_id][$shipping_id]['courier_point_id'])) {
            $this->courier_point_id = Tygh::$app['session']['cart']['selected_yad_courier'][$this->company_id][$shipping_id]['courier_point_id'];
        }

        if (!isset(Tygh::$app['session']['cart']['selected_yad_office'][$this->company_id][$shipping_id]['pickup_point_id'])) {
            return;
        }
        $this->pickup_point_id = Tygh::$app['session']['cart']['selected_yad_office'][$this->company_id][$shipping_id]['pickup_point_id'];
    }

    /**
     * @inheritDoc
     */
    public function processResponse($response)
    {
        $result = [
            'cost'          => false,
            'error'         => false,
            'delivery_time' => false,
        ];

        $service_params = $this->shipping_info['service_params'];
        if (is_array($response) && isset($response['error'])) {
            $result['error'] = $response['error'];
            return $result;
        }

        if (!isset($service_params['type_delivery'])) {
            return $result;
        }
        switch ($service_params['type_delivery']) {
            case DeliveryType::PICKUP:
                $result = $this->processPickupPoints($response, $service_params);
                break;
            case DeliveryType::COURIER:
                $result = $this->processCourierPoints($response, $service_params);
                break;
            case DeliveryType::POST:
            default:
                break;
        }

        $this->storeShippingData($result);
        return $result;
    }

    /**
     * @param array<string, string> $response       Response information from Yandex.Delivery API.
     * @param array<string, string> $service_params Shipping service parameters.
     *
     * @return array
     *
     * @throws \Exception DateTime related exception.
     */
    protected function processPickupPoints(array $response, array $service_params)
    {
        $shipping_service = ServiceProvider::getShippingService();
        $client = ServiceProvider::getApiService();

        $return = [
            'cost'          => false,
            'error'         => false,
            'delivery_time' => false,
            'data'          => [],
        ];
        if (empty($response)) {
            return $return;
        }

        $delivery_ids = $service_params['deliveries'];
        $deliveries = $shipping_service->filterDeliveryServices($response, $delivery_ids);

        $deliveries_info = [];
        foreach ($deliveries as $delivery) {
            $deliveries_info[$delivery['delivery']['partner']['id']] = $delivery['delivery']['partner'];
        }

        $package_info = $this->shipping_info['package_info'];
        $location_term = $package_info['location']['city'] . ', '
            . fn_get_state_name($package_info['location']['state'], $package_info['location']['country']);
        if (Registry::isExist($client::CACHE_KEY . '.' . $location_term)) {
            $pickup_points = Registry::get($client::CACHE_KEY . '.' . $location_term);
        } else {
            $pickup_points = $shipping_service->getPickupPoints($deliveries);
            if (empty($pickup_points) || isset($pickup_points['errors'])) {
                return $return;
            }
            Registry::set($client::CACHE_KEY . '.' . $location_term, $pickup_points);
        }
        /**
         * Allows to sort pickup points before setting a selected one.
         *
         * @param array<int, array<string, array<string, string>>>    $pickup_points Array of pickup points info.
         * @param array<string, array<string, array<string, string>>> $shipping_info Information about shipping method.
         */
        fn_set_hook('yandex_delivery_v3_modify_pickup_points', $pickup_points, $this->shipping_info);

        if (empty($this->pickup_point_id) || !isset($pickup_points[$this->pickup_point_id])) {
            $selected_pickpoint = reset($pickup_points);
            $selected_pickpoint_id = $selected_pickpoint['id'];
        } else {
            $selected_pickpoint_id = $this->pickup_point_id;
        }
        $shipping_data = $shipping_service->getDeliveryByPickpointId($deliveries, $selected_pickpoint_id);
        if (empty($shipping_data)) {
            return $return;
        }
        $return['cost'] = $shipping_data['cost']['deliveryForCustomer'];

        if (isset($pickup_points[$selected_pickpoint_id]['schedule'])) {
            $pickup_points[$selected_pickpoint_id]['work_time'] = $shipping_service->calculateWorkTime($pickup_points[$selected_pickpoint_id]['schedule']);
        }

        $return['data']['selected_point'] = $selected_pickpoint_id;
        $return['data']['deliveries'] = $deliveries;
        $return['data']['pickup_points'] = $pickup_points;
        $return['data']['deliveries_info'] = $deliveries_info;
        if ($shipping_data) {
            $return['data']['selected_service'] = $shipping_data;
            $return['delivery_time'] = $shipping_service->getDeliveryTime($shipping_data);
        }

        return $return;
    }

    /**
     * @param array<string, string> $response       Response information from Yandex.Delivery API.
     * @param array<string, string> $service_params Shipping service parameters.
     *
     * @return array
     *
     * @throws \Exception DateTime related exception.
     */
    protected function processCourierPoints(array $response, array $service_params)
    {
        $result = [
            'cost'          => false,
            'error'         => false,
            'delivery_time' => false,
            'data'          => [],
        ];
        if (empty($response)) {
            return $result;
        }

        $shipping_service = ServiceProvider::getShippingService();

        $delivery_ids = $service_params['deliveries'];
        $deliveries = $shipping_service->filterDeliveryServices($response, $delivery_ids);

        if (empty($deliveries)) {
            return $result;
        }
        $deliveries_info = [];
        foreach ($deliveries as $delivery) {
            $deliveries_info[$delivery['delivery']['partner']['id']] = $delivery['delivery']['partner'];
        }
        $reformated_deliveries = [];
        foreach ($deliveries as $delivery) {
            $reformated_deliveries[$delivery['tariffId']] = $delivery;
        }
        $deliveries = $reformated_deliveries;

        if (empty($this->courier_point_id) || !isset($deliveries[$this->courier_point_id])) {
            $selected_point = key($deliveries);
        } else {
            $selected_point = $this->courier_point_id;
        }
        $selected_delivery = $deliveries[$selected_point];
        $result['cost'] = $selected_delivery['cost']['deliveryForCustomer'];
        $deliveries[$selected_point]['work_time'] = $shipping_service->calculateWorkTime($deliveries[$selected_point]['delivery']['courierSchedule']['schedule'], DeliveryType::COURIER);
        $result['data']['deliveries'] = $deliveries;
        $result['data']['courier_points'] = $deliveries;
        $result['data']['deliveries_info'] = $deliveries_info;
        $result['data']['selected_point'] = $selected_point;

        $result['delivery_time'] = $shipping_service->getDeliveryTime($selected_delivery);
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function processErrors($response)
    {
        return implode(';', $this->error_stack);
    }

    /**
     * @inheritDoc
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * @inheritDoc
     */
    public function getRequestData()
    {
        Registry::registerCache(
            YandexDeliveryService::CACHE_KEY,
            YandexDeliveryService::CACHE_PERIOD,
            Registry::cacheLevel('time')
        );
        $service_params = $this->shipping_info['service_params'];
        if (empty($service_params['deliveries'])) {
            return [];
        }

        $request_data = [];
        /** @var array<string, array<string, string>> $package_info */
        $package_info = $this->shipping_info['package_info'];
        $request_data['city_from'] = !empty($service_params['city_from'])
            ? $service_params['city_from']
            : $package_info['origination']['city'];
        $request_data['city_to'] = !empty($package_info['location']['city'])
            ? $package_info['location']['city']
            : '';

        $service = ServiceProvider::getShippingService();
        if (
            !isset(
                $package_info['location']['city'],
                $package_info['location']['state'],
                $package_info['location']['country']
            )
        ) {
            return [];
        }
        $request_data['weight'] = sprintf('%.3f', round((float) $package_info['W'], 3));
        $request_data['total_cost'] = $package_info['C'];

        $package_size = $service->calculateDimensions($package_info, $service_params);
        $request_data['width'] = !empty($package_size['width']) ? $package_size['width'] : $service_params['width'];
        $request_data['height'] = !empty($package_size['height']) ? $package_size['height'] : $service_params['height'];
        $request_data['length'] = !empty($package_size['length']) ? $package_size['length'] : $service_params['length'];

        return $request_data;
    }

    /**
     * @inheritDoc
     */
    public function getSimpleRates()
    {
        $service_params = $this->shipping_info['service_params'];
        if (empty($service_params['deliveries'])) {
            return ['error' => __('yandex_delivery_v3.no_shipping_services_selected')];
        }

        $package_info = $this->shipping_info['package_info'];

        if (empty($package_info['location']['city'])) {
            return [];
        }
        $client = ServiceProvider::getApiService();
        $request_data = $this->getRequestData();

        $location_term = $request_data['city_to'] . ', '
            . fn_get_state_name($package_info['location']['state'], $package_info['location']['country']);
        $location = Registry::get($client::CACHE_KEY . '.' . md5($location_term));

        if (empty($location)) {
            $locations = $client->getLocation($location_term);
            $location = reset($locations);
            if (!isset($location['geoId'])) {
                return [];
            }
            Registry::set($client::CACHE_KEY . '.' . md5($location_term), $location);
        }

        $to = ['geoId' => $location['geoId']];
        $service = ServiceProvider::getShippingService();
        $param = [
            'deliveryType' => strtoupper($this->shipping_info['service_params']['type_delivery']),
            'places' => $service->formatPackagesDimensions($package_info),
        ];

        $key = md5($location['geoId'] . '_' . $request_data['weight'] . '_' . $request_data['height']
            . '_' . $request_data['width'] . '_' . $request_data['length'] . '_' . $param['deliveryType']);
        $response = Registry::get($client::CACHE_KEY . '.' . $key);
        if (!empty($response)) {
            return $response;
        }

        $response = $client->putDeliveryOptions(
            (int) $service_params['sender_id'],
            $to,
            $param
        );
        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $this->internalError($error['errorCode'] . ' - ' . $error['message']);
            }
        } else {
            Registry::set($client::CACHE_KEY . '.' . $key, $response);
        }
        return $response;
    }

    /**
     * Fetches stored data from session
     *
     * @return array<string, string>
     */
    protected function getStoredShippingData()
    {
        $group_key = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id])) {
            return Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id];
        }

        return [];
    }

    /**
     * Collects errors during preparing and processing request
     *
     * @param string $error Error description.
     */
    private function internalError($error)
    {
        $this->error_stack[] = $error;
    }

    /**
     * Saves shipping data to session
     *
     * @param array<string, string> $rate Rate data.
     *
     * @return bool
     */
    protected function storeShippingData(array $rate)
    {
        $group_key = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id] = [
            'number_of_pickup_points' => isset($rate['data']['pickup_points']) ? count($rate['data']['pickup_points']) : false,
            'cost'                    => $rate['cost'],
        ];

        return true;
    }

    /**
     * Returns shipping service information.
     *
     * @return array<string, string>
     */
    public static function getInfo()
    {
        return [
            'name'         => __('carrier_yandex_delivery'),
            'tracking_url' => '',
        ];
    }
}
