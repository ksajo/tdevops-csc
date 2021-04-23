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

use Tygh\Addons\RusTaxes\Receipt\Item as ReceiptItem;
use Tygh\Addons\RusTaxes\ReceiptFactory;
use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Enum\NotificationSeverity;
use Tygh\Tygh;

class OrderService
{
    /** @var YandexDeliveryService $client */
    protected $client;

    /** @var ReceiptFactory $receipt_service */
    protected $receipt_service;

    /** @var OrderDetailsBuilder $request_builder */
    protected $request_builder;

    /**
     * OrderService constructor.
     *
     * @param YandexDeliveryService $client          Service for making Yandex.Delivery API requests.
     * @param ReceiptFactory        $receipt_service Service for creating receipt information.
     * @param OrderDetailsBuilder   $request_builder Service for build request parts.
     */
    public function __construct(YandexDeliveryService $client, ReceiptFactory $receipt_service, OrderDetailsBuilder $request_builder)
    {
        $this->client = $client;
        $this->receipt_service = $receipt_service;
        $this->request_builder = $request_builder;
    }

    /**
     * Gets information for creating order in Yandex.Delivery service.
     *
     * @param array<string, string|array<string, string>>         $order_info Order information.
     * @param array<string, array<string, array<string, string>>> $shipments  List of available shipments for this order.
     *
     * @return array<string, string>
     *
     * @psalm-return array{
     *                  amount_prepaid?: int|float,
     *                  assessed_value?: int|float,
     *                  first_name?: non-empty-array<string, string>|non-empty-string,
     *                  last_name?: non-empty-string,
     *                  phone?: string,
     *                  senders?: array<array-key, string>,
     *                  warehouses?: array<array-key, string>
     *               }
     */
    public function getDeliveryOrderData(array $order_info, array $shipments = [])
    {
        if (empty($shipments)) {
            $receipt = $this->getReceiptFromOrder($order_info, []);
            if (!$receipt) {
                return [];
            }
            $amount_value_shipments = $receipt->getTotal();
            $assessed_value_shipments = $amount_value_shipments;
            $shipping_item = $receipt->getItem(0, ReceiptItem::TYPE_SHIPPING);
            if ($shipping_item) {
                $assessed_value_shipments -= $shipping_item->getTotal();
            }
        } else {
            $shipment = reset($shipments);
            if ($shipment['carrier'] !== YandexDeliveryService::MODULE) {
                return [];
            }
            $receipt = $this->getReceiptFromOrder($order_info, $shipment);
            if (!$receipt) {
                return [];
            }
            $amount_value_shipments = $receipt->getTotal();
            $assessed_value_shipments = $amount_value_shipments;

            $shipping_item = $receipt->getItem(0, ReceiptItem::TYPE_SHIPPING);
            if ($shipping_item) {
                $assessed_value_shipments -= $shipping_item->getTotal();
            }
        }

        /** @var string $phone */
        $phone = !empty($order_info['s_phone']) ? $order_info['s_phone'] : $order_info['phone'];

        $first_name = '';
        if (!empty($order_info['s_firstname'])) {
            $first_name = $order_info['s_firstname'];
        } elseif (!empty($order_info['firstname'])) {
            $first_name = $order_info['firstname'];
        } elseif (!empty($order_info['b_firstname'])) {
            $first_name = $order_info['b_firstname'];
        }

        $last_name = '';
        if (!empty($order_info['s_lastname'])) {
            $last_name = $order_info['s_lastname'];
        } elseif (!empty($order_info['lastname'])) {
            $last_name = $order_info['lastname'];
        } elseif (!empty($order_info['b_lastname'])) {
            $last_name = $order_info['b_lastname'];
        }

        $address = '';
        if (!empty($order_info['s_address'])) {
            $address = $order_info['s_address'];
        } elseif (!empty($order_info['b_address'])) {
            $address = $order_info['b_address'];
        }
        $email = '';
        if (!empty($order_info['email'])) {
            $email = $order_info['email'];
        }

        return [
            'senders'        => $this->client->getStores(),
            'warehouses'     => $this->client->getWarehouses(),
            'assessed_value' => $assessed_value_shipments,
            'amount_prepaid' => $amount_value_shipments,
            'phone'          => $phone,
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'address'        => $address,
            'email'          => $email,
            'orders'         => $this->getYandexOrders((int) $order_info['order_id']),
            'order_id'       => $order_info['order_id'],
            'date'           => date('Y-m-d', fn_parse_date(TIME)),
            'type'           => 'WITHDRAW',
        ];
    }

