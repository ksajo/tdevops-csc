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
use Tygh\Shippings\BoxberryClient;

use Boxberry\Models\Point;
use Boxberry\Models\DeliveryCosts;
use Boxberry\Collections\ListPointsCollection;

use Exception;

class RusBoxberry implements IService, IPickupService
{
    /** @var array $shipping_info Shipping data */
    protected $shipping_info;
    protected $client;
    protected $weight;

    /**
     * Returns shipping service information
     *
     * @return array
     */
    public static function getInfo()
    {
        return [
            'name'         => __('carrier_rus_boxberry'),
            'tracking_url' => 'http://boxberry.ru/tracking/?id=%s',
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
     * @inheritdoc
     */
    public function prepareData($shipping_info)
    {
        $this->shipping_info = $shipping_info;
        $this->client = new BoxberryClient($shipping_info['service_params']);

        $default_weight = $shipping_info['service_params']['default_weight'];
        $weight_data = fn_convert_weight_to_imperial_units($shipping_info['package_info']['W']);
        $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        $weight = sprintf('%.3f', round((double) $weight + 0.00000000001, 3)) * 1000;
        if (empty($weight) || $weight === (float)1 && !empty($default_weight)){
            $weight = $default_weight;
        }
        $this->weight = $weight;
    }

    /**
     * @inheritdoc
     */
    public function processResponse($response)
    {
        if (isset($this->shipping_info['service_params']['margin_percent']) && is_numeric($this->shipping_info['service_params']['margin_percent'])) {
            $response['cost'] += ($response['cost'] * $this->shipping_info['service_params']['margin_percent']) / 100;
        }
        if (isset($this->shipping_info['service_params']['margin_plus']) && is_numeric($this->shipping_info['service_params']['margin_plus'])) {
            $response['cost'] += $this->shipping_info['service_params']['margin_plus'];
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function processErrors($response)
    {
        if (!is_array($response) || (isset($response['error']) && $response['error'])) {
            return $response['error'];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function allowMultithreading()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getRequestData()
    {
        return array(
            'method' => 'GET',
            'url' => $this->client->getApiUrl(),
            'data' => array(),
        );
    }

    /**
     * @inheritdoc
     */
    public function getSimpleRates()
    {
        $shipping_info = $this->shipping_info;
        $package_info = $this->shipping_info['package_info'];
        $service_params = $this->shipping_info['service_params'];
        $service_code = $this->shipping_info['service_code'];

        $delivery_costs = $this->client->getDeliveryCosts();
        $delivery_costs->setWeight($this->weight);

        if ($service_params['boxberry_target_start']) {
            $delivery_costs->setTargetstart($service_params['boxberry_target_start']);
        }

        if ($service_code == 'boxberry_courier' || $service_code == 'boxberry_courier_prepaid') {
            $zip_delivery = !empty($package_info['location']['zipcode']) ? $package_info['location']['zipcode'] : 0;

            try {
                $zipcheck = $this->client->getZipCheck();
                $zipcheck->setZip($zip_delivery);

                $responseObject = $this->client->execute($zipcheck);
                if ($responseObject->getExpressDelivery()) {
                    $delivery_costs->setZip($zip_delivery);

                } else {
                    $response = array(
                        'cost' => false,
                        'error' => false,
                        'delivery_time' => false,
                    );

                    return $response;
                }

            } catch(Exception $e) {
                $response = array(
                    'cost' => false,
                    'error' => $e->getMessage(),
                    'delivery_time' => false,
                );

                return $response;
            }

        } elseif ($service_code == 'boxberry_self' || $service_code == 'boxberry_self_prepaid') {

            $condition = '';
            if (!empty($package_info['location']['country'])) {
                $condition = db_quote(' AND a.country_code = ?s', $package_info['location']['country']);
            }

            if (!empty($package_info['location']['state'])) {
                $state = db_get_field(
                    'SELECT b.state'
                    . ' FROM ?:states as a LEFT JOIN ?:state_descriptions as b'
                        . ' ON b.state_id = a.state_id AND b.lang_code = ?s'
                    . ' WHERE a.code = ?s ?p',
                    CART_LANGUAGE, $package_info['location']['state'], $condition
                );
            }

            $city = isset($package_info['location']['city']) ? $package_info['location']['city'] : '';
            $region = isset($state) ? $state : null;
            $group_key = isset($shipping_info['keys']['group_key']) ? $shipping_info['keys']['group_key'] : 0;
            $shipping_id = isset($shipping_info['keys']['shipping_id']) ? $shipping_info['keys']['shipping_id'] : 0;

            $boxberry = &Tygh::$app['session']['cart']['shippings_extra']['boxberry'][$group_key][$shipping_id];

            try {
                $widget_key_method = $this->client->getKeyIntegration();
                $widget_key_method->setToken($service_params['password']);
                $widget_response = $this->client->execute($widget_key_method);
                $widget_key = $widget_response->getWidgetKey();
            } catch (Exception $e) {
                $response = array(
                    'cost' => false,
                    'delivery_time' => false,
                    'error' => $e->getMessage()
                );

                return $response;
            }

            $boxberry_target_start = $service_params['boxberry_target_start'];
            $payment_sum = round($package_info['C']);

            $boxberry['apiKey'] = $service_params['password'];
            $boxberry['apiKeyWidget'] = $widget_key;
            $boxberry['boxberry_target_start'] = $boxberry_target_start;
            $boxberry['boxberry_weight'] = $this->weight;
            $boxberry['boxberry_ordersum'] = $payment_sum;

            if ($shipping_info['service_code'] == 'boxberry_courier_prepaid' || $shipping_info['service_code'] == 'boxberry_self_prepaid') {
                $boxberry['boxberry_paymentsum'] = 0;
            } else {
                $boxberry['boxberry_paymentsum'] = $payment_sum;
            }

            try {
                $pickups_list = $this->client->getPickupPoints($city, $region);

                if ($pickups_list->valid()) {
                    $boxberry['number_of_pickup_points'] = $pickups_list->count();
                    if ($this->needResetPickupPoint($boxberry, $pickups_list, $city)) {
                        $pickups_list->rewind();
                        $boxberry_object = $pickups_list->current();
                        $boxberry['point_id'] = $boxberry_object->getCode();
                        $boxberry['pickup_data'] = $this->getArrayPickupData($boxberry_object);
                        $boxberry['city'] = $city;
                    } else {
                        $boxberry['pickup_data'] = $this->getPickupDataById($boxberry['point_id']);
                        $boxberry['city'] = $city;
                    }

                    if (!empty($boxberry['point_id'])) {
                        $delivery_costs->setTarget($boxberry['point_id']);
                    }
                } else {
                    $boxberry = array();
                }

            } catch (Exception $e) {
                $boxberry = array();
                $response = array(
                    'cost' => false,
                    'delivery_time' => false,
                    'error' => $e->getMessage()
                );

                return $response;
            }
        }

        $package_size = $this->getSizePackage($package_info, $service_params);

        $delivery_costs->setWidth($package_size['width']);
        $delivery_costs->setHeight($package_size['height']);
        $delivery_costs->setDepth($package_size['length']);
        $delivery_costs->setOrdersum(round($package_info['C']));
        $delivery_costs->setDeliverysum(0);
        $delivery_costs->setCms('cscart');
        $delivery_costs->setVersion('1.1');

        if ($service_code == 'boxberry_self_prepaid' || $service_code == 'boxberry_courier_prepaid') {
            $delivery_costs->setPaysum(0);
        } else {
            $delivery_costs->setPaysum(round($package_info['C']));
        }

        $delivery_costs->setUrl(Registry::get('config.current_host'));
        try {
            /** @var DeliveryCosts $responseObject */
            $response_object = $this->client->execute($delivery_costs);
            $cost_received = $response_object->getPrice();
            $cost = fn_format_price_by_currency(round($cost_received), 'RUB', CART_PRIMARY_CURRENCY);
            $boxberry['cost'] = $cost;

            $surchages_settings_method = $this->client->getWidgetSettings();
            $surchages_settings = $this->client->execute($surchages_settings_method);

            $delivery_time = false;
            if (!$surchages_settings->getHide_delivery_day()) {
                $delivery = $response_object->getDeliveryPeriod();

                if ($delivery > 0) {
                    $delivery_time = __('n_days', array($delivery));
                }
            }

            $response = array(
                'cost' => $cost,
                'delivery_time' => $delivery_time,
                'error' => false
            );
        } catch (Exception $e) {
            $response = array(
                'cost' => false,
                'delivery_time' => false,
                'error' => $e->getMessage()
            );
        }

        return $response;
    }

    /**
     * Gets pickup point by point ID
     *
     * @param  int $point_id Response from Shipping service server
     * @return array List of pickup point available
     */
    protected function getPickupDataById($point_id = 0)
    {
        try {
            $pickup_object = $this->client->getPickupPoint($point_id);
            if ($pickup_object) {
                $pickup_data = $this->getArrayPickupData($pickup_object);
            } else {
                $pickup_data = false;
            }

        } catch (Exception $e) {
            $pickup_data = false;
        }

        return $pickup_data;
    }

    /**
     * Checks that the pickup point exists
     *
     * @param int $point_id Response from Shipping service server
     * @param ListPointsCollection The class with the pickup points
     *
     * @return bool true in point exists, false - otherwise
     */
    protected function isPointOnList($point_id, $pickups_list)
    {
        foreach ($pickups_list as $pickup) {
            if ($pickup->getCode() == $point_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets an array with the specific data of a pickup point
     *
     * @param Point $point The pickup point
     * @return array An array with the pickup point data
     */
    protected function getArrayPickupData(Point $point)
    {
        return array(
            'code' => $point->getCode(),
            'name' => $point->getName(),
            'address' => $point->getAddressReduce(),
            'full_address' => $point->getAddress(),
            'phone' => $point->getPhone(),
            'trip_description' => $point->getTripDescription(),
            'gps' => $point->getGps(),
            'metro' => $point->getMetro(),
            'work_time' => $point->getWorkSchedule(),
            'type' => $point->getTypeOfOffice(),
        );
    }

    /**
     * Prepare size of parcel
     *
     * @param array $package_info Package info with sizes, weight and amount of products calculated
     * @param array $shipping_settings The settings of the shipping method in CS-Cart
     *
     * @return array An array with sizes of parcel
     */
    protected function getSizePackage($package_info, $shipping_settings)
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

    /**
     * Checks whether to reset the pick-up point info or not
     *
     * @param array $boxberry Pick-up point information
     * @param ListPointsCollection $pickups_list The class with the pickup points
     * @param string $city Delivery destination city
     *
     * @return bool true for the pick-up point reset, false - otherwise
     */
    protected function needResetPickupPoint($boxberry, $pickups_list, $city)
    {
        $need = false;

        if (empty($boxberry['point_id'])) {
            $need = true;

        } elseif ($this->isPointOnList($boxberry['point_id'], $pickups_list)) {
            $need = false;

        } elseif (isset($boxberry['city']) && $boxberry['city'] != $city) {
            $need = true;
        }

        return $need;
    }
    /**
     * Fetches stored data from session
     *
     * @return array
     */
    protected function getStoredShippingData()
    {
        $group_key = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        if (isset(Tygh::$app['session']['cart']['shippings_extra']['boxberry'][$group_key][$shipping_id])) {
            return Tygh::$app['session']['cart']['shippings_extra']['boxberry'][$group_key][$shipping_id];
        }

        return [];
    }

    protected function isPickupService()
    {
        $service_code = isset($this->shipping_info['service_code']) ? $this->shipping_info['service_code'] : false;
        return !$service_code || in_array($service_code, ['boxberry_self', 'boxberry_self_prepaid']);
    }
}
