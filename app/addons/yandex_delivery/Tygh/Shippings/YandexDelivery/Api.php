<?php

namespace Tygh\Shippings\YandexDelivery;

use Tygh\Http;

use Tygh\Shippings\YandexDelivery\Objects\YandexObject;
use Tygh\Shippings\YandexDelivery\Objects\Order;
use Tygh\Shippings\YandexDelivery\Objects\RequestDeliveryList;
use Tygh\Shippings\YandexDelivery\Objects\Delivery;
use Tygh\Shippings\YandexDelivery\Objects\DeliveryPoint;
use Tygh\Shippings\YandexDelivery\Objects\Recipient;
use Tygh\Registry;

class Api extends YandexObject
{
    public $_data = array();
    public $_result = "";
    public $_error = "";
    public $_debug = "";

    public $client_id = 0;
    public $ids = array();
    public $sender_id = array();
    public $warehouse_id = array();
    public $requisite_id = array();
    public $available_city_from = array();
    public $is_logging_enabled = false;

    public $api_url = '';
    public $format = '';
    public $keys = array();

    public function __construct($settings)
    {
        foreach ($settings as $key => $value) {
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
    }

    public function checkInit()
    {
        return !empty($this->client_id) && !empty($this->sender_id) && !empty($this->warehouse_id) && !empty($this->requisite_id);
    }

    public function getSenderInfo($sender_id)
    {
        $this->_data = array(
            'sender_id' => $sender_id
        );

        return $this->request('getSenderInfo');
    }

    public function getSendersList()
    {
        $senders = $this->ids['senders'];
        $list = array();

        if (is_array($senders)) {
            foreach ($senders as $sender) {
                if (!empty($sender['name'])) {
                    $list[$sender['id']] = $sender['name'];

                } else {
                    $sender_data = $this->getSenderInfo($sender['id']);

                    if ($sender_data['status'] == 'ok') {
                        $list[$sender['id']] = $sender_data['data']['field_name'];
                    }
                }
            }
        }

        return $list;
    }

    public function getWarehouseInfo($sender_id = 0, $warehouse_id = 0)
    {
        $sender_id = (empty($sender_id)) ? $this->sender_id : $sender_id;
        $warehouse_id = (empty($warehouse_id)) ? $this->warehouse_id : $warehouse_id;

        if (empty($sender_id) || empty($warehouse_id)) {
            return false;
        }

        $this->_data = array(
            'warehouse_id' => $warehouse_id
        );

        if (!empty($sender_id)) {
            $this->_data['sender_id'] = $sender_id;
        }

        $response = $this->request('getWarehouseInfo');
        return $this->processResponse($response);
    }

    public function getWarehousesList($sender_id = 0)
    {
        $warehouses = $this->ids['warehouses'];
        $list = array();

        if (is_array($warehouses)) {
            foreach ($warehouses as $warehouse) {
                if (!empty($warehouse['name'])) {
                    $list[$warehouse['id']] = $warehouse['name'];
                } else {
                    if ($warehouse_data = $this->getWarehouseInfo($sender_id, $warehouse['id'])) {
                        $list[$warehouse['id']] = $warehouse_data['field_name'];
                    }
                }
            }
        }

        return $list;
    }

    public function getRequisiteInfo($sender_id, $requisite_id)
    {
        $sender_id = (empty($sender_id)) ? $this->sender_id : $sender_id;
        $requisite_id = (empty($requisite_id)) ? $this->requisite_id : $requisite_id;

        if (empty($sender_id) || empty($requisite_id)) {
            return false;
        }

        $this->_data = array(
            'requisite_id' => $requisite_id
        );

        if (!empty($sender_id)) {
            $this->_data['sender_id'] = $sender_id;
        }

        return $this->request('getRequisiteInfo');
    }

    public function getRequisiteList($sender_id = 0)
    {
        $requisites = $this->ids['requisites'];

        $list = array();
        if (is_array($requisites)) {
            foreach ($requisites as $requisite) {
                if (!empty($requisite['name'])) {
                    $list[$requisite['id']] = $requisite['name'];

                } else {
                    $requisite_data = $this->getRequisiteInfo($sender_id, $requisite['id']);

                    if ($requisite_data['status'] == 'ok') {
                        $data = reset($requisite_data['data']['requisites']);
                        $list[$requisite['id']] = $data['legal_form'] . " " . $data['legal_name'];
                    }
                }
            }
        }

        return $list;
    }

    public function getDeliveries($sender_id = 0)
    {
        $this->_data = array();

        if (!empty($sender_id)) {
            $this->_data['sender_id'] = $sender_id;
        }

        $deliveries_data = $this->request('getDeliveries');

        $deliveries = array();
        if ($deliveries_data['status'] == 'ok') {

            foreach ($deliveries_data['data']['deliveries'] as $delivery) {
                if ($delivery['type'] == 'delivery') {
                    $deliveries[$delivery['id']] = $delivery['name'];
                }
            }
        }

        return $deliveries;
    }

    public function getPaymentMethods()
    {
        $this->_data = array();

        return $this->request('getPaymentMethods');
    }

    public function getDeliveryMethods()
    {
        $this->_data = array();

        return $this->request('getDeliveryMethods');
    }

    public function searchDeliveryList($request, $deliverypoint = null)
    {
        $this->_data = array();

        if (!$request instanceof RequestDeliveryList) {
            $this->_error = YD_ERROR_WRONG_PARAM;

            return false;
        }

        if ($deliverypoint != null && !$deliverypoint instanceof DeliveryPoint) {
            $this->_error = YD_ERROR_WRONG_PARAM;

            return false;
        }

        if (!$request->validate()) {
            $this->_error = $request->_last_error;

            return false;
        }

        $request->appendToArray($this->_data, true);
        if ($deliverypoint != null) {
            $deliverypoint->appendToArray($this->_data, true);
        }


        $response = $this->request('searchDeliveryList');

        return $response;
    }

    public function getSenderOrders($orders = false, $real = 1, $sent = 1, $limit = 10, $deliveries = array(), $shops = array(), $statuses = array())
    {
        $this->_data = array();

        $this->_data['real'] = $real;
        $this->_data['sent'] = $sent;
        $this->_data['limit'] = $limit;
        $this->_data['deliveries'] = implode(',', $deliveries);
        $this->_data['shops'] = implode(',', $shops);
        $this->_data['statuses'] = implode(',', $statuses);

        if (is_array($orders)) {
            $this->_data['order_ids'] = implode(',', $orders);

        } elseif (is_numeric($orders)) {
            $this->_data['order_ids'] = $orders;

        } elseif ($orders === false) {
            $this->_data['order_ids'] = '';

        } else {
            $this->_error = YD_ERROR_WRONG_PARAM;
            return false;
        }

        return $this->request('getSenderOrders');
    }

    public function getSenderOrderStatus($order_id, $force = false)
    {
        $this->_data = array(
            'order_id' => $order_id,
        );

        $status_data =  $this->request('getSenderOrderStatus', $force);

        return $this->processResponse($status_data);
    }

    public function getSenderOrderStatuses($order_id, $force = false)
    {
        $this->_data = array(
            'order_id' => $order_id,
        );

        $statuses_data = $this->request('getSenderOrderStatuses', $force);

        return $this->processResponse($statuses_data, array('data', 'data'));
    }

    public function createOrder($order, $recipient, $delivery, $delivery_point)
    {
        $this->_data = array();

        if (!$order instanceof Order or !$recipient instanceof Recipient or !$delivery instanceof Delivery or !$delivery_point instanceof DeliveryPoint) {
            $this->_error = YD_ERROR_WRONG_PARAM;

            return false;
        }

        if (!$order->validate()) {
            if ($order->_wrongItem) {
                $this->_error = $order->_wrongItem->_last_error;

            } else {
                $this->_error = $order->_last_error;
            }

            return false;
        }

        if (!$recipient->validate($order)) {
            $this->_error = $recipient->_last_error;

            return false;
        }

        if (!$delivery->validate($order)) {
            $this->_error = $delivery->_last_error;

            return false;
        }

        if (!$delivery->pickuppoint && !$delivery_point->validate($order)) {
            $this->_error = $delivery_point->_last_error;

            return false;
        }

        $order->appendToArray($this->_data, true);
        $recipient->appendToArray($this->_data, true);
        $delivery->appendToArray($this->_data, true);
        $delivery_point->appendToArray($this->_data, true);

        $order_data = $this->request('createOrder', true);

        return $this->processResponse($order_data, array('data', 'order'));
    }

    public function confirmSenderOrders($yandex_data)
    {
        $this->_data = array(
            'order_ids' => $yandex_data['yandex_order_id'],
            'shipment_date' => $yandex_data['date'],
            'type' => $yandex_data['type']
        );

        $request_data = $this->request('confirmSenderOrders', true);

        return $this->processResponse($request_data, array('data', 'result'));
    }

    public function editOrder($order, $recipient, $delivery, $delivery_point)
    {
        $this->_data = array();

        if (!$order instanceof Order or !$recipient instanceof Recipient or !$delivery instanceof Delivery or !$delivery_point instanceof DeliveryPoint) {
            $this->_error = YD_ERROR_WRONG_PARAM;

            return false;
        }

        if (!$order->validate()) {
            if ($order->_wrongItem) {
                $this->_error = $order->_wrongItem->_last_error;
            } else {
                $this->_error = $order->_last_error;
            }

            return false;
        }

        if (!$recipient->validate()) {
            $this->_error = $recipient->_last_error;

            return false;
        }

        if (!$delivery->validate()) {
            $this->_error = $delivery->_last_error;

            return false;
        }

        if (!$delivery->pickuppoint && !$delivery_point->validate()) {
            $this->_error = $delivery_point->_last_error;

            return false;
        }

        $order->appendToArray($this->_data, true);
        $recipient->appendToArray($this->_data, true);
        $delivery->appendToArray($this->_data, true);
        $delivery_point->appendToArray($this->_data, true);

        return $this->request('editOrder', true);
    }

    public function getOrderInfo($yandex_order_id, $force = false)
    {
        $this->_data = array(
            'order_id' => $yandex_order_id
        );

        $request_data = $this->request('getOrderInfo', $force);

        return $this->processResponse($request_data, array('data'));
    }

    public function deleteOrder($yandex_order_id)
    {
        $this->_data = array(
            'order_id' => $yandex_order_id
        );

        $request_data = $this->request('deleteOrder', true);

        return $this->processResponse($request_data, array('data'));
    }

    public function getSenderOrderLabel($order_id, $return = false)
    {
        $this->_data = array();
        $this->_data['order_id'] = $order_id;
        $response = $this->request('getSenderOrderLabel');

        if ($response->status == 'ok') {
            $file = base64_decode($response->data);
            if ($return) {
                return $file;
            } else {
                $this->_echoLabel($file, 'label_' . $order_id);
            }
        } else {
            return false;
        }

        return false;
    }

    public function getSenderParcelLabel($parcel_id, $return = false)
    {
        $this->_data = array();
        $this->_data['parcel_id'] = $parcel_id;
        $response = $this->request('getSenderParcelLabel');
        if ($response->status == 'ok') {
            $file = base64_decode($response->data);
            if ($return) {
                return $file;
            } else {
                $this->_echoLabel($file, 'docs_' . $parcel_id);
            }
        } else {
            return false;
        }

        return false;
    }

    public function getIndex($address)
    {
        $address = preg_split('/[ ,-]+/', trim($address));
        $address = implode('+', $address);

        $key_address = md5($address);
        $response = fn_get_session_data($key_address);

        if (empty($response)) {
            $url = "https://geocode-maps.yandex.ru/1.x/";
            $data = array(
                'geocode' => $address,
                'format' => 'json',
                'results' => 1
            );

            $back_logging = Http::$logging;
            Http::$logging = $this->is_logging_enabled;

            $response = Http::post($url, $data, array(
                'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
            ));

            Http::$logging = $back_logging;

            fn_set_session_data($key_address, $response, YD_CACHE_DAY);
        }

        $response = json_decode($response, true);
        $address_line = $this->findElmArray($response, 'AddressLine');
        $this->_data = array(
            'address' => reset($address_line)
        );

        $index_data = $this->request('getIndex');

        return $this->processResponse($index_data);
    }

    /**
     * Request the geographic coordinates of an address from Yandex
     *
     * @param  array $address The array with the address information
     * @return array The most probable geographic coordinates of the address
     */
    public function getGeoByAddress($address)
    {
        $url = "https://geocode-maps.yandex.ru/1.x/";
        $settings = Registry::get('addons.geo_maps');
        $data = array(
            'geocode' => implode('+', $address),
            'format' => 'json',
            'results' => 2,
            'sco' => 'longlat',
            'apikey' => isset($settings['yandex_api_key']) ? $settings['yandex_api_key'] : '',
        );

        $back_logging = Http::$logging;
        Http::$logging = $this->is_logging_enabled;

        $response = Http::get($url, $data, array(
            'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
        ));
        Http::$logging = $back_logging;

        $response = json_decode($response, true);
        $response = isset($response['response']['GeoObjectCollection']) ? $response['response']['GeoObjectCollection'] : null;

        $ll_address = false;
        if ($response && $response['metaDataProperty']['GeocoderResponseMetaData']['found'] > 0) {
            $object = reset($response['featureMember']);
            $object = $object['GeoObject'];

            $ll_address = explode(' ', $object['Point']['pos']);
        }

        return $ll_address;
    }
  
    public function autocomplete($term, $type, $city_name = '')
    {
        $this->_data = array(
            'type' => $type,
            'term' => $term,
            'locality_name' => $city_name
        );

        $autocomplete_data = $this->request('autocomplete');

        return $this->processResponse($autocomplete_data, array('data', 'suggestions'));
    }

    /**
     * Get the unique identifier of the city from Yandex by the name of the city and its region.
     *
     * @param array $package_info Package info, which includes the addresses of the store and customer.
     * @return int The unique identifier of the city (GeoID).
     */
    public function getGeoID($package_info)
    {
        $condition = '';
        $city = !empty($package_info['location']['city']) ? $package_info['location']['city'] : '';
        $state = empty($package_info['location']['state']) ? '' : $package_info['location']['state'];

        if (!empty($package_info['location']['state'])) {
            $condition .= db_quote('AND yd_state = ?s', $package_info['location']['state']);
            $state_name = fn_get_state_name($state, $package_info['location']['country']);
        }

        $geo_id = db_get_field(
            'SELECT yd_geo_id FROM ?:yd_geo WHERE yd_city = ?s ?p',
            $city, $condition
        );

        if (isset($state_name)) {
            $address = $state_name . ', ' . $city;

        } elseif (!empty($package_info['location']['state_descr'])) {
            $address = $package_info['location']['state_descr'] . ', ' . $city;

        } else {
            $address = $city;
        }

        $result = $this->autocomplete($address, 'locality');

        if ($result) {
            $city_data = reset($result);

            $geo_id = $city_data['geo_id'];
            $data = array(
                'yd_state' => $state,
                'yd_city' => $city,
                'yd_geo_id' => $geo_id
            );

            db_replace_into('yd_geo', $data);

        } else {
            $geo_id = empty($geo_id) ? 0 : $geo_id;
        }

        return $geo_id;
    }

    public function _echoLabel($file, $filename)
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Length: ' . (function_exists('mb_strlen') ? mb_strlen($file, '8bit') : strlen($file)));
        header("Content-Disposition: attachment; filename=\"$filename.pdf\"");
        header('Content-Transfer-Encoding: binary');
        
        echo $file;
    }
  