    /**
     * Forms receipt from order information.
     *
     * @param array<string, array<string, string>> $order_info Order information.
     * @param array<string, array<string, string>> $shipment   Available shipments for this order.
     *
     * @return \Tygh\Addons\RusTaxes\Receipt\Receipt|null
     */
    protected function getReceiptFromOrder(array $order_info, array $shipment)
    {
        $receipt = $this->receipt_service->createReceiptFromOrder(
            $order_info,
            CART_PRIMARY_CURRENCY,
            true,
            [ReceiptItem::TYPE_PRODUCT, ReceiptItem::TYPE_SURCHARGE]
        );

        if (!$receipt) {
            return $receipt;
        }
        $remainder = 0;
        if (empty($shipment)) {
            foreach ($order_info['products'] as $item_id => $item) {
                $receipt->setItemQuantity($item_id, ReceiptItem::TYPE_PRODUCT, (float) $item['amount']);
            }
        } else {
            foreach ($order_info['products'] as $item_id => $item) {
                if (isset($shipment['products'][$item_id])) {
                    continue;
                }
                $receipt->removeItem($item_id, ReceiptItem::TYPE_PRODUCT);
            }

            foreach ($shipment['products'] as $item_id => $amount) {
                $receipt->setItemQuantity($item_id, ReceiptItem::TYPE_PRODUCT, (float) $amount);
            }
        }

        foreach ($receipt->getItems() as $item) {
            if (
                in_array(
                    $item->getType(),
                    [ReceiptItem::TYPE_PRODUCT, ReceiptItem::TYPE_SURCHARGE, ReceiptItem::TYPE_SHIPPING],
                    true
                )
            ) {
                $price = floor($item->getPrice());
                $remainder += ($item->getPrice() - $price) * $item->getQuantity();
                $item->setPrice($price);
            } else {
                $receipt->removeItem($item->getId(), $item->getType());
            }
        }
        $remainder = floor($remainder);
        if (!empty($remainder)) {
            foreach ($receipt->getItems() as $item) {
                if ($item->getType() !== ReceiptItem::TYPE_SHIPPING && $item->getQuantity() === 1) {
                    $item->setPrice($item->getPrice() + $remainder);
                    $remainder = 0;
                    break;
                }
            }
        }
        if (!empty($remainder)) {
            foreach ($receipt->getItems() as $item) {
                if ($item->getType() !== ReceiptItem::TYPE_SHIPPING) {
                    $clone_item = clone $item;
                    $clone_item->setQuantity(1);
                    $clone_item->setPrice($item->getPrice() + $remainder);
                    $item->setQuantity($item->getQuantity() - 1);
                    $receipt->setItem($clone_item);
                    break;
                }
            }
        }
        return $receipt;
    }

    /**
     * Gets all Yandex.Delivery orders that are connected to specified platform order.
     *
     * @param int $order_id Platform order identifier.
     *
     * @return array<string, string>
     */
    protected function getYandexOrders($order_id)
    {
        $params = [
            'search_yandex_delivery_order' => true,
            'order_id'                     => $order_id,
        ];

        list($shipments,) = fn_get_shipments_info($params);

        $yd_order_statuses = $this->getStatuses();
        $ya_orders = [];
        foreach ($shipments as $order) {
            if (!array_key_exists($order['yad_status_code'], $yd_order_statuses)) {
                continue;
            }
            $order['status_name'] = $yd_order_statuses[$order['yad_status_code']]['yad_status_name'];
            $ya_orders[$order['shipment_id']] = $order;
        }

        return $ya_orders;
    }

    /**
     * Gets all available Yandex.Delivery order statuses.
     *
     * @return array<int|string, array<string, string>>
     */
    protected function getStatuses()
    {
        static $statuses = [];

        if (empty($statuses)) {
            $statuses = db_get_hash_array('SELECT s.yad_status_id, s.yad_status_code, sd.yad_status_name'
                . ' FROM ?:yad_statuses as s LEFT JOIN ?:yad_status_descriptions as sd USING(yad_status_id)', 'yad_status_code');
        }

        return $statuses;
    }

