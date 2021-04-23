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

use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Common\OperationResult;
use Tygh\Enum\NotificationSeverity;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $yandex_delivery = ServiceProvider::getApiService();
    $order_service = ServiceProvider::getOrderService();

    /** @var string $mode */
    if ($mode === 'create_yandex_delivery_order' && isset($_REQUEST['yandex_order'])) {
        $yandex_params = $_REQUEST['yandex_order'];
        $force_notification = fn_get_notification_rules($yandex_params);

        $order_info = fn_get_order_info($yandex_params['order_id']);
        $shipping = reset($order_info['shipping']);
        $delivery_type = strtoupper($shipping['service_params']['type_delivery']);
        if (empty((int) $yandex_params['shipment_id'])) {
            $yandex_params['shipment_id'] = $order_service->createShipment($order_info);

        }
        $params = $order_service->createPostOrderRequest($yandex_params);
        $result = new OperationResult(false);
        if (!empty($params)) {
            $yandex_order_id = $yandex_delivery->postOrders($yandex_params['sender_id'], $delivery_type, $params);
        } else {
            $yandex_order_id = [];
        }
        if (is_numeric($yandex_order_id)) {
            $result->setSuccess(true);
            $yandex_order_info = $yandex_delivery->getOrdersId((int) $yandex_order_id);
            $order_service->updateYandexOrder($yandex_params['shipment_id'], $yandex_order_info, $yandex_params, false);
            $is_order_need_submit = $yandex_delivery->getOrdersPublishStatus();
            if ($is_order_need_submit && !empty($params)) {
                $result = $yandex_delivery->postOrdersSubmit([(int) $yandex_order_id]);
                if ($result->isSuccess()) {
                    $yandex_order = $result->getData();
                    $result->addMessage($yandex_order['status'], __('yandex_delivery_v3.order_created'));
                    $yandex_order_info = $yandex_delivery->getOrdersId((int) $yandex_order_id);
                    $order_service->updateYandexOrder($yandex_params['shipment_id'], $yandex_order_info, $yandex_params, $force_notification);
                } else {
                    fn_set_notification(NotificationSeverity::NOTICE, __('notice'), __('yandex_delivery_v3.draft_created'));
                }
            }
        } else {
            $result = $yandex_delivery->processErrors($result, $yandex_order_id);
        }
        $result->showNotifications();
        return [CONTROLLER_STATUS_OK, $_REQUEST['redirect_url']];
    }
    if ($mode === 'create_yandex_delivery_draft') {
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        if (!$order_info) {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __('yandex_delivery_v3.order_data_missing'));
            return [CONTROLLER_STATUS_OK, 'orders.details?order_id=' . $_REQUEST['order_id']];
        }
        $shipping = reset($order_info['shipping']);
        $delivery_type = strtoupper($shipping['service_params']['type_delivery']);
        $sender_id = $shipping['service_params']['sender_id'];

        $shipment_id = $order_service->createShipment($order_info);
        list($shipments,) = fn_get_shipments_info(['shipment_id' => $shipment_id, 'advanced_info' => true]);

        $yandex_params = $order_service->getDeliveryOrderData($order_info, $shipments);
        if (empty($yandex_params)) {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), __('yandex_delivery_v3.order_data_missing'));
            return [CONTROLLER_STATUS_OK, 'orders.details?order_id=' . $_REQUEST['order_id']];
        }

        $params = $order_service->createPostOrderRequest($yandex_params, $order_info, $shipments, true);
        $result = new OperationResult(false);
        if (!empty($params)) {
            $yandex_order_id = $yandex_delivery->postOrders($sender_id, $delivery_type, $params);
        } else {
            $yandex_order_id = [];
        }
        if (is_numeric($yandex_order_id)) {
            $result->setSuccess(true);
            $result->addMessage($yandex_order_id['status'], __('yandex_delivery_v3.draft_created'));
            $yandex_order_info = $yandex_delivery->getOrdersId((int) $yandex_order_id);
            $order_service->updateYandexOrder($shipment_id, $yandex_order_info, $yandex_params, false);
        } else {
            $result = $yandex_delivery->processErrors($result, $yandex_order_id);
        }
        $result->showNotifications();
        return [CONTROLLER_STATUS_OK, 'orders.details?order_id=' . $_REQUEST['order_id']];
    }
}