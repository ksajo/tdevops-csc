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


namespace Tygh\Addons\CommerceML\Importers;


use Tygh\Addons\CommerceML\Dto\OrderDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\OrderStorage;
use Tygh\Common\OperationResult;
use Tygh\Enum\YesNo;

/**
 * Class OrderImporter
 *
 * @package Tygh\Addons\CommerceML\Importers
 */
class OrderImporter
{
    /**
     * @var \Tygh\Addons\CommerceML\Storages\OrderStorage
     */
    private $order_storage;

    /**
     * OrderImporter constructor.
     *
     * @param \Tygh\Addons\CommerceML\Storages\OrderStorage $order_storage Order storage
     */
    public function __construct(OrderStorage $order_storage)
    {
        $this->order_storage = $order_storage;
    }

    /**
     * Imports orders
     *
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto           $order                    Order DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage           Import storage
     * @param bool                                           $is_change_status_allowed Flag if change status permission allowed
     * @param bool                                           $is_edit_order_allowed    Flag if edit order permission allowed
     *
     * @return \Tygh\Common\OperationResult
     */
    public function import(OrderDto $order, ImportStorage $import_storage, $is_change_status_allowed, $is_edit_order_allowed)
    {
        $result = new OperationResult(true);

        $current_order_data = $this->order_storage->getOrderData((int) $order->id->local_id);

        if (empty($current_order_data)) {
            $result->setSuccess(false);
            $result->addMessage('order.not_updated', __('commerceml.import.error.order.local_order_not_found'));

            return $result;
        }

        $order_status = $this->fillOrderStatus($order, $current_order_data);

        $notify_user = $order_status === $current_order_data['status'];

        if ($is_edit_order_allowed) {
            $result = $this->updateOrderNew($order, $import_storage, $current_order_data, $notify_user);
        }

        if ($result->isFailure()) {
            return $result;
        }

        if ($is_change_status_allowed && $order_status !== $current_order_data['status']) {
            $result = $this->updateStatus($order, $order_status, (string) $current_order_data['status']);
        }

        if ($order->updated_at) {
            $this->order_storage->updateOrderUpdatedTime($order, $order->updated_at);
        }

        return $result;
    }

    /**
     * Updates orders status
     *
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto $order      Order Dto
     * @param string                               $new_status New order status
     * @param string                               $old_status Old order status
     *
     * @return OperationResult
     */
    private function updateStatus(OrderDto $order, $new_status, $old_status)
    {
        $main_result = new OperationResult();

        $result = $this->order_storage->updateOrderStatus($order, $new_status, $old_status);

        if (!$result) {
            $main_result->setSuccess(false);
            return $main_result;
        }

        $main_result->addMessage('order.updated', __('commerceml.import.message.order.status_updated', [
            '[local_id]'   => $order->id->local_id,
            '[new_status]' => $new_status,
            '[old_status]' => $old_status,
        ]));

        return $main_result;
    }

    /**
     * Updates order data
     *
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto           $order              Order Dto
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage     Import storage
     * @param array<string, string|int>                      $current_order_data Current order data
     * @param bool                                           $notify_user        Flag to notify user while the order update
     *
     * @return OperationResult
     */
    private function updateOrderNew(OrderDto $order, ImportStorage $import_storage, array $current_order_data, $notify_user)
    {
        $result = new OperationResult();

        $order_id = (int) $order->id->local_id;

        $order_data = array_merge($order->properties->getValueMap(), [
            'order_id'          => $order_id,
            'user_id'           => (int) $current_order_data['user_id'],
            'firstname'         => (string) $current_order_data['firstname'],
            's_firstname'       => (string) $current_order_data['s_firstname'],
            'b_firstname'       => (string) $current_order_data['b_firstname'],
            'lastname'          => (string) $current_order_data['lastname'],
            's_lastname'        => (string) $current_order_data['s_lastname'],
            'b_lastname'        => (string) $current_order_data['b_lastname'],
            'company_id'        => (int) $current_order_data['company_id'],
            'timestamp'         => (int) $current_order_data['timestamp'],
            'total'             => 0,
            'subtotal_discount' => 0,
            'discount'          => 0,
            'payment_surcharge' => 0,
            'shipping_cost'     => $order->shipping_cost === null ? 0 : $order->shipping_cost,
            'stored_discount'   => YesNo::YES,
            'products'          => []
        ]);

        $order_data = $this->fillProductsData($order_data, $order, $import_storage, (int) $current_order_data['company_id']);

        if (empty($order_data)) {
            $result->setSuccess(false);
            $result->addMessage('order.not_updated', __('commerceml.import.error.order.local_product_not_found'));

            return $result;
        }

        $order_data = $this->fillOrderTotals($order_data, $order, $current_order_data);

        if (empty($order_data)) {
            $result->setSuccess(false);
            return $result;
        }

        $main_result = $this->order_storage->updateOrder(
            $order_data,
            $order_id,
            $notify_user,
            sprintf('Order %s updating failed', $order->id->getId())
        );

        if ($main_result->isFailure()) {
            return $main_result;
        }

        $product_ids = array_column((array) $order_data['products'], 'product_id');

        $main_result->addMessage('order.updated', __('commerceml.import.message.order.updated', [
            '[local_id]' => $order_id,
            '[products]' => isset($order_data['products']) ? implode(',', $product_ids) : '-',
        ]));

        return $main_result;
    }