    public function sign($method)
    {
        $hash = '';
      
        $this->_data['secret_key'] = '';
        $this->_data['format'] = $this->format;
        $this->_data['client_id'] = $this->client_id;
      
        $keys = array_keys($this->_data);
        sort($keys);

        foreach ($keys as $key) {
            if (!is_array($this->_data[$key])) {
                $hash .= $this->_data[$key];
            } else {
                $subkeys = array_keys($this->_data[$key]);
                sort($subkeys);
                foreach ($subkeys as $subkey) {
                    if (!is_array($this->_data[$key][$subkey])) {
                        $hash .= $this->_data[$key][$subkey];
                    } else {
                        $subsubkeys = array_keys($this->_data[$key][$subkey]);
                        sort($subsubkeys);
                        foreach ($subsubkeys as $subsubkey) {
                            if (!is_array($this->_data[$key][$subkey][$subsubkey])) {
                                $hash .= $this->_data[$key][$subkey][$subsubkey];
                            }
                        }
                    }
                }
            }
        }

        $hash .= $this->keys[$method];
        $hash = md5($hash);

        $this->_data['secret_key'] = $hash;
    }
  
    public function request($method, $force = false)
    {
        $this->_result = false;

        if (empty($this->client_id)) {
            fn_set_notification('W', __('yandex_delivery.error_yandex_delivery'), __("yandex_delivery.text_error_config", array(
                '[param]' => 'client_id'
            )));
            return false;

        } elseif (empty($this->keys[$method]) ) {
            fn_set_notification('W', __('yandex_delivery.error_yandex_delivery'), __("yandex_delivery.text_error_config", array(
                '[param]' => $method
            )));
            return false;
        }

        if (empty($this->_data['client_id']) && !empty($this->client_id)) {
            $this->_data['client_id'] = $this->client_id;
        }

        if (empty($this->_data['sender_id']) && !empty($this->sender_id)) {
            $this->_data['sender_id'] = $this->sender_id;
        }

        $this->sign($method);

        $back_logging = Http::$logging;
        Http::$logging = $this->is_logging_enabled;

        $curl_answer = Http::post($this->api_url . $method, $this->_data, array(
            'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
        ));

        Http::$logging = $back_logging;

        $this->_result = json_decode($curl_answer, true);

        return $this->_result;
    }
    
    public function getSessionKey($method)
    {
        return $this->sign($method);
    }

    protected function findElmArray($ar, $searchfor)
    {
        static $result = array();

        foreach($ar as $index => $data) {
            if (is_string($index) && $index == $searchfor) {
                $result[] = $data;
            }

            if (is_array($ar[$index])) {
                $this->findElmArray($data, $searchfor);
            }
        }
        return $result;
    }

    public function processResponse($response, $levels =  array('data'))
    {
        if (isset($response['status']) && $response['status'] == 'ok') {
            $result = $response;
            foreach ($levels as $level) {

                if (!empty($result[$level])) {
                    $result = $result[$level];

                } else {
                    $result = false;
                    break;
                }
            }
        } else {
            $result = false;

            if ($response['status'] == 'error' && !empty($response['data']['errors'])) {
                foreach ($response['data']['errors'] as $error) {
                    fn_set_notification('W', __('yandex_delivery.error_yandex_delivery'), $error);
                }
            }
        }

        return $result;
    }
}
