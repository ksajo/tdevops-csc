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

namespace Tygh\Addons\Retailcrm;


use RetailCrm\Response\ApiResponse;
use Tygh\Addons\Retailcrm\Client\ApiClient;
use Tygh\Addons\Retailcrm\Converters\CustomerConverter;
use Tygh\Addons\Retailcrm\Converters\OrderConverter;
use Exception;
use Tygh\Registry;

/**
 * The class provides methods to interactions with retailCRM.
 *
 * @package Tygh\Addons\Retailcrm
 */
class Service
{
    /**
     * @var Settings RetailCRM settings instance.
     */
    private $settings;

    /**
     * @var CustomerConverter Customer data converter instance.
     */
    private $customer_converter;

    /**
     * @var OrderConverter Order data converter instance.
     */
    private $order_converter;

    /**
     * @var ApiClient RetailCRM Api client instance.
     */
    private $api_client;

    /**
     * @var Logger Logger instance.
     */
    private $logger;

    const KEY_SYNC_ORDER_TIME = 'retailcrm_sync_order_time';

    /**
     * Initializes the retailCRM service class.
     *
     * @param Settings          $settings           RetailCRM settings instance.
     * @param CustomerConverter $customer_converter Customer data converter instance.
     * @param OrderConverter    $order_converter    Order data converter instance.
     * @param ApiClient         $api_client         RetailCRM Api client instance.
     * @param Logger            $logger             Logger instance.
     */
    public function __construct(
        Settings $settings,
        CustomerConverter $customer_converter,
        OrderConverter $order_converter,
        ApiClient $api_client,
        Logger $logger
    ) {
        $this->settings = $settings;
        $this->customer_converter = $customer_converter;
        $this->order_converter = $order_converter;
        $this->api_client = $api_client;
        $this->logger = $logger;
    }

    /**
     * Creates RetailCRM order by store order.
     *
     * @param array $order Order data.
     *
     * @return bool
     */
    public function createRetailCrmOrder(array $order)
    {
        $site = $this->settings->getExternalSite($order['company_id']);

        $this->logger->info(
            sprintf('Starting creating RetailCRM order by store order #%d.', $order['order_id']),
            __METHOD__
        );

        if (!$site) {
            $this->logger->info(
                sprintf('Order #%d skipped, storefront not configured.', $order['order_id']),
                __METHOD__
            );
            return false;
        }

        $customer_data = $this->customer_converter->convertToCrmCustomer($order);
        $order_data = $this->order_converter->convertToCrmOrder($order, $customer_data['externalId']);

        if (!$order_data['status'] || $order['status'] == STATUS_INCOMPLETED_ORDER) {
            $this->logger->info(
                sprintf('Order #%d skipped, storefront not configured.', $order['order_id']),
                __METHOD__
            );
            return false;
        }

        try {
            $customer = $this->api_client->customersGet($customer_data['externalId'], 'externalId', $site);

            if (!$customer->isSuccessful()) {
                $this->api_client->customersCreate($customer_data, $site);
            }

            $result = $this->api_client->ordersCreate($order_data, $site);

            if ($result->isSuccessful()) {
                $this->logger->info(
                    'RetailCRM order successfully created.',
                    __METHOD__
                );
            } else {
                $this->logger->error(
                    sprintf('Error creating RetailCRM order: %s.', implode(', ', $this->getResponseErrors($result))),
                    __METHOD__
                );
            }

            return $result->isSuccessful();
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Error creating RetailCRM order: %s.', $e->getMessage()),
                __METHOD__
            );
        }