    /**
     * Checks Yandex.Delivery orders statuses from API and updates them if they changed.
     *
     * @param int $shipment_id Shipment identifier.
     */
    public function updateYandexOrderStatusByShipment($shipment_id)
    {
        $yandex_order_id = (int) db_get_field('SELECT yandex_id FROM ?:yad_orders WHERE shipment_id = ?i', $shipment_id);

        if (empty($yandex_order_id)) {
            return;
        }
        $yandex_delivery = ServiceProvider::getApiService();
        $yandex_order_status = $yandex_delivery->getOrdersId($yandex_order_id);
        if (empty($yandex_order_status)) {
            return;
        }
        $this->updateYandexOrderStatus($yandex_order_id, $yandex_order_status['status']);
    }

    /**
     * Updating Yandex.Delivery order status.
     *
     * @param int    $id          Yandex.Delivery order identifier.
     * @param string $status_code Yandex.Delivery order status code.
     */
    protected function updateYandexOrderStatus($id, $status_code)
    {
        $order_statuses = $this->getStatuses();
        if (empty($order_statuses)) {
            return;
        }
        $status = $order_statuses[$status_code];
        db_query(
            'UPDATE ?:yad_orders SET status = ?i WHERE yandex_id = ?i',
            $status['yad_status_id'],
            $id
        );
    }

    /**
     * Updates information about Yandex.Delivery order.
     *
     * @param int|string               $shipment_id        Shipment identifier.
     * @param array<string, string>    $yandex_order_info  Information about Yandex.Delivery order.
     * @param array<string, string>    $yandex_params      Parameters for creation of Yandex.Delivery order.
     * @param array<string, bool>|bool $force_notification Notification rules.
     */
    public function updateYandexOrder($shipment_id, array $yandex_order_info, array $yandex_params, $force_notification = false)
    {
        $statuses = $this->getStatuses();

        $order_status_id = $statuses[$yandex_order_info['status']]['yad_status_id'];
        $order = [
            'yandex_id' => $yandex_order_info['id'],
            'status'    => $order_status_id,
        ];
        $yandex_order = db_get_field('SELECT 1 FROM ?:yad_orders WHERE shipment_id = ?i', $shipment_id);
        if (empty($yandex_order)) {
            $order['shipment_id'] = $shipment_id;
            db_query('INSERT INTO ?:yad_orders ?e', $order);
        } else {
            db_query('UPDATE ?:yad_orders SET ?u WHERE shipment_id = ?i', $order, $shipment_id);
        }
        fn_update_shipment(
            ['timestamp' => fn_date_to_timestamp($yandex_params['date'])],
            $shipment_id
        );
        // Remove this notification when fn_update_shipment will support notifications on partial update shipment scenario.
        list($shipments, ) = fn_get_shipments_info(['shipment_id' => $shipment_id, 'advanced_info' => true]);

        $shipment = reset($shipments);
        $shipment['timestamp'] = $shipment['shipment_timestamp'];
        $order_info = fn_get_order_info($yandex_order_info['externalId']);

        /** @var \Tygh\Notifications\EventDispatcher $event_dispatcher */
        $event_dispatcher = Tygh::$app['event.dispatcher'];

        /** @var \Tygh\Notifications\Settings\Factory $notification_settings_factory */
        $notification_settings_factory = Tygh::$app['event.notification_settings.factory'];
        $notification_rules = $notification_settings_factory->create($force_notification);

        $event_dispatcher->dispatch(
            'order.shipment_updated',
            ['shipment' => $shipment, 'order_info' => $order_info],
            $notification_rules
        );
    }

