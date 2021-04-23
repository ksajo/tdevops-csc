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

namespace Tygh\Addons\YandexDelivery\Services;

use Tygh\Addons\YandexDelivery\Api\Client;
use Tygh\Common\OperationResult;
use Tygh\Http;

class YandexDeliveryService
{
    const MODULE = 'yandex_delivery';

    const CACHE_KEY = 'yd_cache_v3';

    const CACHE_PERIOD = 60 * 10;

    /** @var \Tygh\Addons\YandexDelivery\Api\Client */
    public $client;

    /** @var string $cabinet_id */
    protected $cabinet_id;

    /** @var array<string> $warehouse_ids */
    protected $warehouse_ids;

    /** @var array<string> $store_ids */
    protected $store_ids;

    /** @var bool $is_orders_publish */
    protected $is_orders_publish;

    /**
     * YandexDeliveryService constructor.
     *
     * @param string $oauth_key      OAuth authorization key.
     * @param string $cabinet_id     Shop id in Yandex.Delivery.
     * @param string $store_ids      Identifiers of stores in Yandex.Delivery service.
     * @param string $warehouse_ids  Identifiers of warehouses in Yandex.Delivery service.
     * @param bool   $confirm_orders Flag that order would be confirming into Yandex.Delivery service.
     */
    public function __construct($oauth_key, $cabinet_id, $store_ids, $warehouse_ids, $confirm_orders)
    {
        $this->cabinet_id = $cabinet_id;
        $this->client = new Client($oauth_key);
        $this->is_orders_publish = $confirm_orders;

        $this->store_ids = $this->getFieldsIds($store_ids);
        $this->warehouse_ids = $this->getFieldsIds($warehouse_ids);
    }

    /**
     * Transforms data from addon settings to array of ids and name of warehouses/stores.
     *
     * @param string $data Input from text area at addon settings page.
     *
     * @return array
     */
    protected function getFieldsIds($data)
    {
        if (empty($data)) {
            return null;
        }
        $objects = explode("\r\n", $data);
        $result = [];
        foreach ($objects as $object) {
            $object_parts = explode('-', $object);
            if (!is_numeric($object_parts[0])) {
                continue;
            }
            $result[$object_parts[0]] = [
                'id'   => $object_parts[0],
                'name' => isset($object_parts[1]) ? $object_parts[1] : $object_parts[0],
            ];
        }
        return $result;
    }

    /**
     * Returns status of auto publishing orders into Yandex.Delivery
     *
     * @return bool
     */
    public function getOrdersPublishStatus()
    {
        return $this->is_orders_publish;
    }

    /**
     * Gets location objects that connected to specified address.
     *
     * @param string $term Address.
     *
     * @return array<string, array<string, string|int>>|string
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/get-location-docpage/
     */
    public function getLocation($term)
    {
        $method_name = 'location';
        $method_type = Http::GET;
        $data = [
            'term' => $term
        ];
        return $this->client->request($method_name, $method_type, $data);
    }

    /**
     * Gets all delivery services that is available to specified cabinet.
     *
     * @return array<string, string>
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/get-delivery-services-docpage/
     */
    public function getDeliveryServices()
    {
        if (empty($this->cabinet_id)) {
            return [];
        }
        $method_name = 'delivery-services';
        $method_type = Http::GET;
        $data = [
            'cabinetId' => $this->cabinet_id
        ];
        return $this->client->request($method_name, $method_type, $data);
    }

    /**
     * Gets all information about pickup points from specified location.
     *
     * @param array<int|string> $pickup_point_ids List of pickup points identifiers.
     *
     * @return array<string, array<string, string>>
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/put-pickup-points-docpage/
     */
    public function putPickupPoints(array $pickup_point_ids)
    {
        $method_name = 'pickup-points';
        $method_type = Http::PUT;
        return $this->client->multiRequest($method_name, $method_type, $pickup_point_ids);
    }

    /**
     * Returns all delivery options for product of specified size to specified location.
     *
     * @param int                                                                       $sender_id Sender identifier.
     * @param array{geoId?: int|string, location?: string, pickupPointIds?: array{int}} $to        Target location of delivery.
     * @param array<string, string>                                                     $params    Optional parameters for request.
     *
     * @psalm-param array{
     *                  cost?: array{
     *                      assessedValue: float|int|string,
     *                      fullyPrepaid: bool,
     *                      itemsSum: string,
     *                      manualDeliveryForCustomer: string
     *                  },
     *                  shipment?: array{
     *                      date: false|string,
     *                      includeNonDefault: bool,
     *                      type: string
     *                  },
     *                  tariffId: int
     *              } $params
     *
     * @return array<string, string>
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/put-delivery-options-docpage/
     */
    public function putDeliveryOptions($sender_id, array $to, array $params = [])
    {
        $method_name = 'delivery-options';
        $method_type = Http::PUT;
        $data = [
            'senderId'   => $sender_id,
            'to'         => $to,
        ];
        if (!empty($params)) {
            $data = fn_array_merge($data, $params, true);
        }
        return $this->client->request($method_name, $method_type, $data);
    }


