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
if ($mode === 'configure') {
    if (
        !empty($_REQUEST['module'])
        && $_REQUEST['module'] === YandexDeliveryService::MODULE
        && !empty($_REQUEST['shipping_id'])
    ) {
        $client = ServiceProvider::getApiService();
        $warehouses = $client->getWarehouses();
        $stores = $client->getStores();
        $deliveries = $client->getDeliveryServices();
        // Remove this filtration when POST delivery type will be integrated.
        $deliveries = array_filter($deliveries, static function($delivery) {
            return $delivery['id'] !== 1003375 && $delivery['id'] !== 1003390;
        });
        $shipping = fn_get_shipping_params($_REQUEST['shipping_id']);
        $selected_deliveries = empty($shipping['deliveries'])
            ? array_column($deliveries, 'id')
            : $shipping['deliveries'];

        Tygh::$app['view']->assign('deliveries', $deliveries);
        Tygh::$app['view']->assign('deliveries_select', $selected_deliveries);
        Tygh::$app['view']->assign('warehouses', $warehouses);
        Tygh::$app['view']->assign('stores', $stores);
        Tygh::$app['view']->assign('addon_settings', fn_url('addons.manage'));
    }
}