    /**
     * Creates parameters for post-order request.
     *
     * @param array<string, string> $data       Specific information needed for Yandex.Delivery order.
     * @param array<string, string> $order_info Information about order into platform.
     * @param array<string, string> $shipment   Information about shipments that used for this Yandex.Delivery order.
     * @param bool                  $is_draft   Whether the created order should remain draft.
     *
     * @psalm-param array{
     *                  amount_prepaid?: float|int,
     *                  assessed_value?: float|int,
     *                  first_name?: non-empty-array<string, string>|non-empty-string,
     *                  last_name?: non-empty-string,
     *                  phone?: string,
     *                  senders?: array<array-key, string>,
     *                  warehouses?: array<array-key, string>
     *              } $data
     *
     * @return array<string, array<string, string>>
     *
     * @psalm-return array{
     *                  comment?: string,
     *                  contacts?: array<int, array<string, string>>,
     *                  cost?: array<string, bool|float|string>,
     *                  deliveryOption?: array<string, array<string, string>|string>,
     *                  externalId?: int|string,
     *                  places?: array{
     *                              array{
     *                                  dimensions: array{
     *                                      height: float|int,
     *                                      length: float|int,
     *                                      weight: string,
     *                                      width: float|int
     *                                  },
     *                                  items?: non-empty-list<array{
     *                                      assessedValue: int,
     *                                      count: int,
     *                                      dimensions: array{
     *                                          height: float,
     *                                          length: float,
     *                                          weight: float,
     *                                          width: float
     *                                      },
     *                                      name: string,
     *                                      price: float
     *                                  }>
     *                              }
     *                          },
     *                    recipient?: array{
     *                      address: array{
     *                          apartment?: string,
     *                          country: string,
     *                          geoId?: int|string,
     *                          house?: string,
     *                          locality: string,
     *                          postalCode: string,
     *                          region: string,
     *                          street?: string
     *                      },
     *                      firstName?: string,
     *                      lastName?: string,
     *                      pickupPointId?: int
     *                    },
     *                    shipment?: array<string, string>
     *              }
     */
    public function createPostOrderRequest(array $data, array $order_info = [], array $shipment = [], $is_draft = false)
    {
        $request = [];
        if (empty($order_info)) {
            $order_info = fn_get_order_info($data['order_id']);
            if (empty($order_info['shipping'])) {
                return $request;
            }
        }
        if (empty($shipment)) {
            list($shipment,) = fn_get_shipments_info(
                [
                    'shipment_id' => $data['shipment_id'],
                    'advanced_info' => true,
                ]
            );
        }
        if ($data['type'] === 'import') {
            $yad_shipment = $this->request_builder->createPostShipmentRequest($order_info, $data);
            if (!$yad_shipment) {
                fn_set_notification(NotificationSeverity::WARNING, __('notice'), __('yandex_delivery_v3.your_order_setting_are_not_supported'));
                return $request;
            }
        }
        $delivery_options = $this->request_builder->createPutDeliveryOptionsRequest($order_info, $data);
        if (!is_array($delivery_options) || isset($delivery_options['message'])) {
            return $request;
        }
        $delivery_options = reset($delivery_options);
        $request['externalId'] = $data['order_id'];
        if (!empty($data['comment'])) {
            $request['comment'] = $data['comment'];
        }
        $request['recipient'] = $this->request_builder->buildOrderRecipient($data, $order_info);
        $request['contacts'] = $this->request_builder->buildOrderContacts($data);
        $request['cost'] = $this->request_builder->buildOrderCost($data, $delivery_options, $order_info['status']);
        $request['places'] = $this->request_builder->buildOrderPlaces($order_info, $shipment);
        if (!$request['places']) {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __('yandex_delivery_v3.your_products_without_parameters'));
            return [];
        }
        $request['deliveryOption'] = $this->request_builder->buildOrderDeliveryOption($order_info, $delivery_options);
        if (!$is_draft) {
            $request['shipment'] = $this->request_builder->buildOrderShipment($data, $order_info, $delivery_options);
        }
        return $request;
    }

    /**
     * Creates shipment with all products available in the specified order.
     *
     * @param array<string, string> $order_info Information about specified order.
     *
     * @return int
     */
    public function createShipment(array $order_info)
    {
        $shipping = reset($order_info['shipping']);
        $products = [];
        foreach ($order_info['products'] as $item_key => $product) {
            $products[$item_key] = $product['amount'];
        }

        return fn_update_shipment(
            [
                'order_id'    => $order_info['order_id'],
                'carrier'     => YandexDeliveryService::MODULE,
                'shipping_id' => $shipping['shipping_id'],
                'tracking_number' => '',
                'products'        => $products,
            ],
            0,
            0,
            true,
            false
        );
    }
}