    /**
     * Fills order data with products data
     *
     * @param array<string|int, int|string|array<int, string|int|array>> $order_data     Order data
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto                       $order          Order Dto
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage             $import_storage Import storage
     * @param int                                                        $company_id     Company identifier
     *
     * @psalm-param array{
     *      b_firstname: string,
     *      b_lastname: string,
     *      company_id: int,
     *      discount: int,
     *      firstname: string,
     *      lastname: string,
     *      order_id: int,
     *      payment_surcharge: int,
     *      products: array,
     *      s_firstname: string,
     *      s_lastname: string,
     *      shipping_cost: float|int,
     *      stored_discount: string,
     *      subtotal_discount: int,
     *      timestamp: int,
     *      total: int,
     *      user_id: int
     * } $order_data
     *
     * @return array<string, int|string|float|array>|false
     */
    private function fillProductsData(array $order_data, OrderDto $order, ImportStorage $import_storage, $company_id)
    {
        $products = [];

        foreach ($order->products as $product) {
            $local_id = $import_storage->findEntityLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product->id)->asInt();

            if (empty($local_id)) {
                return false;
            }

            if (!isset($order_data['products'][$local_id])) {
                /** @var array $order_data['products'] */
                $products[$local_id] = [
                    'base_price'      => $product->price,
                    'original_price'  => $product->price,
                    'price'           => $product->price,
                    'company_id'      => $company_id,
                    'amount'          => $product->amount,
                    'original_amount' => $product->amount,
                    'product_id'      => $local_id,
                    'stored_price'    => YesNo::YES,
                    'stored_discount' => YesNo::YES,
                ];
            } else {
                /** @var array<int, array{product_id: int, amount: int, price: float}> $products */
                $old_total_price = $products[$local_id]['price'] * $products[$local_id]['amount'];
                $new_total_price = $old_total_price + ((float) $product->price * (int) $product->amount);

                $products[$local_id]['amount'] += (int) $product->amount;

                $price = $new_total_price / $products[$local_id]['amount'];

                $products[$local_id]['price'] = $price;
            }
        }

        if (!empty($products)) {
            /** @var array $order_data['products'] */
            $order_data['products'] = $products;
        }

        return $order_data;
    }

    /**
     * Fills order data with order status
     *
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto $order              Order Dto
     * @param array<string, int|string>            $current_order_data Current order data
     *
     * @return string
     */
    private function fillOrderStatus(OrderDto $order, array $current_order_data)
    {
        if ($order->status === null) {
            return (string) $current_order_data['status'];
        }

        $status = $this->order_storage->getOrderStatusByDescription($order->status->default_value);

        return empty($status) ? (string) $current_order_data['status'] : $status;
    }

    /**
     * Fills order data with order totals
     *
     * @param array<string, int|string|array|float> $order_data         Order data
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto  $order              Order Dto
     * @param array<string, int|string|array>       $current_order_data Current order data
     *
     * @return array<string, int|string|array|float>
     */
    private function fillOrderTotals(array $order_data, OrderDto $order, array $current_order_data)
    {
        $order_data['subtotal'] = $order->subtotal;
        $order_data['subtotal_discount'] = $order->subtotal_discount;
        $order_data['total'] = $order_data['subtotal'] - $order_data['subtotal_discount'];

        if (!empty($order_data['shipping_cost']) && !empty($current_order_data['shipping_cost'])) {
            $order_data['total'] += (float) $order_data['shipping_cost'];
        }

        return $order_data;
    }
}