        return false;
    }

    /**
     * Updates RetailCRM order status.
     *
     * @param int       $order_id       Order identifier.
     * @param string    $order_status   New order status.
     * @param int       $company_id     Company identifier.
     *
     * @return bool
     */
    public function updateRetailCrmOrderStatus($order_id, $order_status, $company_id)
    {
        $status = $this->settings->getExternalOrderStatus($order_status);
        $site = $this->settings->getExternalSite($company_id);

        $this->logger->info(
            sprintf('Starting updating RetailCRM order status by order #%d.', $order_id),
            __METHOD__
        );

        if (!$site || !$status) {
            $this->logger->info(
                sprintf('Order #%d skipped, storefront not configured.', $order_id),
                __METHOD__
            );

            return false;
        }

        $result = $this->api_client->ordersEdit(
            array(
                'externalId' => $order_id,
                'status' => $status
            ),
            'externalId',
            $site
        );

        if ($result->isSuccessful()) {
            $this->logger->info(
                'RetailCRM order status successfully updated.',
                __METHOD__
            );
        } else {
            $this->logger->error(
                sprintf('Error updating RetailCRM order status: %s.', implode(', ', $this->getResponseErrors($result))),
                __METHOD__
            );
        }

        return $result->isSuccessful();
    }

    /**
     * Synchronizes orders from RetailCRM.
     */
    public function syncOrders()
    {
        $runtime_company_id = Registry::get('runtime.company_id');
        $last_sync_time = fn_get_storage_data(self::KEY_SYNC_ORDER_TIME);

        $this->logger->info(
            'Starting synchronizing RetailCRM orders.',
            __METHOD__
        );

        if (!$last_sync_time) {
            $this->logger->error(
                'Last synchronization time is not defined.',
                __METHOD__
            );
            return;
        }

        $page = 1;
        $retailcrm_order_ids = array();
        $order_external_ids = array();
        $deleted_order_ids = array();

        try {
            do {
                $history = array();
                $result = $this->api_client->ordersHistory(
                    array('startDate' => date('Y-m-d H:i:s', $last_sync_time)),
                    $page
                );

                if ($result->isSuccessful()) {
                    $history = $result['history'];

                    foreach ($history as $item) {
                        if (!empty($item['apiKey']['current'])) {
                            continue;
                        }

                        if (empty($item['deleted'])) {
                            $retailcrm_order_ids[$item['order']['id']] = $item['order']['id'];
                        } elseif (
                            !empty($item['order']['externalId'])
                            && !empty($item['order']['site'])
                            && $this->settings->getInternalSite($item['order']['site'])
                        ) {
                            $deleted_order_ids[$item['order']['id']] = $item['order']['externalId'];
                        }
                    }

                    $last_sync_time = strtotime($result['generatedAt']);
                } else {
                    $this->logger->error(
                        sprintf('Error getting RetailCRM order history: %s.', implode(', ', $this->getResponseErrors($result))),
                        __METHOD__
                    );
                }

                $page++;
            } while ($history);

            Registry::set('runtime.company_id', 0);

            foreach ($deleted_order_ids as $retailcrm_order_id => $order_id) {
                unset($retailcrm_order_ids[$retailcrm_order_id]);

                if (fn_delete_order($order_id)) {
                    $this->logger->info(sprintf('Order #%d successfully deleted.', $order_id), __METHOD__);
                } else {
                    $this->logger->error(
                        sprintf('Deleting order #%d failed: %s', $order_id, json_encode(fn_get_notifications())),
                        __METHOD__
                    );
                }
            }

            foreach ($retailcrm_order_ids as $retailcrm_order_id) {
                Registry::set('runtime.company_id', 0);

                $result = $this->api_client->ordersGet($retailcrm_order_id, 'id');

                if ($result->isSuccessful()) {
                    $order = $this->order_converter->convertToShopOrder($result['order']);

                    if (!$order) {
                        $this->logger->error(
                            sprintf('RetailCRM order #%s can not converted to store order.', $retailcrm_order_id),
                            __METHOD__
                        );
                        continue;
                    }

                    $order_id = $this->syncOrder($order);

                    if (empty($order['order_id'])) {
                        $order_external_ids[$order_id] = $retailcrm_order_id;
                    }
                } else {
                    $this->logger->error(
                        sprintf('Error getting RetailCRM order data: %s.', implode(', ', $this->getResponseErrors($result))),
                        __METHOD__
                    );
                }
            }

            if (!empty($order_external_ids)) {
                $data = array();

                foreach ($order_external_ids as $order_id => $retailcrm_order_id) {
                    $data[] = array(
                        'id' => $retailcrm_order_id,
                        'externalId' => $order_id,
                    );
                }

                $this->api_client->ordersFixExternalIds($data);
            }

            Registry::set('runtime.company_id', $runtime_company_id);
            fn_set_storage_data(self::KEY_SYNC_ORDER_TIME, $last_sync_time);

        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Error synchronizing RetailCRM orders: %s.', $e->getMessage()),
                __METHOD__
            );
        }

        $this->logger->info(
            'Ending synchronizing RetailCRM orders.',
            __METHOD__
        );
    }

    /**
     * Updates or creates order.
     *
     * @param array $order Order data converted from RetailCRM
     *
     * @return bool|int
     */
    protected function syncOrder($order)
    {
        $cart = array();
        $auth = fn_fill_auth(array(), array(), false, 'C');

        fn_clear_cart($cart, true);

        if ($order['order_id'] && !fn_form_cart($order['order_id'], $cart, $auth)) {
            $this->logger->error(
                sprintf('Order #%d not found.', $order['order_id']),
                __METHOD__
            );
            return false;
        }

        $shippings = isset($cart['shipping']) ? $cart['shipping'] : array();
        $chosen_shipping_ids = isset($cart['chosen_shipping']) ? $cart['chosen_shipping'] : array();

        $cart = array_merge($cart, $order);

        $cart['calculate_shipping'] = false;
        $cart['shipping_required'] = false;
        $cart['product_groups'] = array();

        if (!empty($order['shipping_ids'])) {
            $cart['chosen_shipping'] = array($order['shipping_ids']);
        }

        fn_calculate_cart_content($cart, $auth, 'S', false, 'F', false);

        if (!isset($order['shipping_ids']) || $order['shipping_ids'] == reset($chosen_shipping_ids)) {
            $cart['shipping'] = $shippings;

            foreach ($cart['product_groups'] as &$product_group) {
                $product_group['chosen_shippings'] = $shippings;
            }
            unset($product_group);
        }

        $cart['shipping_failed'] = false;
        $cart['company_shipping_failed'] = false;
        $cart['shipping_cost'] = $order['shipping_cost'];
        $cart['total'] = $order['total'];

        foreach ($cart['shipping'] as &$shipping) {
            $shipping['rates'] = array($order['shipping_cost']);
        }
        unset($shipping);

        foreach ($cart['product_groups'] as &$product_group) {
            if (empty($product_group['chosen_shippings'])) {
                $product_group['chosen_shippings'] = array(reset($product_group['shippings']));
            }

            foreach ($product_group['chosen_shippings'] as &$shipping) {
                $shipping['rate'] = $order['shipping_cost'];
            }
            unset($shipping);
        }
        unset($product_group);

        Registry::set('runtime.company_id', $order['company_id']);

        //update order and change status to incomplete like changing during order management
        list($order_id, $order_status) = fn_update_order($cart, $order['order_id']);

        if ($order_id) {
            $force_notification = fn_get_notification_rules(array(), true);

            //change order status to status_from for substracting reward points
            fn_change_order_status($order_id, $order_status, '', $force_notification);

            if (isset($order['status']) && $order_status != $order['status']) {
                $force_notification['C'] = true;
                //change order status to status_to for getting promo and orders notification
                fn_change_order_status($order_id, $order['status'], $order_status, $force_notification);
            }

            if (empty($order['order_id'])) {
                $this->logger->info(
                    sprintf('Order #%d successfully created.', $order_id),
                    __METHOD__
                );
            } else {
                $this->logger->info(
                    sprintf('Order #%d successfully updated.', $order_id),
                    __METHOD__
                );
            }

            return $order_id;
        } else {
            if ($order['order_id']) {
                $this->logger->error(
                    sprintf('Cannot update order #%d, errors: %s.', $order['order_id'], json_encode(fn_get_notifications())),
                    __METHOD__
                );
            } else {
                $this->logger->error(
                    sprintf('Cannot create order, errors: %s.', $order['order_id'], json_encode(fn_get_notifications())),
                    __METHOD__
                );
            }
        }

        return false;
    }

    /**
     * Gets error from response.
     *
     * @param ApiResponse $response
     *
     * @return array
     */
    private function getResponseErrors(ApiResponse $response)
    {
        $errors = array();

        if (!$response->isSuccessful()) {
            if (isset($response['errors'])) {
                $errors = $response['errors'];
            } elseif (isset($response['errorMsg'])) {
                $errors[] = $response['errorMsg'];
            } else {
                $errors[] = 'Response status code: ' . $response->getStatusCode();
            }
        }

        return $errors;
    }

    /**
     * Check if order is exist on RetailCRM by id.
     *
     * @param int $order_id   Order identifier
     * @param int $company_id Company identifier.
     *
     * @return bool
     */
    public function isRetailCrmOrderExists($order_id, $company_id)
    {
        $site = $this->settings->getExternalSite($company_id);

        if (!$site) {
            $this->logger->info(
                sprintf('Order #%d skipped, storefront not configured.', $order_id),
                __METHOD__
            );

            return false;
        }

        $order_data = $this->api_client->ordersGet($order_id, 'externalId', $site);
        if ($order_data->isSuccessful()) {
            return true;
        }

        return false;
    }
}