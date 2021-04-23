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

/**
 * @var string $mode
 * @var array $auth
 */
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'update' && $_REQUEST['addon'] === 'retailcrm') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    /** @var \Tygh\Addons\Retailcrm\Settings $settings */
    $settings = Tygh::$app['addons.retailcrm.settings'];

    $retailcrm_host = Registry::ifGet('addons.retailcrm.retailcrm_host', '');
    $retailcrm_api_key = Registry::ifGet('addons.retailcrm.retailcrm_api_key', '');

    if (isset($_REQUEST['retailcrm_host'])) {
        $retailcrm_host = $_REQUEST['retailcrm_host'];
    }

    if (isset($_REQUEST['retailcrm_api_key'])) {
        $retailcrm_api_key = $_REQUEST['retailcrm_api_key'];
    }


    $retailcrm_host = fn_retailcrm_filter_retailcrm_host($retailcrm_host);

    $result = fn_retailcrm_validate_retailcrm_credentials($retailcrm_host, $retailcrm_api_key);

    if ($result->isSuccess()) {
        /** @var \RetailCrm\ApiClient $client */
        $client = call_user_func(Tygh::$app['addons.retailcrm.api_client_factory'], $retailcrm_host,
            $retailcrm_api_key);

        $retailcrm_order_statuses = array();
        $response = $client->statusesList();


        if ($response->isSuccessful()) {
            $retailcrm_order_statuses = $response['statuses'];
        }

        $retailcrm_sites = array();
        $response = $client->sitesList();

        if ($response->isSuccessful()) {
            $retailcrm_sites = $response['sites'];
        }

        $retailcrm_payment_types = array();
        $response = $client->paymentTypesList();

        if ($response->isSuccessful()) {
            $retailcrm_payment_types = $response['paymentTypes'];
        }

        $retailcrm_shipping_types = array();
        $response = $client->deliveryTypesList();

        if ($response->isSuccessful()) {
            $retailcrm_shipping_types = $response['deliveryTypes'];
        }

        $retailcrm_order_methods = array();
        $response = $client->orderMethodsList();

        if ($response->isSuccessful()) {
            $retailcrm_order_methods = $response['orderMethods'];
        }

        $retailcrm_order_types = array();
        $response = $client->orderTypesList();

        if ($response->isSuccessful()) {
            $retailcrm_order_types = $response['orderTypes'];
        }

        $order_statuses = fn_get_statuses(STATUSES_ORDER, [], true, true);
        $payment_types = fn_get_payments(array('status' => 'A'));
        $shipping_types = fn_get_shippings(false);
        list($storefronts) = fn_get_companies(array(), $auth);

        $view
            ->assign('retailcrm_connection_status', true)
            ->assign('retailcrm_order_statuses', $retailcrm_order_statuses)
            ->assign('retailcrm_sites', $retailcrm_sites)
            ->assign('retailcrm_payment_types', $retailcrm_payment_types)
            ->assign('retailcrm_shipping_types', $retailcrm_shipping_types)
            ->assign('retailcrm_order_methods', $retailcrm_order_methods)
            ->assign('retailcrm_order_types', $retailcrm_order_types)
            ->assign('order_statuses', $order_statuses)
            ->assign('storefronts', $storefronts)
            ->assign('shipping_types', $shipping_types)
            ->assign('payment_types', $payment_types)
            ->assign('order_method', $settings->getOrderMethod())
            ->assign('order_type', $settings->getOrderType())
            ->assign('map_order_statuses', $settings->getMapOrderStatuses())
            ->assign('map_shipping_types', $settings->getMapShippingTypes())
            ->assign('map_sites', $settings->getMapSites())
            ->assign('map_payment_types', $settings->getMapPaymentTypes());

    } else {

        $view->assign('retailcrm_connection_status', false);

        if ($action === 'connect') {
            $result->showNotifications();
        }
    }

    $view->assign('retailcrm_order_sync_console_cmd', fn_retailcrm_get_console_cmd());
}