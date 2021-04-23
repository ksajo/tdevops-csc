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

use Tygh\Addons\YandexDelivery\Enum\DeliveryType;
use Tygh\Addons\YandexDelivery\Enum\PaymentMethod;
use Tygh\Addons\YandexDelivery\Enum\Taxes;

class OrderDetailsBuilder
{
    /** @var YandexDeliveryService $client */
    protected $client;

    /** @var ShippingService $shipping_service */
    protected $shipping_service;

    /** @var array<string> $settled_statuses */
    protected $settled_statuses;

    /**
     * OrderDetailsBuilder constructor.
     *
     * @param YandexDeliveryService $client           Object with methods for Yandex.Delivery API.
     * @param ShippingService       $shipping_service Service with shipping processing methods.
     * @param array<string>         $settled_statuses Order statuses that represent settled state.
     */
    public function __construct(YandexDeliveryService $client, ShippingService $shipping_service, array $settled_statuses)
    {
        $this->client = $client;
        $this->shipping_service = $shipping_service;
        $this->settled_statuses = $settled_statuses;
    }

    /**
     * Builds Recipient part of creation order parameters request.
     *
     * @param array<string, string>                                                             $data       Information about Yandex.Delivery order.
     * @param array<string, array<string, array<string, array<string, array<string, string>>>>> $order_info Information about platform order.
     *
     * @return array<string, string|array<string, string>>
     *
     * @psalm-return array{
     *                  address: array{
     *                      apartment?: string,
     *                      country: string,
     *                      geoId?: int|string,
     *                      house?: string,
     *                      locality: string,
     *                      postalCode: string,
     *                      region: string,
     *                      street?: string
     *                  },
     *                  firstName?: string,
     *                  lastName?: string,
     *                  pickupPointId?: int
     *              }
     */
    public function buildOrderRecipient(array $data, array $order_info)
    {
        $product_groups = reset($order_info['product_groups']);
        $package_info = $product_groups['package_info'];
        /** @var string $city_to */
        $city_to = $package_info['location']['city'];
        $state = $package_info['location']['state'];
        $country = $package_info['location']['country'];
        /** @var string $zipcode */
        $zipcode = $package_info['location']['zipcode'];
        $location_term = $city_to . ', ' . fn_get_state_name($state, $country);

        $country = fn_get_countries_name($country);
        /** @var array<string, string> $country */
        $country = reset($country);
        /** @var string $region */
        $region = fn_get_state_name($state, $country['code']);
        $result = [
            'address'   => [
                'country'    => $country['country'],
                'region'     => $region,
                'locality'   => $city_to,
                'postalCode' => $zipcode
            ]
        ];
        if (isset($data['first_name'])) {
            $result['firstName'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $result['lastName'] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $result['email'] = $data['email'];
        }

        $locations = $this->client->getLocation($location_term);
        if (is_array($locations)) {
            $location = reset($locations);
        }
        if (!empty($location)) {
            $result['address']['geoId'] = (int) $location['geoId'];
        }

        $shipping = reset($order_info['shipping']);
        switch ($shipping['service_params']['type_delivery']) {
            case DeliveryType::PICKUP:
                $result['pickupPointId'] = (int) $shipping['point_id'];
                break;
            case DeliveryType::COURIER:
                if (isset($data['street'])) {
                    $result['address']['street'] = $data['street'];
                }
                if (isset($data['house'])) {
                    $result['address']['house'] = $data['house'];
                }
                if (isset($data['apartment'])) {
                    $result['address']['apartment'] = $data['apartment'];
                }
                break;
            case DeliveryType::POST:
            default:
                break;
        }
        return $result;
    }

    /**
     * Builds Contacts part of creation order parameters request.
     *
     * @param array<string, string> $data Information about Yandex.Delivery order.
     *
     * @return array<int, array<string, string>>
     */
    public function buildOrderContacts(array $data)
    {
        $result = [[]];
        if (empty($data)) {
            return $result;
        }
        if (isset($data['first_name'])) {
            $result[0]['firstName'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $result[0]['lastName'] = $data['last_name'];
        }
        if (isset($data['phone'])) {
            $result[0]['phone'] = $data['phone'];
        }
        if (!empty($result)) {
            $result[0]['type'] = 'RECIPIENT';
        }
        return $result;
    }

    /**
     * Builds Cost part of creation order parameters request.
     *
     * @param array<string, string>                $data             Information about Yandex.Delivery order.
     * @param array<string, array<string, string>> $delivery_options Available delivery options.
     * @param string                               $status           Order status.
     *
     * @return array<string, string|float|bool>
     */
    public function buildOrderCost(array $data, array $delivery_options, $status)
    {
        return [
            'assessedValue'             => (float) $data['assessed_value'],
            'paymentMethod'             => PaymentMethod::PREPAID,
            'manualDeliveryForCustomer' => $delivery_options['cost']['deliveryForCustomer'],
            'fullyPrepaid'              => in_array($status, $this->settled_statuses, true),
        ];
    }

    /**
     * Builds DeliveryOption part of creation order parameters request.
     *
     * @param array<string, array<string, string>|string> $order_info       Information about platform order.
     * @param array<string, array<string, string>>        $delivery_options Available delivery options.
     *
     * @return array<string, string|array<string, string>>
     */
    public function buildOrderDeliveryOption(array $order_info, array $delivery_options)
    {
        if (is_array($order_info['shipping'])) {
            $shipping = reset($order_info['shipping']);
        } else {
            $shipping = $order_info['shipping'];
        }

        return [
            'tariffId'                  => $shipping['delivery']['tariffId'],
            'delivery'                  => $order_info['shipping_cost'],
            'deliveryForCustomer'       => $delivery_options['cost']['deliveryForCustomer'],
            'partnerId'                 => $shipping['delivery']['delivery']['partner']['id'],
            'calculatedDeliveryDateMin' => $delivery_options['delivery']['calculatedDeliveryDateMin'],
            'calculatedDeliveryDateMax' => $delivery_options['delivery']['calculatedDeliveryDateMax'],
            'services'                  => $delivery_options['services'],
        ];
    }

    /**
     * Builds Shipment part of creation order parameters request.
     *
     * @param array<string, string>                $data             Information about Yandex.Delivery order.
     * @param array<string, string>                $order_info       Information about platform order.
     * @param array<string, array<string, string>> $delivery_options Available delivery options.
     *
     * @return array<string, string>
     */
    public function buildOrderShipment(array $data, array $order_info, array $delivery_options)
    {
        $shipping = reset($order_info['shipping']);
        $shipping_shipment = reset($shipping['delivery']['shipments']);
        $delivery_options_shipment = reset($delivery_options['shipments']);
        $result = [
            'type'          => isset($data['type']) ? strtoupper($data['type']) : $shipping_shipment['type'],
            'partnerTo'     => $delivery_options_shipment['partner']['id'],
            'date'          => $delivery_options_shipment['date'],
            'warehouseFrom' => $shipping['service_params']['warehouse_id'],
        ];
        if (isset($data['type']) && $data['type'] === 'import') {
            $result['warehouseTo'] = $delivery_options_shipment['warehouse']['id'];
        }
        return $result;
    }

    /**
     * Builds Places part of creation order parameters request.
     *
     * @param array<string, string> $order_info    Information about platform order.
     * @param array<string, string> $shipment_info Information about shipment associated with future Yandex.Delivery order.
     * @param array<string, string> $auth          Authentication information.
     *
     * @return array<int|string, array<string, string|array<string, string>>>
     *
     * @psalm-return array{
     *                  array{
     *                      dimensions: array{
     *                          height: float|int,
     *                          length: float|int,
     *                          weight: string,
     *                          width: float|int
     *                      },
     *                      items?: non-empty-list<array{
     *                          assessedValue: int,
     *                          count: int,
     *                          dimensions: array{
     *                              height: float,
     *                              length: float,
     *                              weight: float,
     *                              width: float
     *                          },
     *                          name: string,
     *                          price: float
     *                      }>
     *                  }
     *              }|array
     */
    public function buildOrderPlaces(array $order_info, array $shipment_info, array $auth = [])
    {
        $shipping = reset($order_info['shipping']);
        $product_groups = reset($order_info['product_groups']);
        $service_params = $shipping['service_params'];
        $package_size = $this->shipping_service->calculateDimensions($product_groups['package_info'], $service_params);
        $weight = (float) sprintf('%.3f', round((float) $product_groups['package_info']['W'], 3));
        $result = [
            [
                'dimensions' => [
                    'length' => $package_size['length'],
                    'width'  => $package_size['width'],
                    'height' => $package_size['height'],
                    'weight' => $weight,
                ],
            ]
        ];
        if (
            !$this->shipping_service->isProductDimensionsExist(
                [
                    (float) $package_size['length'],
                    (float) $package_size['width'],
                    (float) $package_size['height'],
                    $weight
                ]
            )
        ) {
            return [];
        }
        if (empty($shipment_info)) {
            return $result;
        }
        $shipment = reset($shipment_info);
        foreach ($order_info['products'] as $item_id => $product) {
            if (!isset($shipment['products'][$item_id])) {
                continue;
            }
            $product_data = fn_get_product_data($product['product_id'], $auth);
            if (
                !$this->shipping_service->isProductDimensionsExist(
                    [
                        (float) $product_data['box_length'],
                        (float) $product_data['box_height'],
                        (float) $product_data['box_width'],
                        (float) $product_data['weight']
                    ]
                )
            ) {
                return [];
            }
            $tax = fn_get_tax(reset($product_data['tax_ids']));
            $item = [
                'externalId'    => $product['product_code'],
                'name'          => (string) $product['product'],
                'count'         => (int) $shipment['products'][$item_id],
                'price'         => (float) $product['price'],
                'assessedValue' => (int) $product['price'],
                'dimensions' => [
                    'length' => (float) $product_data['box_length'],
                    'width'  => (float) $product_data['box_width'],
                    'height' => (float) $product_data['box_height'],
                    'weight' => (float) $product_data['weight'],
                ],
            ];
            if (isset($tax['tax_type'])) {
                $item['tax'] = Taxes::getTax($tax['tax_type']);
            }
            $result[0]['items'][] = $item;
        }
        return $result;
    }

    /**
     * Creates putDeliveryOption request for additional data for order request.
     *
     * @param array<string, string>           $order_info Information about platform order.
     * @param array<string, string|int|float> $data       Information about future Yandex.Delivery order.
     *
     * @return array<string, string>
     */
    public function createPutDeliveryOptionsRequest(array $order_info, array $data)
    {
        $shipping = reset($order_info['shipping']);
        $locations = $this->client->getLocation($order_info['s_city'] . ', ' . fn_get_state_name($order_info['s_state'], $order_info['s_country']));
        if (empty($locations)) {
            return [];
        }
        $location = reset($locations);
        if (!isset($location['geoId'])) {
            return [];
        }
        $service_params = $shipping['service_params'];
        $product_groups = reset($order_info['product_groups']);
        $places = $this->shipping_service->formatPackagesDimensions($product_groups['package_info']);

        $sender_id = (int) $service_params['sender_id'];
        $to = [
            'geoId' => $location['geoId'],
        ];
        if ($service_params['type_delivery'] === DeliveryType::PICKUP) {
            $to['pickupPointIds'] = [
                (int) $shipping['point_id'],
            ];
        }
        $shipment = [
            'date' => isset($data['date']) ? date('Y-m-d', fn_parse_date($data['date'])) : date('Y-m-d', fn_parse_date(TIME)),
            'type' => isset($data['type']) ? strtoupper($data['type']) : 'WITHDRAW',
            // Change this for true, when module will be ready for ignoring Yandex.Delivery cabinet settings.
            'includeNonDefault' => false,
        ];
        return $this->client->putDeliveryOptions(
            $sender_id,
            $to,
            [
                'cost' => [
                    'assessedValue' => $data['assessed_value'],
                    'itemsSum'      => $order_info['subtotal'],
                    'fullyPrepaid'  => in_array($order_info['status'], $this->settled_statuses),
                    'manualDeliveryForCustomer' => $order_info['shipping_cost'],
                ],
                'shipment' => $shipment,
                'tariffId' => (int) $shipping['delivery']['tariffId'],
                'places'   => $places,
            ]
        );
    }

    /**
     * Creates shipment for import type delivery.
     *
     * @param array<string, string> $order_info Information about order.
     * @param array<string, string> $data       Information about preparing Yandex.Delivery order.
     *
     * @return array<string, string>|string|null
     */
    public function createPostShipmentRequest(array $order_info, array $data)
    {
        $shipping = reset($order_info['shipping']);
        $shipping_shipment = reset($shipping['delivery']['shipments']);
        if (!isset($shipping_shipment['warehouse'])) {
            return null;
        }
        $warehouse_to = $shipping_shipment['warehouse']['id'];
        $date = date('Y-m-d', fn_parse_date($data['date']));
        $intervals = $this->client->getShipmentsIntervals($warehouse_to, $date);
        $product_groups = reset($order_info['product_groups']);
        $service_params = $shipping['service_params'];
        $package_size = $this->shipping_service->calculateDimensions($product_groups['package_info'], $service_params);
        $weight = sprintf('%.3f', round((float) $product_groups['package_info']['W'], 3));
        $shipment = [
            'type'          => 'IMPORT',
            'date'          => $date,
            'warehouseFrom' => $shipping['service_params']['warehouse_id'],
            'warehouseTo'   => $warehouse_to,
            'partnerTo'     => $shipping_shipment['partner']['id'],
        ];
        $courier = [
            'type'       => strtoupper($data['courier']['type']),
            'firstName'  => $data['courier']['first_name'],
            'middleName' => '',
            'lastName'   => $data['courier']['last_name'],
            'phone'      => $data['courier']['phone'],
        ];
        if ($courier['type'] === 'CAR') {
            $courier['carBrand'] = $data['courier']['car_brand'];
            $courier['carNumber'] = $data['courier']['car_number'];
        }

        $params = [
            'intervalId' => reset($intervals)['id'],
            'courier'    => $courier,
            'dimensions' => [
                'length' => $package_size['length'],
                'width'  => $package_size['width'],
                'height' => $package_size['height'],
                'weight' => $weight,
            ],
        ];
        return $this->client->postShipmentsApplication($shipment, $params);
    }
}
