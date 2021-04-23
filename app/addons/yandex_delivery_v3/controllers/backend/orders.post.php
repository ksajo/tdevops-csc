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
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $mode */
if ($mode === 'details') {
    $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;
    $order_info = Tygh::$app['view']->getTemplateVars('order_info');
    $shipments = Tygh::$app['view']->getTemplateVars('shipments');
    $shipping = reset($order_info['shipping']);

    if ($shipping['module'] !== YandexDeliveryService::MODULE) {
        $carriers = Tygh::$app['view']->getTemplateVars('carriers');
        unset($carriers['yandex_delivery']);
        Tygh::$app['view']->assign('carriers', $carriers);
    } else {
        $order_service = ServiceProvider::getOrderService();
        if (empty($shipments)) {
            Tygh::$app['view']->assign('can_create_yandex_draft', true);
            $yandex_delivery = ServiceProvider::getApiService();
            $can_auto_confirm = $yandex_delivery->getOrdersPublishStatus();
            Tygh::$app['view']->assign('can_auto_confirm', $can_auto_confirm);
            if ($can_auto_confirm) {
                $yandex_order_data = $order_service->getDeliveryOrderData($order_info);
                Tygh::$app['view']->assign('yandex_delivery_data', $yandex_order_data);
            }
        } else {
            foreach ($shipments as $shipment) {
                $order_service->updateYandexOrderStatusByShipment($shipment['shipment_id']);
            }
            $yandex_order_data = $order_service->getDeliveryOrderData($order_info, $shipments);
            Tygh::$app['view']->assign('yandex_delivery_data', $yandex_order_data);
        }
    }
}