    /**
     * Makes postOrders request.
     *
     * @param int                                  $sender_id     Sender identifier.
     * @param string                               $delivery_type Type of delivery.
     * @param array<string, array<string, string>> $params        Optional parameters for request.
     *
     * @return array<string, string|int>|string
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/post-orders.html/
     */
    public function postOrders($sender_id, $delivery_type, array $params = [])
    {
        $method_name = 'orders';
        $method_type = Http::POST;
        $data = [
            'senderId'     => $sender_id,
            'deliveryType' => $delivery_type,
        ];
        if (!empty($params)) {
            $data = fn_array_merge($data, $params, true);
        }

        return $this->client->request($method_name, $method_type, $data);
    }

    /**
     * Publish specified Yandex.Delivery drafts.
     *
     * @param array<int> $order_ids Specified Yandex.Delivery orders in DRAFT status.
     *
     * @return OperationResult
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/post-orders-submit.html
     */
    public function postOrdersSubmit(array $order_ids)
    {
        $result = new OperationResult(true);
        $method_name = 'orders/submit';
        $method_type = Http::POST;
        $data = [
            'orderIds' => $order_ids,
        ];
        $answer = $this->client->request($method_name, $method_type, $data);
        if (is_array($answer) && count($order_ids) === 1) {
            $answer = reset($answer);
        }
        $result = $this->processErrors($result, $answer);
        $result->setData($answer);
        return $result;
    }

    /**
     * Gets information about Yandex.Delivery order by its order identifier.
     *
     * @param int $order_id Yandex.Delivery order identifier.
     *
     * @return array
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/get-orders-id.html/
     */
    public function getOrdersId($order_id)
    {
        $method_name = 'orders/' . $order_id;
        $method_type = Http::GET;

        return $this->client->request($method_name, $method_type);
    }

    /**
     * Sets 'Cancelled' status to Yandex.Delivery order.
     *
     * @param int $order_id Yandex.Delivery order id.
     *
     * @return array|string
     *
     * @see https://yandex.ru/dev/delivery-3/doc/dg/reference/delete-orders-id.html
     */
    public function deleteOrdersId($order_id)
    {
        $method_name = 'orders/' . $order_id;
        $method_type = Http::DELETE;

        return $this->client->request($method_name, $method_type);
    }

    /**
     * Creates shipment for required
     *
     * @param array<string, string> $shipment Information about shipment.
     * @param array<string, string> $params   Additional params for request.
     *
     * @return array<string, string>|string
     */
    public function postShipmentsApplication(array $shipment, array $params)
    {
        $method_name = 'shipments/application';
        $method_type = Http::POST;
        $data = [
            'cabinetId' => $this->cabinet_id,
            'shipment'  => $shipment,
        ];
        if (!empty($params)) {
            $data = fn_array_merge($data, $params, true);
        }

        return $this->client->request($method_name, $method_type, $data);
    }

    /**
     * Gets working period of specified warehouse at specified date.
     *
     * @param int    $warehouse_id Warehouse id into Yandex.Delivery.
     * @param string $date         Specified date format Y-m-d.
     *
     * @return array<string, string>|string
     */
    public function getShipmentsIntervals($warehouse_id, $date)
    {
        $method_name = 'shipments/intervals/import';
        $method_type = Http::GET;
        $data = [
            'warehouseId' => $warehouse_id,
            'date'        => $date,
        ];
        return $this->client->request($method_name, $method_type, $data);
    }

    /**
     * Returns all warehouses identifiers.
     *
     * @return array<string>
     */
    public function getWarehouses()
    {
        return $this->warehouse_ids;
    }

    /**
     * Returns all store identifiers.
     *
     * @return array<string>
     */
    public function getStores()
    {
        return $this->store_ids;
    }

    /**
     * Process error message from Yandex.Delivery API requests.
     *
     * @param OperationResult       $result Currently processing operation.
     * @param array<string, string> $answer Answer data from Yandex.Delivery API.
     *
     * @psalm-param array{
     *          message?: string,
     *          errors?: array{
     *                      errorCode?: string,
     *                      field: string,
     *                      message: string
     *                   },
     *          violations?: array{
     *                          errorCode?: string,
     *                          field: string,
     *                          message: string
     *                      },
     *        } $answer
     *
     * @return OperationResult
     */
    public function processErrors(OperationResult $result, array $answer)
    {
        if (isset($answer['message'])) {
            $result->setSuccess(false);
            $error_code = isset($answer['type']) ? $answer['type'] : '0';
            $result->addError($error_code, $answer['message']);
            return $result;
        }
        if (isset($answer['errors'])) {
            $result->setSuccess(false);
            foreach ($answer['errors'] as $code => $error) {
                $error_code = isset($error['errorCode']) ? $error['errorCode'] : $code;
                if (is_array($error)) {
                    foreach ($error as $field => $message) {
                        if (is_array($message)) {
                            $message = implode(', ', $message);
                        }
                        $result->addError($error_code . '_' . $field, $field . ' - ' . $message);
                    }
                } else {
                    $result->addError($error_code, $error['field'] . ' - ' . $error['message']);
                }
            }
        }
        if (isset($answer['violations'])) {
            $result->setSuccess(false);
            foreach ($answer['violations'] as $code => $violation) {
                $error_code = isset($violation['errorCode']) ? $violation['errorCode'] : $code;
                $result->addError($error_code, $violation['field'] . ' - ' . $violation['message']);
            }
        }
        return $result;
    }
}
